<?php

namespace App\Http\Controllers;

use App\Models\Organisatie;
use Illuminate\Contracts\View\View;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\Response;

class OrganisatiePageController extends Controller
{
    public function show(string $id): View
    {
        try {
            $objectId = new ObjectId($id);
        } catch (\Throwable) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $organisatie = Organisatie::query()->find($objectId);

        abort_unless($organisatie instanceof Organisatie, Response::HTTP_NOT_FOUND);

        return view('pages.organisation-show', [
            'organisatie' => $organisatie,
        ]);
    }
}
