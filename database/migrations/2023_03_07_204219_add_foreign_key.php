<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_local', function (Blueprint $table) {
            $table->foreign('type_salle_id')->references('id')->on('type_salle');
            $table->foreign('type_jour_id')->references('id')->on('type_jour');
        });
        Schema::table('personnels_group_locals', function (Blueprint $table) {
            $table->foreign('group_local_id')->references('id')->on('group_local');
            $table->foreign('personnel_id')->references('id')->on('personnels');
        });
        Schema::table('commentaire', function (Blueprint $table) {
            $table->foreign('reservation_id')->references('id')->on('reservation');
            $table->foreign('personnel_id')->references('id')->on('personnels');
        });
        Schema::table('line_type_accessoire', function (Blueprint $table) {
            $table->foreign('reservation_id')->references('id')->on('reservation');
            $table->foreign('type_accessoire_id')->references('id')->on('type_accessoire');
        });
        Schema::table('group_local_local', function (Blueprint $table) {
            $table->foreign('group_local_id')->references('id')->on('group_local');
            $table->foreign('local_id')->references('id')->on('local');
        });
        Schema::table('reservation', function (Blueprint $table) {
            $table->foreign('local_id')->references('id')->on('local');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('periode_id')->references('id')->on('periode');
            $table->foreign('personnel_id')->references('id')->on('personnels');
            $table->foreign('group_local_id')->references('id')->on('group_local');
        });
        Schema::table('personnels', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
