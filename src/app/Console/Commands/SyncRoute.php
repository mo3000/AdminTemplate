<?php

namespace App\Console\Commands;

use App\Permissions;
use App\Service\MenuService;
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
		$this->info('prepare...');
		$menuService = new MenuService();
		$conf = $menuService->getAll();
		$bar = $this->output->createProgressBar(count($conf));
		$this->info('updating menus');
		DB::beginTransaction();
		try {
			DB::table('permissions')
			  ->update(['needed' => 0]);
			foreach ($conf as $item) {
				Permissions::updateOrCreate(
					[
						'gymid' => intval($item['gymid']) > 0 ? $item['gymid'] : null,
						'project_name' => $item['project_name'],
						'path_name' => $item['path_name'],
						'name' => $item['name'],
					],
					[
						'needed' => 1,
						'display_name' => $item['display_name']
					]
				);
				$bar->advance();
			}
			$bar->finish();
			DB::table("permissions")
			  ->where('needed', 0)
			  ->delete();
			DB::commit();
			$this->line('');
			$this->info('done!');
		} catch (\Exception $e) {
			$this->error($e->getTraceAsString());
			DB::rollback();
		}
	}
}
