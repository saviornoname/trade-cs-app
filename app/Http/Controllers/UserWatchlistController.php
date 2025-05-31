<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\Csv\Reader;
use App\Models\UserWatchlistItem;

class UserWatchlistController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->store('uploads');

        $csv = Reader::createFromPath(storage_path("app/{$path}"), 'r');
        $csv->setHeaderOffset(0);

        $user = $request->user(); // або auth()->user()

        foreach ($csv->getRecords() as $record) {
            $user->watchlistItems()->create([
                'title' => $record['title'],
                'max_price_usd' => (float)$record['max_price_usd'] * 100,
                'min_float' => $record['min_float'] ?: null,
                'max_float' => $record['max_float'] ?: null,
                'phase' => $record['phase'] ?: null,
                'paint_seed' => $record['paint_seed'] ?: null,
            ]);
        }

        return back()->with('success', 'CSV імпортовано!');
    }
}

