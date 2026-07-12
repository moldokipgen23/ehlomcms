<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'subdomain' => fake()->unique()->slug(2),
            'name' => fake()->company(),
            'site_type' => fake()->randomElement(['shopping', 'info']),
            'status' => 'active',
            'plan' => fake()->randomElement(['basic', 'pro', null]),
        ];
    }

    public function shopping(): static
    {
        return $this->state(fn (array $attributes) => [
            'site_type' => 'shopping',
        ]);
    }

    public function info(): static
    {
        return $this->state(fn (array $attributes) => [
            'site_type' => 'info',
        ]);
    }
}
