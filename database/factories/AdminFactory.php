<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition()
    {
        return [
            'username' => $this->faker->unique()->userName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department' => $this->faker->randomElement(['BSIT', 'BSBA', 'BSED', 'BSHM']),
            'is_active' => true,
        ];
    }

    public function superadmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'superadmin',
                'department' => null,
            ];
        });
    }

    public function departmentAdmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'department_admin',
            ];
        });
    }
}
