<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RestoreDatabase extends Command
{
    protected $signature = 'db:restore {file : Absolute path of backup file} {--force : Skip confirmation}';
    protected $description = 'Restore the database from a backup file';

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("Backup file not found: $file");
            return Command::FAILURE;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("This will ERASE current database and restore from {$file}. Continue?")) {
                $this->info("Restore cancelled.");
                return Command::SUCCESS;
            }
        }
        $dbAdmin = config('database.connections.pgsql.admin');
        $dbPass = config('database.connections.pgsql.password');
        $dbHost = config('database.connections.pgsql.host');
        $dbPort = config('database.connections.pgsql.port');
        $dbName = config('database.connections.pgsql.database');

        // decrypt the backup file if it is encrypted
        $decryptedFile = $file;
        if (str_ends_with($file, '.enc')) {
            $this->info("Decrypting backup file...");
            $encryptionKey = config('app.encryption_key');
            $decryptedFile = str_replace('.enc', '', $file);
            $decryptCmd = "openssl enc -aes-256-cbc -pbkdf2 -iter 100000 -d -in \"$file\" -out \"$decryptedFile\" -k \"$encryptionKey\"";
            exec($decryptCmd, $decryptOutput, $decryptStatus);
            if ($decryptStatus !== 0) {
                $this->error("Decryption FAILED with exit code $decryptStatus");
                return Command::FAILURE;
            }
            $this->info("Decryption successful: " . $decryptedFile);
        }
        $file = $decryptedFile;

        // decompress the backup file if it is compressed
        if (str_ends_with($file, '.gz')) {
            $this->info("Decompressing backup file...");
            $decompressCmd = "gunzip -f \"$file\"";

            exec($decompressCmd, $decompressOutput, $decompressStatus);
            if ($decompressStatus !== 0) {
                $this->error("Decompression FAILED with exit code $decompressStatus");
                return Command::FAILURE;
            }
            $this->info("Decompression successful.");
            $file = str_replace('.gz', '', $file);
        }

        // Drop and recreate database schema (optional but safe for restore)
        $this->info("Dropping all tables...");
        DB::connection('pgsql')->getSchemaBuilder()->dropAllTables();

        // Restore database
        $this->info("Restoring database...");
        $command = "PGPASSWORD=\"$dbPass\" psql -U $dbAdmin -h $dbHost -p $dbPort -d $dbName < \"$file\"";
        exec($command, $output, $return);

        if ($return !== 0) {
            $this->error("Restore FAILED with exit code $return");
            return Command::FAILURE;
        }

        $this->info("Database restored successfully from {$file}");

        return Command::SUCCESS;
    }
}
