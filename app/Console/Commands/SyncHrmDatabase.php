<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncHrmDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:newqubifytech';
    protected $description = 'Synchronize newqubifytech database with qubifytech database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sourceConnection = 'newqubifytech';
        $targetConnection = 'qubifytech';

        $tables = DB::connection($sourceConnection)->select('SHOW TABLES');
        $tables = array_map('current', $tables);

        DB::connection($targetConnection)->statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tables as $table) {
            // Truncate the table in the target database
            DB::connection($targetConnection)->table($table)->truncate();

            // Copy data from the source table to the target table
            $data = DB::connection($sourceConnection)->table($table)->get();

            if ($data->isNotEmpty()) {
                $dataArray = $data->map(function ($item) {
                    return (array)$item;
                })->toArray();

                DB::connection($targetConnection)->table($table)->insert($dataArray);
            }
        }

        DB::connection($targetConnection)->statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('HRM database has been synchronized with HRM Demo database.');
    }
}
