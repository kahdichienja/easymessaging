<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('primary_color')->nullable();
            $table->string('timezone')->nullable();
            $table->string('language')->nullable();
            $table->string('theme')->nullable();
            $table->boolean('online_status')->default(false);
            $table->boolean('notification_enabled')->default(true);
            $table->boolean('sms_notifications_enabled')->default(true);
            $table->boolean('email_notifications_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_settings');
    }
}
