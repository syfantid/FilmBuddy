<?php

/**
 * Created by IntelliJ IDEA.
 * User: sifantid
 * Date: 22/12/2016
 * Time: 8:08 μμ
 * Handles the Facebook login server-side and background processing of Facebook data
 */
if(!session_id()) {
    session_start();
}
set_include_path($_SERVER["DOCUMENT_ROOT"] . "/film_buddy/src/includes/");
require_once('vendor/autoload.php');
require_once('constants.php');

function Redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}

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
    /*echo 'Graph returned an error: ' . $e->getMessage();
    exit;*/
    Redirect("./404.html");
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
   /* echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;*/
    Redirect("./404.html");
}

if (! isset($accessToken)) {
    /*echo 'No cookie set or no OAuth data could be obtained from cookie.';
    exit;*/
    Redirect("./404.html");
}

// Logged in
/*echo '<h3>Access Token</h3>';
var_dump($accessToken->getValue());*/

$_SESSION['fb_access_token'] = (string) $accessToken;
$since = 2015; // todo Pass the since parameter

/* ***************************************************************** */
function get_data($fb) {
    try {
        // Returns a `Facebook\FacebookResponse` object
        $response = $fb->get('/me?fields=id,name,email,likes.limit(500){name,category},posts.limit(500){message}', $_SESSION['fb_access_token']);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        /*echo 'Graph returned an error: ' . $e->getMessage();
        exit;*/
        Redirect("./404.html");
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        /*echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;*/
        Redirect("./404.html");
    }

    $user = $response->getGraphUser()->asArray();
    return $user;
}

function get_user($fb) {
    try {
        // Returns a `Facebook\FacebookResponse` object
        $response = $fb->get('/me?fields=id,name,email', $_SESSION['fb_access_token']);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        /*echo 'Graph returned an error: ' . $e->getMessage();
        exit;*/
        Redirect("./404.html");
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        /*echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;*/
        Redirect("./404.html");
    }

    $user = $response->getGraphUser()->asArray();
    return $user;
}

function get_likes($fb,$since) {
    try {
        $getPages = $fb->get('/me/likes?fields=name,category&since=' . $since, $_SESSION['fb_access_token']);
    } catch (Exception $e) {
        Redirect("./404.html");
    }
    $likes = $getPages->getGraphEdge();

    $totalLikes = array();

    if ($fb->next($likes)) {
        $likesArray = $likes->asArray();
        $totalLikes = array_merge($totalLikes, $likesArray);
        while ($likes = $fb->next($likes)) {
            $likesArray = $likes->asArray();
            $totalLikes = array_merge($totalLikes, $likesArray);
        }
    } else {
        $likesArray = $likes->asArray();
        $totalLikes = array_merge($totalLikes, $likesArray);
    }

    return $totalLikes;
}

function get_posts($fb,$since) {
    try {
        $getPosts = $fb->get('/me/posts?fields=message&since=' . $since, $_SESSION['fb_access_token']);
    } catch (Exception $e) {
        Redirect("./404.html");
    }
    $posts = $getPosts->getGraphEdge();

    $totalPosts = array();

    if ($fb->next($posts)) {
        $postsArray = $posts->asArray();
        $totalPosts = array_merge($totalPosts, $postsArray);
        while ($posts = $fb->next($posts)) {
            $postsArray = $posts->asArray();
            $totalPosts = array_merge($totalPosts, $postsArray);
        }
    } else {
        $postsArray = $posts->asArray();
        $totalPosts = array_merge($totalPosts, $postsArray);
    }

    return $totalPosts;
}

print_r(get_data($fb));
/*print_r(get_likes($fb,$since));
print_r(get_posts($fb,$since));*/

?>
