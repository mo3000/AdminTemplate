<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_notification', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 150);
            $table->json('data')
                ->comment('内容');
            $table->dateTime('read_at')->nullable();
            $table->integer('notifiable_id');
            $table->string('notifiable_type', 50);
            $table->timestamps();
            $table->index(['notifiable_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_notification');
    }
}
