<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WatchlistImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_from_csv_are_marked_active()
    {
        $user = User::factory()->create();

        $csvContent = "id;title\n123;Test Item\n";
        $file = UploadedFile::fake()->createWithContent('items.csv', $csvContent);

        $response = $this->actingAs($user)->post(route('watchlist.import'), [
            'csv_file' => $file,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_watchlist_items', [
            'user_id' => $user->id,
            'item_id' => '123',
            'active' => 1,
        ]);
    }
}
