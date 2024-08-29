<?php

namespace App\Http\Controllers\backup;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DownloadDatabaseBackup extends Controller
{

    public function downloadDatabaseBackup()
    {
        // 👉 Run the backup command with --only-db flag
        Artisan::call('backup:run', ['--only-db' => true]);
        // 👉 Get the path to the most recent backup SQL file
        $file_path =  env('APP_NAME', 'smart_isp');
        $backupPath = storage_path("app/$file_path"); // Change to your backup storage path
        $latestBackup = collect(File::allFiles($backupPath))->sortByDesc(function ($file) {
            return $file->getMTime();
        })->first();
        if ($latestBackup) {
            return response()->download($latestBackup->getPathname());
        } else {
            abort(404, 'Backup file not found.');
        }
    }
}
