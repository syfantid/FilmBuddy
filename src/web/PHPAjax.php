<?php
/**
 * Created by IntelliJ IDEA.
 * User: sifantid
 * Date: 22/12/2016
 * Time: 8:08 μμ
 * Handles the loading page (AJAX) during the processing of Facebook data
 */
if(isset($_SESSION['urlQuery'])) {
    header('Location:' . $_SESSION['urlQuery']);
    exit;
}

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
    <link href="https://fonts.googleapis.com/css?family=Amatic+SC" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">

    <!-- jQuery Version 3.1.1 -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>-->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.js"></script>


    <script type="text/javascript">
        /* Display the loading gif */
        document.write('<div class="se-pre-con" id="loading"><br><br><br><br><br>' +
            'This might take a minute... Working our magic!<br><span class="subHeader2">' +
            'Film Buddy is a tool that gives you personalized ' +
            'movie recommendations. <br>The recommendations are based on the pages you\'ve liked and the things you\'ve ' +
            'posted on Facebook.</span></div>');


        //Ajax Function
        function getHTTPObject()
        {
            var xmlhttp;
            if (window.ActiveXObject)
            {
                try
                {
                    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
                }
                catch (e)
                {
                    try
                    {
                        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    catch (E)
                    {
                        xmlhttp = false;
                    }
                }
            }
            else
            {
                xmlhttp = false;
            }
            if (window.XMLHttpRequest)
            {
                try
                {
                    xmlhttp = new XMLHttpRequest();
                }
                catch (e)
                {
                    xmlhttp = false;
                }
            }
            return xmlhttp;
        }
        //HTTP Objects..
        var http = getHTTPObject();

        //Function which we are calling...
        function AjaxFunction()
        {
            url = 'fbserver.php';
            http.open("GET",url, true);
            http.onreadystatechange = function()
            {
                if (http.readyState == 4)
                {
                    $(".se-pre-con").fadeOut("slow");
                    var query = http.responseText; // The user's interests as a string

                    $('#query').tagsinput('add', query);
                    /*document.getElementById('content').innerHTML = query;*/

                }
            }
            http.send(null);
        }

        function post(path, params, method) {
            method = method || "post"; // Set method to post by default if not specified.

            // The rest of this code assumes you are not using a library.
            // It can be made less wordy if you use one.
            var form = document.createElement("form");
            form.setAttribute("method", method);
            form.setAttribute("action", path);

            for(var key in params) {
                if(params.hasOwnProperty(key)) {
                    var hiddenField = document.createElement("input");
                    hiddenField.setAttribute("name", key);
                    hiddenField.setAttribute("value", params[key]);
                    hiddenField.setAttribute("data-role","tagsinput");

                    var btn = document.createElement("input");
                    btn.setAttribute("type", "hidden");
                    btn.setAttribute("name", "submitbtn");
                    btn.setAttribute("value", "");

                    form.appendChild(hiddenField);
                    form.appendChild(btn);
                }
            }

            document.body.appendChild(form);
            form.submit();
        }
    </script>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Film Buddy is a Movie Recommender Engine, which identifies the user's interests
    by extracting information from their Facebook profiles and using semantic expansion. Film buddy automates the
    recommendation process and helps users find what truly suits their tastes in the blink of an eye!">
    <meta name="author" content="Sofia Yfantidou">
    <title>Film Buddy: A Social Movie Recommender Engine using Semantics</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">

    <!-- Custom CSS -->
    <link href="assets/css/index-style.css" rel="stylesheet">
    <!-- Bootstrap Core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!-- Tag Cloud Integration -->
    <link rel="stylesheet" href="assets/css/bootstrap-tagsinput.css">
    <script src="assets/js/bootstrap-tagsinput.js"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body onload="AjaxFunction()">

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
                    <a target="_blank" href="https://goo.gl/forms/OxbdOOS1ZaXlEhXg1">Feedback</a>
                </li>
                <li>
                    <a href="evaluation.html">Evaluation</a>
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

            <form method="post" action="./results.php">
                <h2 class="header">These are the things we think you like! Feel free to disagree!</h2>
                <h4 class="subHeader">Press the button on the bottom of this page to get our recommendations!</h4>
                <input name="q" id="query" data-role="tagsinput" value=""/>
                <br>
                <input type="submit" name="submitbtn" id="submitButton" class="btn btn-default btn-md" role="button">
            </form>


<!--            <input type="submit" name="submitbtn" id="submitButton" class="btn btn-default btn-md" role="button" onclick="post('./results.php', {q: http.responseText});"/>
            <div id="content"></div>-->

        </div>
    </div>
    <!-- /.row -->

</div>
<!-- /.container -->


<!-- Bootstrap Core JavaScript -->
<script src="assets/js/bootstrap.min.js"></script>

</body>

</html>