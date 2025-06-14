<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserWatchlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchlistToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_and_toggle_watchlist_items()
    {
        $user = User::factory()->create();
        $item = UserWatchlistItem::factory()->for($user)->create(['active' => true]);

        $this->actingAs($user)
            ->get(route('watchlist.items'))
            ->assertJsonFragment(['id' => $item->id])
            ->assertJsonStructure(['data', 'current_page']);

        $this->actingAs($user)
            ->patch(route('watchlist.toggle', ['item' => $item->id]))
            ->assertOk();

        $this->assertDatabaseHas('user_watchlist_items', [
            'id' => $item->id,
            'active' => false,
        ]);
    }
}
