<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserWatchlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchlistDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_watchlist_item()
    {
        $user = User::factory()->create();
        $item = UserWatchlistItem::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('watchlist.delete', ['item' => $item->id]))
            ->assertOk();

        $this->assertDatabaseMissing('user_watchlist_items', [
            'id' => $item->id,
        ]);
    }
}
