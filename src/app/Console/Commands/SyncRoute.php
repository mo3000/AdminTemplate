<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导入route到数据库';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    	$oldnames = DB::table("permissions")
		    ->get()
		    ->pluck('name')
	        ->toArray();
    	$newnames = [];
        while ($line = fgets(STDIN)) {
        	$newnames[] = rtrim($line);
        }
        $newnames = array_unique($newnames);
        $deletes = array_diff($oldnames, $newnames);
        $adds = array_diff($newnames, $oldnames);
        if (count($deletes) > 0) {
	        DB::table('permissions')
	          ->whereIn('name', $deletes)
	          ->delete();
        }
        if (count($adds) > 0) {
	        DB::table('permissions')
	          ->insert(
		          collect($adds)
			          ->map(function ($v) {
				          return ['name' => $v];
			          })
			          ->toArray()
	          );
        }

    }
}
