<?php
if(!session_id()) {
    session_start();
}
/**
 * Created by IntelliJ IDEA.
 * User: sifantid
 * Date: 22/12/2016
 * Time: 8:05 μμ
 */

set_include_path($_SERVER["DOCUMENT_ROOT"] . "/film_buddy/src/includes/");
require_once('vendor/autoload.php');
require_once('constants.php');

$fb = new Facebook\Facebook([
    'app_id' => FBID, // Replace {app-id} with your app id
    'app_secret' => FBSECRET,
    'default_graph_version' => 'v2.2',
]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email']; // Optional permissions
$loginUrl = $helper->getLoginUrl('http://snf-730593.vm.okeanos.grnet.gr/film_buddy/src/web/fb-callback.php', $permissions);

/* Here goes the login button */
echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';