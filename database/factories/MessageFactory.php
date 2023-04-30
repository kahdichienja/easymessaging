<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition()
    {
        return [
            'group_id' => rand(1, 5),
            'user_id' => rand(1, 10),
            'content' => $this->faker->sentence,
            'is_read' => false,
        ];
    }
}
