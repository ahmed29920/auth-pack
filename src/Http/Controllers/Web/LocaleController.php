<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale)
    {
        $available = config('auth-package.available_locales', []);

        if (! array_key_exists($locale, $available)) {
            abort(404);
        }

        session(['locale' => $locale]);

        return redirect()->back();
    }
}
