<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ChangeTimestampColumnCommand extends Command
{
    protected $signature = 'api:change-table-timestamp-column {table?}';

    protected $description = 'Command description';


    public function handle()
    {
        $table = $this->argument('table');
        if ($table) {
            $tables = [$table];
        } else {
            $tables = Schema::getAllTables();
        }

        foreach ($tables as $table) {
            $table = (array)$table;
            $table = array_values($table)[0];
            $columns = Schema::getColumnListing($table);
            foreach ($columns as $column) {
                $type = Schema::getColumnType($table, $column);
                if ($type === 'datetime') {
                    Schema::table($table, function ($table) use($column) {
                        $table->datetime($column)->nullable()->change();
                    });
                }
            }
        }
    }
}
