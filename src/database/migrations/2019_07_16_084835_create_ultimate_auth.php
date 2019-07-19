<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUltimateAuth extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')
                ->comment('名称');
            $table->string('display_name')
                ->comment('展示名称');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')
                ->comment('名称');
            $table->string('alias')
                ->comment('别名');
            $table->string('display_name')
                ->comment('展示名称');
            $table->integer('level')
                ->comment('当前节点位置');
            $table->string('pathname')
                ->unique()
                ->comment('路径名');
            $table->tinyInteger('in_menu_tree')
                ->default(0)
                ->comment('是否为树状菜单');
            $table->tinyInteger('hidden_on_reject')
                ->default(0)
                ->comment('是否无权限时隐藏');
            $table->tinyInteger('require_extra')
                ->default(0)
                ->comment('额外要求 0否 1是');
            $table->json('extra')
                ->comment('预留字段');
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->integer('roleid', false, true);
            $table->integer('permissionid', false, true);
            $table->foreign('roleid')
                ->references('id')
                ->on('roles');
            $table->foreign('permissionid')
                ->references('id')
                ->on('permissions');
            $table->json('extra')
                ->comment('预留字段');
            $table->timestamps();
        });

        Schema::create('admin_role', function (Blueprint $table) {
            $table->integer('adminid', false, true);
            $table->integer('roleid', false, true);
            $table->foreign('adminid')
                ->references('id')
                ->on('admins');
            $table->foreign('roleid')
                ->references('id')
                ->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('admin_role');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
}
