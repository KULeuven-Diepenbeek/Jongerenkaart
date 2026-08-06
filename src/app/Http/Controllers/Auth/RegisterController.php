<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organisatie;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $organisatieIds = $this->organisatieIdsForEmail($data['email']);

        if ($organisatieIds === []) {
            throw ValidationException::withMessages([
                'email' => 'Er is geen organisatie gekoppeld aan dit e-mailadres.',
            ]);
        }

        User::query()->create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_CONTACT,
            'organisatie_ids' => $organisatieIds,
            'is_active' => false,
            'approved_at' => null,
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'Je account is aangemaakt en wacht op activatie door een administrator.');
    }

    private function organisatieIdsForEmail(string $email): array
    {
        $normalizedEmail = strtolower(trim($email));

        return Organisatie::query()
            ->get()
            ->filter(fn (Organisatie $organisatie) => $organisatie->hasContactEmail($normalizedEmail))
            ->map(fn (Organisatie $organisatie) => (string) $organisatie->getKey())
            ->values()
            ->all();
    }
}
