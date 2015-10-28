<?php

namespace App\Http\Middleware;

use Closure;
use Facebook\Facebook;

class FacebookMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $fb = new Facebook([
            'app_id'     => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'default_graph_version' => 'v2.5',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        try {
            if (isset($_SESSION['facebook_access_token'])) {
                $accessToken = $_SESSION['facebook_access_token'];
            } else {
                $accessToken = $helper->getAccessToken();
            }

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;

        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (isset($accessToken)) {
            $_SESSION['facebook_access_token'] = (string) $accessToken;

            return $next($request);
        }

        $permissions = ['public_profile']; // optional
        $loginUrl = $helper->getLoginUrl(env('CALLBACK'), $permissions);

        return redirect($loginUrl);


    }
}
