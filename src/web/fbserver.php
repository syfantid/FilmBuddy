<?php

/**
 * Created by IntelliJ IDEA.
 * User: sifantid
 * Date: 22/12/2016
 * Time: 8:08 μμ
 */
if(!session_id()) {
    session_start();
}
set_include_path($_SERVER["DOCUMENT_ROOT"] . "/film_buddy/src/includes/");
require_once('vendor/autoload.php');
require_once('constants.php');

$fb = new Facebook\Facebook([
    'app_id' => FBID, // Replace {app-id} with your app id
    'app_secret' => FBSECRET,
    'default_graph_version' => 'v2.2',
]);
$helper = $fb->getJavaScriptHelper();

try {
    $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

if (! isset($accessToken)) {
    echo 'No cookie set or no OAuth data could be obtained from cookie.';
    exit;
}

// Logged in
echo '<h3>Access Token</h3>';
var_dump($accessToken->getValue());

$_SESSION['fb_access_token'] = (string) $accessToken;

try {
    // Returns a `Facebook\FacebookResponse` object
    $response = $fb->get('/me?fields=id,name,age_range,email,gender,location,likes,feed.since(2014)', $_SESSION['fb_access_token']);
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

$user = $response->getGraphUser();

// todo PROCESS response and show LOADING PAGE
print_r($user);
// User is logged in with a long-lived access token.
// You can redirect them to a members-only page.
/*header('Location: http://snf-730593.vm.okeanos.grnet.gr/film_buddy/src/web/results.php?q=' . strtok($user['name'], " "));*/