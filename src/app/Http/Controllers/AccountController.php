<?php

namespace App\Http\Controllers;

use App\Models\Organisatie;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $organisaties = collect();

        if (! $user->isAdmin()) {
            $organisatieIds = collect($user->organisatie_ids ?? [])
                ->map(fn (string $id) => trim($id))
                ->filter()
                ->values();

            $organisaties = Organisatie::query()
                ->get()
                ->filter(fn (Organisatie $organisatie) => $organisatieIds->contains((string) $organisatie->getKey())
                    || $organisatie->hasContactEmail($user->email))
                ->sortBy('publiekeNaam', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        }

        return view('account.show', [
            'user' => $user,
            'organisaties' => $organisaties,
        ]);
    }
}
