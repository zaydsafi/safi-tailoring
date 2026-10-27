<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Dependency-free database backups. Dumps every table to a plain SQL
 * file under storage/app/backups and can replay the dumped file to
 * restore the database. Works with both engines this project runs on:
 * MySQL in production and SQLite in the test suite.
 *
 * Files are only ever addressed by the name this class generates, so a
 * Restoring path can never escape the backups directory.
 */
class Backup
{
    /** Files handed out by this class always match this. */
    public const FILENAME_PATTERN = '/^shop-backup-[A-Za-z0-9_.\-]+\.sql$/';

    /**
     * One statement per dump chunk is separated by this marker line, so
     * restore never has to guess where a statement ends — even if row
     * data itself contains semicolons or comment lines.
     */
    public const MARKER = '-- @--STMT--@';

    /**
     * Every tagged statement carries a comment line naming its table, so
     * partial recovery can replay just the chosen tables.
     */
    public const TABLE_TAG = '-- ?--BAK-TABLE:';

    public static function directory(): string
    {
        $dir = storage_path('app/backups');

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return $dir;
    }

    /**
     * All backup files on disk, newest first.
     *
     * @return list<array{name: string, size: int, modified: int}>
     */
    public static function files(): array
    {
        $paths = glob(rtrim(self::directory(), '/\\').'/*.sql') ?: [];

        // Newest first; the timestamped names break mtime ties so files
        // created within the same second still order chronologically.
        usort($paths, fn (string $a, string $b) => [filemtime($b), basename($b)] <=> [filemtime($a), basename($a)]);

        return array_map(fn (string $path) => [
            'name' => basename($path),
            'size' => (int) filesize($path),
            'modified' => (int) filemtime($path),
        ], $paths);
    }

    public static function exists(string $name): bool
    {
        return preg_match(self::FILENAME_PATTERN, $name) === 1
            && is_file(self::path($name));
    }

    public static function path(string $name): string
    {
        if (preg_match(self::FILENAME_PATTERN, $name) !== 1) {
            throw new RuntimeException("Invalid backup name: {$name}");
        }

        return self::directory().DIRECTORY_SEPARATOR.basename($name);
    }

    /**
     * Dump the whole database to storage/app/backups and return the
     * generated file name.
     */
    public static function create(): string
    {
        $name = 'shop-backup-'.now()->format('Y-m-d_His').'.sql';

        // Backups created within the same second get a numeric suffix
        // instead of silently overwriting each other.
        $suffix = 1;
        while (is_file(self::directory().DIRECTORY_SEPARATOR.$name)) {
            $suffix++;
            $name = preg_replace('/\.sql$/', "-{$suffix}.sql", 'shop-backup-'.now()->format('Y-m-d_His').'.sql');
        }

        $path = self::directory().DIRECTORY_SEPARATOR.$name;

        $handle = fopen($path, 'w');

        if ($handle === false) {
            throw new RuntimeException("Cannot open backup file for writing: {$path}");
        }

        $driver = DB::connection()->getDriverName();
        $tables = self::orderedTables(self::tables());

        fwrite($handle, "-- Safi Tailoring Shop database backup\n");
        fwrite($handle, '-- Engine: '.$driver."\n");
        fwrite($handle, '-- Generated: '.now()->toIso8601String()."\n");
        fwrite($handle, '-- Tables: '.count($tables)."\n");

        if ($driver === 'mysql') {
            self::writeStatement($handle, 'SET FOREIGN_KEY_CHECKS=0');
        }

        // Schema first, then data, so foreign keys are satisfied during
        // replay in both engines.
        foreach ($tables as $table) {
            self::writeSchema($handle, $table);
        }

        foreach ($tables as $table) {
            self::writeData($handle, $table);
        }

        if ($driver === 'sqlite') {
            foreach ($tables as $table) {
                self::writeIndexes($handle, $table);
            }
        }

        if ($driver === 'mysql') {
            self::writeStatement($handle, 'SET FOREIGN_KEY_CHECKS=1');
        }

        fclose($handle);

        return $name;
    }

    /**
     * Tables and how many rows each holds in the dump file.
     *
     * @return array<string, int>
     */
    public static function dumpTables(string $name): array
    {
        $tables = [];

        $tagged = preg_quote(self::TABLE_TAG, '/');

        foreach (self::chunks($name) as $statement) {
            preg_match('/^'.$tagged.'\s*([A-Za-z0-9_]+)/', $statement, $tableMatch);

            if (! isset($tableMatch[1])) {
                continue;
            }

            $table = $tableMatch[1];
            $sql = (string) preg_replace('/^'.$tagged.'\s*([A-Za-z0-9_]+)\n/', '', $statement);

            $tables[$table] = ($tables[$table] ?? 0)
                + (str_starts_with($sql, 'INSERT INTO') ? 1 : 0);
        }

        return $tables;
    }

    /**
     * Replay the dump's SQL: every table replaced, or — when $tables is
     * given — only those tables emptied and refilled while all others
     * keep their current data.
     *
     * @param  list<string>|null  $tables
     */
    public static function restore(string $name, ?array $tables = null): void
    {
        $chunks = self::chunks($name);

        if ($tables === null) {
            self::dropAllTables();
        } else {
            $available = self::dumpTables($name);
            $unknown = array_diff($tables, array_keys($available));

            if ($unknown !== []) {
                throw new RuntimeException('Dump has no data for table(s): '.implode(', ', $unknown));
            }

            self::dropTables(array_values(array_intersect(
                array_keys($available),
                $tables
            )));
        }

        $tagged = preg_quote(self::TABLE_TAG, '/');

        foreach ($chunks as $chunk) {
            preg_match('/^'.$tagged.'\s*([A-Za-z0-9_]+)/', $chunk, $tableMatch);

            if (isset($tableMatch[1])) {
                if ($tables !== null && ! in_array($tableMatch[1], $tables, true)) {
                    continue;
                }

                // Drop the tag comment line so the SQL itself is what runs.
                $chunk = (string) preg_replace('/^'.$tagged.'\s*([A-Za-z0-9_]+)\n/', '', $chunk);
            }

            $statement = trim($chunk);

            if ($statement === '' || str_starts_with($statement, '--')) {
                continue;
            }

            // Header lines written before the first marker.
            if (str_starts_with($statement, 'SET FOREIGN_KEY_CHECKS=')) {
                if (DB::connection()->getDriverName() === 'mysql') {
                    DB::unprepared($statement);
                }

                continue;
            }

            if (str_starts_with($statement, 'PRAGMA ') || str_starts_with($statement, 'COMMIT')) {
                continue;
            }

            DB::unprepared($statement);
        }
    }

    public static function delete(string $name): void
    {
        unlink(self::path($name));
    }

    /**
     * Keep only the newest $keep backup files; returns how many were
     * removed.
     */
    public static function prune(int $keep): int
    {
        if ($keep < 1) {
            return 0;
        }

        $removed = 0;

        foreach (array_slice(self::files(), $keep) as $file) {
            self::delete($file['name']);
            $removed++;
        }

        return $removed;
    }

    /**
     * The dump split into individual statements, comment lines and all,
     * for inspection and selective replay.
     *
     * @return list<string>
     */
    private static function chunks(string $name): array
    {
        $path = self::path($name);

        if (! is_file($path)) {
            throw new RuntimeException("Backup file not found: {$name}");
        }

        return explode("\n".self::MARKER."\n", (string) file_get_contents($path));
    }

    private static function writeStatement($handle, string $sql, ?string $table = null): void
    {
        $tag = $table !== null ? self::TABLE_TAG.' '.$table."\n" : '';

        fwrite($handle, self::MARKER."\n".$tag.$sql.";\n");
    }

    /**
     * @return list<string>
     */
    private static function tables(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $rows = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');

            return array_map(fn ($row) => array_values((array) $row)[0], $rows);
        }

        $rows = DB::select(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"
        );

        return array_map(fn ($row) => $row->name, $rows);
    }

    /**
     * Tables that the given one holds foreign keys for (its parents).
     *
     * @return list<string>
     */
    private static function foreignKeyTargets(string $table): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $rows = DB::select(
                'SELECT DISTINCT REFERENCED_TABLE_NAME AS ref FROM information_schema.KEY_COLUMN_USAGE'
                .' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
                [$table]
            );

            return array_map(fn ($row) => $row->ref, $rows);
        }

        $rows = DB::select("PRAGMA foreign_key_list({$table})");

        return array_map(fn ($row) => $row->table, $rows);
    }

    /**
     * Sort tables so every parent comes before its children — otherwise
     * replaying the dump on a fresh database violates foreign keys.
     * Cycles are broken deterministically by keeping input order.
     *
     * @param  list<string>  $tables
     * @return list<string>
     */
    private static function orderedTables(array $tables): array
    {
        $targets = [];

        foreach ($tables as $table) {
            $targets[$table] = array_values(array_intersect(
                self::foreignKeyTargets($table),
                $tables
            ));
        }

        $ordered = [];
        $placed = [];

        while (count($ordered) < count($tables)) {
            $progress = false;

            foreach ($tables as $table) {
                if (isset($placed[$table])) {
                    continue;
                }

                $unplacedParents = array_filter(
                    $targets[$table],
                    fn (string $parent) => ! isset($placed[$parent])
                );

                if ($unplacedParents === []) {
                    $ordered[] = $table;
                    $placed[$table] = true;
                    $progress = true;
                }
            }

            if (! $progress) {
                // A cycle: append everything still missing in input order.
                foreach ($tables as $table) {
                    if (! isset($placed[$table])) {
                        $ordered[] = $table;
                    }
                }
                break;
            }
        }

        return array_values(array_unique($ordered));
    }

    private static function writeSchema($handle, string $table): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $row = DB::selectOne("SHOW CREATE TABLE `{$table}`");

            self::writeStatement($handle, 'DROP TABLE IF EXISTS `'.$table.'`', $table);
            self::writeStatement($handle, (string) $row->{'Create Table'}, $table);

            return;
        }

        $row = DB::selectOne(
            "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?",
            [$table]
        );

        self::writeStatement($handle, 'DROP TABLE IF EXISTS `'.$table.'`', $table);
        self::writeStatement($handle, (string) $row->sql, $table);
    }

    private static function writeData($handle, string $table): void
    {
        $pdo = DB::connection()->getPdo();

        $columns = [];
        $rows = DB::select("SELECT * FROM `{$table}`");

        foreach ($rows as $row) {
            $values = [];
            $dataArray = (array) $row;

            if ($columns === []) {
                $columns = array_map(fn (string $column) => '`'.$column.'`', array_keys($dataArray));
            }

            foreach ($dataArray as $value) {
                if ($value === null) {
                    $values[] = 'NULL';
                } elseif (is_int($value) || ctype_digit((string) $value)) {
                    $values[] = (string) $value;
                } else {
                    $values[] = $pdo->quote((string) $value);
                }
            }

            self::writeStatement($handle, sprintf(
                'INSERT INTO `%s` (%s) VALUES (%s)',
                $table,
                implode(', ', $columns),
                implode(', ', $values)
            ), $table);
        }
    }

    /**
     * SQLite keeps indexes outside CREATE TABLE, so they are dumped
     * separately after the data load.
     */
    private static function writeIndexes($handle, string $table): void
    {
        $rows = DB::select(
            "SELECT sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND sql IS NOT NULL",
            [$table]
        );

        foreach ($rows as $row) {
            self::writeStatement($handle, (string) $row->sql, $table);
        }
    }

    private static function dropAllTables(): void
    {
        self::dropTables(self::tables());
    }

    /**
     * Drop the named tables only.
     *
     * @param  list<string>  $tables
     */
    private static function dropTables(array $tables): void
    {
        $tables = array_values($tables);
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($tables as $table) {
                DB::statement('DROP TABLE IF EXISTS `'.$table.'`');
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return;
        }

        // SQLite with foreign keys on refuses to drop referenced parents,
        // so drop what we can in passes until nothing is left.
        $remaining = array_combine($tables, $tables);

        while ($remaining !== []) {
            $progress = false;

            foreach ($remaining as $table) {
                try {
                    DB::statement('DROP TABLE IF EXISTS `'.$table.'`');
                    unset($remaining[$table]);
                    $progress = true;
                } catch (\Throwable) {
                    try {
                        DB::statement('DROP TABLE `'.$table.'`');
                        unset($remaining[$table]);
                        $progress = true;
                    } catch (\Throwable) {
                        // Referenced by a table that is still standing; next pass.
                    }
                }
            }

            if (! $progress) {
                throw new RuntimeException('Could not drop relationally locked tables: '.implode(', ', $remaining));
            }
        }
    }
}
