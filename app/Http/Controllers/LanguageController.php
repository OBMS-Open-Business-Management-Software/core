<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function changeLanguage(string $locale)
    {
        Session::put('locale', $locale);

        App::setLocale($locale);

        return redirect()->back();
    }
}
