<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return  [
            'user_id' => rand(10, 11),
            'primary_color' => $this->faker->hexColor(),
            'timezone' => 'africa/Nairobi',
            'language'  => 'en',
            'theme' => 'light',
            // 'online_status' => true,
            // 'notification_enabled' => 'africa/Narobi',
            // 'sms_notifications_enabled' => 'africa/Narobi',
            // 'email_notifications_enabled' => 'africa/Narobi',
        ];
    }
}
