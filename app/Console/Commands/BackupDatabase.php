<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Backup ALL databases and upload to Google Cloud Storage';

    public function handle()
    {
        $name = "bo_tai_chinh_backup_" . date('Y_m_d_His') . ".sql";
        $backupFile = storage_path("app/" . $name);

        $dbUser = config('database.connections.pgsql.username');
        $dbPass = config('database.connections.pgsql.password');
        $dbHost = config('database.connections.pgsql.host');
        $dbPort = config('database.connections.pgsql.port');
        $dbName = config('database.connections.pgsql.database');

        // create backup using pg_dump
        $command = "PGPASSWORD=\"$dbPass\" pg_dump -U $dbUser -h $dbHost -p $dbPort $dbName > $backupFile";

        exec($command, $output, $status);

        if ($status === 0) {
            $this->info("Database backup created successfully: " . $backupFile);
        } else {
            $this->error('Database backup failed with exit code: ' . $status);
            return Command::FAILURE;
        }

        // compress the backup file
        exec("gzip -f $backupFile", $gzipOutput, $gzipStatus);

        if ($gzipStatus === 0) {
            $this->info("Database backup compressed successfully: " . $backupFile . ".gz");
            $backupFile .= ".gz";
        } else {
            $this->error('Database backup compression failed with exit code: ' . $gzipStatus);
            return Command::FAILURE;
        }

        //encrypt the backup file (optional)
        $encryptionKey = config('app.encryption_key');
        $encryptedFile = $backupFile . ".enc";
        $encryptCmd = "openssl enc -aes-256-cbc -pbkdf2 -iter 100000 -salt -in \"$backupFile\" -out \"$encryptedFile\" -k \"$encryptionKey\"";
        exec($encryptCmd, $encryptOutput, $encryptStatus);

        if ($encryptStatus === 0) {
            $this->info("Database backup encrypted successfully: " . $encryptedFile);
            // delete the unencrypted file
            unlink($backupFile);
            $backupFile = $encryptedFile;
        } else {
            $this->error('Database backup encryption failed with exit code: ' . $encryptStatus);
            return Command::FAILURE;
        }

        $remotePath = "gdrive:db_backups/" . $name;

        $cmd = "rclone copy \"$backupFile\" \"$remotePath\" --transfers=1 --checkers=1 --quiet";
        exec($cmd, $rcloneOutput, $status);

        if ($status === 0) {
            $this->info("Database backup uploaded successfully to Google Drive: " . $remotePath);
        } else {
            $this->error("Database backup upload failed with exit code: " . $status);
            return Command::FAILURE;
        }

        // // Xóa file backup local sau khi upload
        // unlink($backupFile);

        return Command::SUCCESS;
    }
}
