<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Backup;
use App\Support\MailBackup;
use App\Support\OffsiteBackup;
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

        // Two extra copies leave the server immediately: one is emailed to
        // the shop owner, one (when configured) is uploaded to their cPanel
        // hosting so a dead local server cannot kill all the backups.
        $mailed = MailBackup::sendCopyOf($name);
        $staged = OffsiteBackup::push($name);

        if ($staged && $mailed) {
            $message = t('admin.flash.backup_created_offsite_email',
                'Backup created: :name — a copy was emailed and one stored on your cPanel hosting.', ['name' => $name]);
        } elseif ($staged) {
            $message = t('admin.flash.backup_created_offsite',
                'Backup created: :name — a copy was stored on your cPanel hosting (no email copy — set a valid shop email in settings).', ['name' => $name]);
        } elseif ($mailed) {
            $message = t('admin.flash.backup_created', 'Backup created: :name', ['name' => $name]);
        } else {
            $message = t('admin.flash.backup_created_no_email',
                'Backup created: :name (no email copy — set a valid shop email in settings)', ['name' => $name]);
        }

        $redirect = back();

        if (OffsiteBackup::enabled() && ! $staged) {
            $redirect->with('error', t('admin.flash.backup_offsite_failed',
                'Off-site copy of :name could not be stored on your cPanel hosting — check the FTP settings on the Settings page.', ['name' => $name]));
        }

        return $redirect->with('success', $message);
    }

    /**
     * Manual copy of an existing backup file to the cPanel storage.
     */
    public function offsite(string $name)
    {
        abort_unless(Backup::exists($name), 404);

        if (OffsiteBackup::push($name)) {
            return back()->with('success', t('admin.flash.offsite_uploaded',
                ':name is now on your cPanel hosting.', ['name' => $name]));
        }

        return back()->with('error', t('admin.flash.offsite_upload_failed',
            'Could not store :name on your cPanel hosting — check the FTP settings on the Settings page.', ['name' => $name]));
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
