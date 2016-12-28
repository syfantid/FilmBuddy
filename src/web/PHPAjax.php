<?php
/**
 * Created by IntelliJ IDEA.
 * User: sifantid
 * Date: 22/12/2016
 * Time: 8:08 μμ
 * Handles the loading page (AJAX) during the processing of Facebook data
 */

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
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.js"></script>

    <script type="text/javascript">
        /* Display the loading gif */
        document.write('<div class="se-pre-con" id="loading"></div>');


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
            url='fbserver.php';
            http.open("GET",url, true);
            http.onreadystatechange = function()
            {
                if (http.readyState == 4)
                {
                    //Change the text when result comes.....
                    $(".se-pre-con").fadeOut("slow");
                    /*document.getElementById("content").innerHTML="http. responseText";*/
                    window.location = './results.php?q=cat';
                }
            }
            http.send(null);
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
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">




    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body onload="AjaxFunction()">
</body>
</html>