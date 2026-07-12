<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * TEMPORARY, ONE-TIME-USE migration helper for moving off a host with no
 * shell access. Delete this file and its routes immediately after use.
 */
class TempExportController extends Controller
{
    private const TOKEN = '2bd962f33060cbbe71bb327142ce16815b8236fb92e8719cf67975f2a952cf69';

    private function checkToken(string $token): void
    {
        abort_unless(hash_equals(self::TOKEN, $token), 404);
    }

    public function database(string $token): StreamedResponse
    {
        $this->checkToken($token);
        set_time_limit(0);

        return response()->streamDownload(function () {
            $tables = collect(DB::select('SHOW TABLES'))
                ->map(fn ($row) => array_values((array) $row)[0]);

            echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $create = DB::select("SHOW CREATE TABLE `{$table}`")[0]->{'Create Table'};
                echo "DROP TABLE IF EXISTS `{$table}`;\n{$create};\n\n";

                DB::table($table)->orderBy(DB::raw('1'))->chunk(500, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        $data = (array) $row;
                        $cols = implode(', ', array_map(fn ($c) => "`{$c}`", array_keys($data)));
                        $vals = implode(', ', array_map(function ($v) {
                            if ($v === null) {
                                return 'NULL';
                            }

                            return DB::connection()->getPdo()->quote((string) $v);
                        }, array_values($data)));
                        echo "INSERT INTO `{$table}` ({$cols}) VALUES ({$vals});\n";
                    }
                });
                echo "\n";
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, 'ehlom_export.sql');
    }

    public function files(string $token): StreamedResponse
    {
        $this->checkToken($token);
        set_time_limit(0);

        $source = storage_path('app/public');
        $zipPath = storage_path('app/temp_export_files.zip');

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if (is_dir($source)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                $relative = 'public/'.substr($file->getPathname(), strlen($source) + 1);
                $zip->addFile($file->getPathname(), $relative);
            }
        }

        $zip->close();

        return response()->streamDownload(function () use ($zipPath) {
            readfile($zipPath);
            @unlink($zipPath);
        }, 'ehlom_storage_files.zip');
    }
}
