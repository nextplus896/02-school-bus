<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2021_08_01_000052_add_admin_info_to_settings_table
class AddAdminInfoToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //add admin_email, admin_phone, admin_address, admin_latitude, admin_longitude to settings table
        Schema::table('settings', function (Blueprint $table) {
            $table->string('admin_email')->nullable();
            $table->string('admin_phone')->nullable();
            $table->string('admin_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //drop admin_email, admin_phone, admin_address, admin_latitude, admin_longitude from settings table
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('admin_email');
            $table->dropColumn('admin_phone');
            $table->dropColumn('admin_address');
        });
    }
}
