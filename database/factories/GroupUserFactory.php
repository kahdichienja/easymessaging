<?php

namespace Database\Factories;

use App\Models\GroupUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupUserFactory extends Factory
{
    protected $model = GroupUser::class;

    public function definition()
    {
        return [
            'group_id' => rand(1, 5),
            'user_id' => rand(1, 10),
        ];
    }
}
