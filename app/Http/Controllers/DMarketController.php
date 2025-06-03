<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\DMarketService;

class DMarketController extends Controller
{
    public function index()
    {
        return Inertia::render('DMarketTest');
    }

    public function getMarketItems(Request $request, DMarketService $dmarket)
    {
        // отримуємо дані з форми або з фронтенда
        $title = $request->input('title', '');
        $gameId = $request->input('gameId', 'a8db'); // CS:GO/CS2 — це DMarket Game ID

        $items = $dmarket->getMarketTargets($gameId, $title);

        return response()->json($items);
    }

    public function getUserTargets(DMarketService $dmarket)
    {
        return response()->json($dmarket->getUserTargets());
    }

    public function createTarget(Request $request, DMarketService $dmarket)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'price' => 'required|numeric',
        ]);

        $response = $dmarket->createTarget([
            'title' => $data['title'],
            'price' => $data['price'],
        ]);

        return response()->json($response);
    }
}
