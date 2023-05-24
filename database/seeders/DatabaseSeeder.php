<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\Message;
use App\Models\GroupUser;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        // Create 10 users
        User::factory()->count(10)->create();

        // // Create 5 groups
        Group::factory()->count(5)->create();

        // // // Create 20 group users
        GroupUser::factory()->count(20)->create();

        // // Create 50 messages
        Message::factory()->count(50)->create();

        // // Create 1 settings
        UserSetting::factory()->count(1)->create();

    }
}
