<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_Oauth2;

class UserController extends Controller
{
    public function auth()
    {
        /* $gclient = new Google_Client();
        $gclient->setAuthConfig(public_path('client_secret_818007677718-4e17jlg7an98mnlec984uc5cbs7tvmsd.apps.googleusercontent.com.json'));
        // $gclient->setAccessType('offline');
        $gclient->setIncludeGrantedScopes(true);
        $gclient->addScope([Google_Service_Oauth2::USERINFO_EMAIL, Google_Service_Oauth2::USERINFO_PROFILE]);
        $gclient->setRedirectUri('http://wayne.dev:8000/api/auth');

        $google_login_url = $gclient->createAuthUrl();

        // 登入後，導回來的網址會有 code 的參數
        if (isset($_GET['code']) && $gclient->authenticate($_GET['code'])) {
            $token = $gclient->getAccessToken(); // 取得 Token
            // $token data: [
            // 'access_token' => string
            // 'expires_in' => int 3600
            // 'scope' => string 'https://www.googleapis.com/auth/userinfo.email openid https://www.googleapis.com/auth/userinfo.profile' (length=102)
            // 'created' => int 1550000000
            // ];
            $gclient->setAccessToken($token); // 設定 Token

            $oauth = new Google_Service_Oauth2($gclient);
            $profile = $oauth->userinfo->get();

            $uid = $profile->id; // Primary key
            print_r($profile); // 自行取需要的內容來使用囉~
        } else {
            return redirect($google_login_url);
        } */

        $fb = new \Facebook\Facebook([
            'app_id' => '309561803416685',
            'app_secret' => 'e684fc11c48d4496f910dd2090ffb509',
            'default_graph_version' => 'v7.0',
            //'default_access_token' => '{access-token}', // optional
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['email'];

        $loginUrl = $helper->getLoginUrl('https://932e7c55c397.ngrok.io/api/callback', $permissions);

        return redirect($loginUrl);
    }

    public function callback()
    {
        if (!session_id()) {
            session_start();
        }

        $app_id = '309561803416685'; // 把 {app_id} 換成你的應用程式編號
        $app_secret = 'e684fc11c48d4496f910dd2090ffb509';  // 把 {app_secret} 換成你的應用程式密鑰
        $fb = new \Facebook\Facebook([
            'app_id' => $app_id,
            'app_secret' => $app_secret,
            'default_graph_version' => 'v7.0',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        if (isset($_GET['state'])) {
            $helper->getPersistentDataHandler()->set('state', $_GET['state']);
        }

        try {
            $accessToken = $helper->getAccessToken();
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        // Logged in
        echo '<h3>Access Token</h3>';

        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        echo '<h3>Metadata</h3>';
        var_dump($tokenMetadata);

        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId($app_id); // Replace {app-id} with your app id
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();

        if (!$accessToken->isLongLived()) {
            // Exchanges a short-lived access token for a long-lived one
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
                exit;
            }

            echo '<h3>Long-lived</h3>';
            var_dump($accessToken->getValue());
        }

        $_SESSION['fb_access_token'] = (string)$accessToken;

        // User is logged in with a long-lived access token.
        // You can redirect them to a members-only page.
        //header('Location: https://example.com/members.php');
    }
}
