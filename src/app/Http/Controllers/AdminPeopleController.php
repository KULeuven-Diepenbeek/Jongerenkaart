<?php

namespace App\Http\Controllers;

use App\Models\Medewerker;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class AdminPeopleController extends Controller
{
    public function storeMedewerker(Request $request): RedirectResponse
    {
        $data = $this->validatedMedewerker($request);

        Medewerker::query()->create($data);

        return back()->with('settings_status', [
            'title' => 'Medewerker toegevoegd',
            'items' => [$data['naam'].' is toegevoegd aan het publieke team.'],
        ]);
    }

    public function updateMedewerker(Request $request, Medewerker $medewerker): RedirectResponse
    {
        $data = $this->validatedMedewerker($request);
        $medewerker->fill($data);
        $medewerker->save();

        return back()->with('settings_status', [
            'title' => 'Medewerker aangepast',
            'items' => [$data['naam'].' is opgeslagen.'],
        ]);
    }

    public function destroyMedewerker(Medewerker $medewerker): RedirectResponse
    {
        $name = $medewerker->naam;
        $medewerker->delete();

        return back()->with('settings_status', [
            'title' => 'Medewerker verwijderd',
            'items' => [$name.' is verwijderd uit het publieke team.'],
        ]);
    }

    public function createAdmin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::query()->create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_ADMIN,
            'organisatie_ids' => [],
            'is_active' => true,
            'approved_at' => now(),
        ]);

        return back()->with('settings_status', [
            'title' => 'Admin gebruiker aangemaakt',
            'items' => [$data['email'].' kan nu inloggen als admin.'],
        ]);
    }

    public function activateUser(User $user): RedirectResponse
    {
        $user->is_active = true;
        $user->approved_at = now();
        $user->save();

        return back()->with('settings_status', [
            'title' => 'Account geactiveerd',
            'items' => [$user->email.' kan nu inloggen.'],
        ]);
    }

    public function resetUserPassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->password = Hash::make($data['password']);
        $user->save();

        return back()->with('settings_status', [
            'title' => 'Wachtwoord opnieuw ingesteld',
            'items' => ['Het wachtwoord van '.$user->email.' is aangepast.'],
        ]);
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        abort_if((string) $request->user()->getKey() === (string) $user->getKey(), Response::HTTP_FORBIDDEN, 'Je kunt je eigen account hier niet verwijderen.');

        $email = $user->email;
        $user->delete();

        return back()->with('settings_status', [
            'title' => 'Gebruiker verwijderd',
            'items' => [$email.' is verwijderd.'],
        ]);
    }

    private function validatedMedewerker(Request $request): array
    {
        return $request->validate([
            'regio' => ['required', 'string', 'max:255'],
            'naam' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);
    }
}
