<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Controllers\Web;

use Illuminate\Routing\Controller;

class ProfileController extends Controller
{
    public function show()
    {
        return view('kango-auth::auth.profile', [
            'user' => request()->user(),
        ]);
    }
}
