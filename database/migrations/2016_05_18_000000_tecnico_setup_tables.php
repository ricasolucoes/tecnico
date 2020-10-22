<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class TecnicoSetupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(\Config::get('tecnico.users_table'), function (Blueprint $table) {
            $table->integer('current_group_id')->unsigned()->nullable();
        });

        Schema::create(\Config::get('tecnico.groups_table'), function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('owner_id')->unsigned()->nullable();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create(\Config::get('tecnico.group_user_table'), function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned();
            $table->integer('group_id')->unsigned();
            $table->timestamps();

            $table->foreign('user_id')
                ->references(\Config::get('tecnico.user_foreign_key'))
                ->on(\Config::get('tecnico.users_table'))
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('group_id')
                ->references('id')
                ->on(\Config::get('tecnico.groups_table'))
                ->onDelete('cascade');
        });

        Schema::create(\Config::get('tecnico.group_invites_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned();
            $table->integer('group_id')->unsigned();
            $table->enum('type', ['invite', 'request']);
            $table->string('email');
            $table->string('accept_token');
            $table->string('deny_token');
            $table->timestamps();
            $table->foreign('group_id')
                ->references('id')
                ->on(\Config::get('tecnico.groups_table'))
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(\Config::get('tecnico.users_table'), function (Blueprint $table) {
            $table->dropColumn('current_group_id');
        });

        Schema::table(\Config::get('tecnico.group_user_table'), function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(\Config::get('tecnico.group_user_table').'_user_id_foreign');
            }
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(\Config::get('tecnico.group_user_table').'_group_id_foreign');
            }
        });

        Schema::drop(\Config::get('tecnico.group_user_table'));
        Schema::drop(\Config::get('tecnico.group_invites_table'));
        Schema::drop(\Config::get('tecnico.groups_table'));
    }
}
