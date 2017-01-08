<?php
/**
 * Created by IntelliJ IDEA.
 * User: sifantid
 * Date: 22/12/2016
 * Time: 8:08 μμ
 * Handles the Facebook login client-side
 */
set_include_path($_SERVER["DOCUMENT_ROOT"] . "/film_buddy/src/includes/");
require_once('constants.php');
session_start();

function Redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Film Buddy is a Movie Recommender Engine, which identifies the user's interests
    by extracting information from their Facebook profiles and using semantic expansion. Film buddy automates the
    recommendation process and helps users find what truly suits their tastes in the blink of an eye!">
    <meta name="author" content="Sofia Yfantidou">
    <noscript>
        <meta http-equiv="refresh" content="0; url=./javascriptError.html" />
    </noscript>

    <title>Film Buddy: A Social Movie Recommender Engine using Semantics</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">

    <!-- Custom CSS -->
    <link href="assets/css/index-style.css" rel="stylesheet">
    <!-- Bootstrap Core CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<script>
    // This is called with the results from from FB.getLoginStatus().
    function statusChangeCallback(response) {
        /*console.dir(response);*/
        console.log('statusChangeCallback');
        console.log(response);

        // The response object is returned with a status field that lets the
        // app know the current login status of the person.
        // Full docs on the response object can be found in the documentation
        // for FB.getLoginStatus().
        if (response.status === 'connected') {
            // Logged into your app and Facebook.
            redirect();
        } else if (response.status === 'not_authorized') {
            // The person is logged into Facebook, but not your app.
            /*document.getElementById('status').innerHTML = 'Please log ' +
                'into this app.';*/
        } else {
            // The person is not logged into Facebook, so we're not sure if
            // they are logged into this app or not.
            /*document.getElementById('status').innerHTML = 'Please log ' +
                'into Facebook.';*/
        }
    }

    // This function is called when someone finishes with the Login
    // Button.  See the onlogin handler attached to it in the sample
    // code below.
    function checkLoginState() {
        FB.getLoginStatus(function(response) {
            statusChangeCallback(response);
        });
    }

    window.fbAsyncInit = function() {
        FB.init({
            appId      : '<?php echo FBID;?>',
            cookie     : true,  // enable cookies to allow the server to access
                                // the session
            xfbml      : true,  // parse social plugins on this page
            version    : 'v2.8' // use graph api version 2.8
        });

        // Now that we've initialized the JavaScript SDK, we call
        // FB.getLoginStatus().  This function gets the state of the
        // person visiting this page and can return one of three states to
        // the callback you provide.  They can be:
        //
        // 1. Logged into your app ('connected')
        // 2. Logged into Facebook, but not your app ('not_authorized')
        // 3. Not logged into Facebook and can't tell if they are logged into
        //    your app or not.
        //
        // These three cases are handled in the callback function.

        FB.getLoginStatus(function(response) {
            statusChangeCallback(response);
        });

    };

    // Load the SDK asynchronously with friends
    (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.8&appId=<?php echo FBID?>";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

    // Here we run a very simple test of the Graph API after login is
    // successful.  See statusChangeCallback() for when this call is made.
    function redirect() {
        console.log('Welcome!  Fetching your information.... ');
        FB.api('/me', function(response) {
            console.log('Successful login for: ' + response.name);
            /*document.getElementById('status').innerHTML =
                'Thanks for logging in, ' + response.name + '!';*/
            window.location = 'PHPAjax.php';
            /*response*/
        });
    }
</script>

<!--
  Below we include the Login Button social plugin. This button uses
  the JavaScript SDK to present a graphical Login button that triggers
  the FB.login() function when clicked.
-->

<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">Film Buddy</a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li>
                    <a href="about.html">About</a>
                </li>
                <li>
                    <a href="feelingLucky.php">Feeling Lucky</a>
                </li>
                <li>
                    <a href="privacypolicy.html">Privacy Policy</a>
                </li>
                <li>
                    <a href="contact.html">Contact</a>
                </li>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container -->
</nav>

<!-- Page Content -->
<div class="container">
    <div class="row">
        <div class="col-lg-12 text-center">
            <img src="images/logo_cropped.png" class="img-responsive" style="max-height: 90px; margin-top: 30px;margin-left: auto; margin-right: auto;" alt="Film Buddy Logo">
            <p class="lead subHeader">A Social Movie Recommender Engine using Semantics</p>
            <!--<div class="jumbotron vertical-center">-->
                <div class="container text-center">
                    <h4 class="header">Film Buddy discovers your new favorite movies!</h4>
                    <h5 class="subHeader">Let us get to know you better by continuing with Facebook</h5>
                    <div id="loginDiv">
                        <fb:login-button id="login" size="large"
                                         data-show-faces="true"
                                         data-auto-logout-link="false" scope="public_profile,email,user_likes,user_posts"
                                         onlogin="checkLoginState();">
                                         Continue with Facebook
                        </fb:login-button>
                    </div>
                    <div id="status"></div>
                </div>
    <!--</div>-->
    <!-- /.row -->

</div>
<!-- /.container -->

        <hr>
        <!-- Footer -->
        <footer>
            <div class="row">
                <div class="col-lg-12">
                    <p>Copyright &copy; Film Buddy 2016</p>
                </div>
            </div>
            <!-- /.row -->
        </footer>

<!-- jQuery Version 1.11.1 -->
<script src="assets/js/jquery.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="assets/js/bootstrap.min.js"></script>

</body>
</html>