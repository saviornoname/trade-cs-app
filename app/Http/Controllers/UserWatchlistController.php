<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use League\Csv\Reader;
use App\Models\UserWatchlistItem;
use Illuminate\Support\Facades\Log;

class UserWatchlistController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        // Зберігаємо файл у public disk (тобто storage/app/public/uploads)
        $path = $request->file('csv_file')->store('uploads', 'public');
        Log::info('Файл збережено за шляхом: ' . $path);

        // Використовуємо storage_path з префіксом "app/public"
        $csv = Reader::createFromPath(storage_path("app/public/{$path}"), 'r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);

        $user = $request->user();
        if (!$user) {
            $user = User::inRandomOrder()->first();
        }
        foreach ($csv->getRecords() as $record) {
            $user->watchlistItems()->updateOrCreate(
                ['item_id' => $record['id']],
                ['title' => $record['title']]
            );
        }

        return response()->json(['message' => 'Імпорт завершено успішно.']);
    }
}


