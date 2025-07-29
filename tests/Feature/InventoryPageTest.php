<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_inventory_page()
    {
        $this->withoutVite();
        $response = $this->get('/dashboard/inventory');
        $response->assertStatus(200);
    }

    public function test_authenticated_users_can_view_inventory_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutVite();
        $response = $this->get('/dashboard/inventory');
        $response->assertStatus(200);
    }
}
