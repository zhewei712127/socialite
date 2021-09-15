<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToProvider($social_site)
    {
        return Socialite::driver($social_site)->redirect();
    }
    public function handleProviderCallback($social_site)
    {
        $user = Socialite::driver($social_site)->user();

        dd($user);
    }
}
