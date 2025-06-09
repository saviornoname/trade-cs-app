<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ApiCredential;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiCredentialController extends Controller
{
    public function edit(): Response
    {
        $credentials = ApiCredential::first();

        return Inertia::render('settings/ApiCredentials', [
            'credentials' => $credentials,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'buff_cookie' => ['nullable', 'string'],
            'dmarket_public_key' => ['nullable', 'string'],
            'dmarket_secret_key' => ['nullable', 'string'],
        ]);

        $cred = ApiCredential::first();
        if (!$cred) {
            ApiCredential::create($validated);
        } else {
            $cred->update($validated);
        }

        return back();
    }
}
