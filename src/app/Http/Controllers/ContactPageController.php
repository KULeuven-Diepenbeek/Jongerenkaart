<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;

class ContactPageController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.contact', [
            'contactEmail' => SiteSetting::contactEmail(),
        ]);
    }
}
