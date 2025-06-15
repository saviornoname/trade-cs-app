<?php

namespace Database\Factories;

use App\Models\UserWatchlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserWatchlistItem>
 */
class UserWatchlistItemFactory extends Factory
{
    protected $model = UserWatchlistItem::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'item_id' => (string) $this->faker->numberBetween(100000, 999999),
            'active' => true,
            'filters' => [],
        ];
    }
}
