<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupAndDownload extends Command
{
    protected $signature = 'backup:download';

    protected $description = 'Backup database and download';

    public function handle()
    {
        // Run the backup:run command with the --only-db option
        Artisan::call('backup:run', ['--only-db' => true]);

        // Your custom download code here
        $filename = 'backup-name.sql'; // Adjust the backup filename
        $filePath = storage_path("app/backup/{$filename}");

        if (File::exists($filePath)) {
            $this->info("Backup and download successful!");
            return response()->download($filePath);
        } else {
            $this->error("Backup file not found.");
        }
    }
}
