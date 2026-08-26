<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup data MySQL secara portabel tanpa mysqldump (pure PHP export)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai backup database...');

        $tables = [];
        $databaseName = DB::connection()->getDatabaseName();
        $dbType = DB::connection()->getDriverName();

        if ($dbType !== 'mysql') {
            $this->error('Hanya mendukung database MySQL/MariaDB saat ini.');
            return 1;
        }

        // Dapatkan semua nama tabel
        $results = DB::select('SHOW TABLES');
        $keyName = 'Tables_in_' . $databaseName;

        foreach ($results as $row) {
            $tables[] = $row->$keyName;
        }

        $sqlDump = "-- Fadilah Digital Printing Database Backup\n";
        $sqlDump .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
        $sqlDump .= "-- Database: " . $databaseName . "\n\n";
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $this->info("Mengekspor tabel: {$table}...");

            // Generate CREATE TABLE
            $createTableResult = DB::select("SHOW CREATE TABLE `{$table}`");
            $sqlDump .= $createTableResult[0]->{'Create Table'} . ";\n\n";

            // Generate INSERT INTO
            $rows = DB::table($table)->get();
            foreach ($rows as $row) {
                $rowArray = (array) $row;
                $keys = array_map(function($key) {
                    return "`{$key}`";
                }, array_keys($rowArray));

                $values = array_map(function($value) {
                    if ($value === null) {
                        return 'NULL';
                    }
                    // Escape string
                    return "'" . addslashes($value) . "'";
                }, array_values($rowArray));

                $sqlDump .= "INSERT INTO `{$table}` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $sqlDump .= "\n";
        }

        $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backups/backup-' . date('Y-m-d_H-i-s') . '.sql';
        Storage::disk('local')->put($filename, $sqlDump);

        $this->info("Backup database berhasil disimpan di: " . storage_path('app/' . $filename));
        return 0;
    }
}
