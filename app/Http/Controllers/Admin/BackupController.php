<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Backup;
use App\Support\MailBackup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function index()
    {
        return view('admin.backups.index', [
            'backups' => Backup::files(),
        ]);
    }

    public function store()
    {
        $name = Backup::create();

        // A second copy leaves the server immediately: the backup is
        // emailed to the shop owner with the SQL file attached.
        $mailed = MailBackup::sendCopyOf($name);

        $message = $mailed
            ? t('admin.flash.backup_created', 'Backup created: :name', ['name' => $name])
            : t('admin.flash.backup_created_no_email', 'Backup created: :name (no email copy — set a valid shop email in settings)', ['name' => $name]);

        return back()->with('success', $message);
    }

    public function download(string $name): BinaryFileResponse
    {
        abort_unless(Backup::exists($name), 404);

        return response()->download(Backup::path($name), $name, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function recover(string $name)
    {
        abort_unless(Backup::exists($name), 404);

        return view('admin.backups.recover', [
            'name' => $name,
            'tables' => Backup::dumpTables($name),
        ]);
    }

    public function destroy(string $name)
    {
        abort_unless(Backup::exists($name), 404);

        Backup::delete($name);

        return back()->with('success', t('admin.flash.backup_deleted', 'Backup deleted: :name', ['name' => $name]));
    }

    public function restore(Request $request, string $name)
    {
        abort_unless(Backup::exists($name), 404);

        $tables = $request->input('tables');

        if (filled($tables)) {
            // Recovery keeps everything not ticked untouched; the names
            // must be tables the dump actually contains.
            $tables = $request->validate([
                'tables' => ['array'],
                'tables.*' => ['string', Rule::in(array_keys(Backup::dumpTables($name)))],
            ])['tables'];

            Backup::restore($name, $tables);

            return back()->with('success', t('admin.flash.tables_recovered', 'Recovered :tables from :name', [
                'tables' => implode(', ', $tables),
                'name' => $name,
            ]));
        }

        Backup::restore($name);

        return back()->with('success', t('admin.flash.backup_restored', 'Database restored from :name', ['name' => $name]));
    }
}
