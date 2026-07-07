<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Controllers\Web;

use Illuminate\Routing\Controller;

class ProfileController extends Controller
{
    public function show()
    {
        return view('laravel-auth-kit::auth.profile', [
            'user' => request()->user(),
        ]);
    }
}
