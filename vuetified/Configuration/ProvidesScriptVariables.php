<?php

namespace Vuetified\Configuration;

use Vuetified\Vuetified;
use Illuminate\Support\Facades\Auth;
use Vuetified\Contracts\InitialFrontendState;

trait ProvidesScriptVariables
{
    /**
     * Get the default JavaScript variables for Spark.
     *
     * @return array
     */
    public static function scriptVariables()
    {
        return [
            'ssl_on'       => config('websockets.ssl.on'),
            'csrfToken'    => csrf_token(),
            'env'          => config('app.env'),
            'api_endpoint' => config('app.api'),
            'sponsor'      => self::getSponsor()
        ];
    }

    protected static function getSponsor()
    {
        if ($link = request()->referrallink) {
            $user = Vuetified::user()->find($link->user_id);
            return [
                'user_id'  => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'profile'  => $user->profile
            ];
        } else {
            // We Will Return a Default Sponsor
            $user = Vuetified::user()->first()->load('profile');
            return [
                'user_id'  => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'profile'  => $user->profile
            ];
        }
    }

    protected static function getState()
    {
        return Vuetified::call(InitialFrontendState::class.'@forUser', [Auth::user()]);
    }
}
