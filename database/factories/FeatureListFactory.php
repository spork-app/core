<?php

namespace Spork\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spork\Core\Models\FeatureList;

class FeatureListFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FeatureList::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $features = [
            'core',
            'development',
            'finance',
        ];

        $userModel = config('spork-core.models.user');

        return [
            'name' => $this->faker->name(),
            'feature' => $features[array_rand($features, 1)],
            'user_id' => $userModel::factory(),
            'settings' => [],
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
