<?php

namespace App\Console\Commands;

use App\Admin;
use App\Models\Auth\Roles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AddInitData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AddInitData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '添加初始角色';

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
        DB::beginTransaction();
        try {
            $role = new Roles();
            $role->name = 'superadmin';
            $role->display_name = '超级管理员';
            $role->save();
            $admin = new Admin();
            $admin->password = Hash::make('123456');
            $admin->realname = '管理员';
            $admin->username = 'admin';
            $admin->save();
            $admin->roles()->save($role);
            DB::commit();
            $this->info('success');
        } catch (\RuntimeException $e) {
            DB::rollBack();
            $this->error($e->getTraceAsString());
        }
    }
}
