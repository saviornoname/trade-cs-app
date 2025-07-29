<?php

namespace Tests\Feature;

use App\Services\DMarketService;
use Mockery;
use Tests\TestCase;

class DMarketApiTest extends TestCase
{
    public function test_inventory_route_returns_service_response()
    {
        $service = Mockery::mock(DMarketService::class);
        $service->shouldReceive('getUserInventory')->with('a8db')->once()->andReturn(['data' => []]);
        $this->instance(DMarketService::class, $service);

        $this->get(route('dmarket.inventory'))
            ->assertOk()
            ->assertJson(['data' => []]);
    }

    public function test_offers_route_returns_service_response()
    {
        $service = Mockery::mock(DMarketService::class);
        $service->shouldReceive('getUserOffers')->with('a8db', [])->once()->andReturn(['orders' => []]);
        $this->instance(DMarketService::class, $service);

        $this->get(route('dmarket.offers'))
            ->assertOk()
            ->assertJson(['orders' => []]);
    }
}
