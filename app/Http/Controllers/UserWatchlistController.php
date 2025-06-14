<?php

namespace App\Http\Controllers;

use App\Models\UserWatchlistItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

class UserWatchlistController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user() ?: User::first();

        $query = $user->watchlistItems();

        if ($request->boolean('active')) {
            $query->where('active', true);
        }

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $perPage = $request->integer('per_page', 10);

        return response()->json($query->paginate($perPage));
    }

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
        $importedIds = [];

        foreach ($csv->getRecords() as $record) {
            $item = $user->watchlistItems()->updateOrCreate(
                ['item_id' => $record['id']],
                [
                    'title' => $record['title'],
                    'active' => true,
                ]
            );
            $importedIds[] = $item->id;
        }

        // Deactivate items not present in file
        $user->watchlistItems()->whereNotIn('id', $importedIds)->update(['active' => false]);

        return response()->json(['message' => 'Імпорт завершено успішно.']);
    }
    public function toggleActive(Request $request, UserWatchlistItem $item)
    {
        $item->active = !$item->active;

        $item->save();

        return response()->json(['active' => $item->active]);
    }

    public function deactivateAll(Request $request)
    {
        $user = $request->user() ?: User::first();
        $user->watchlistItems()->update(['active' => false]);

        return response()->json(['status' => 'ok']);
    }

    public function activateAll(Request $request)
    {
        $user = $request->user() ?: User::first();
        $user->watchlistItems()->update(['active' => true]);

        return response()->json(['status' => 'ok']);
    }
}


