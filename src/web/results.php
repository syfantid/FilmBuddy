<?php
session_start();
if(isset($_REQUEST['submitbtn'])) {
    $_SESSION['total'] = 0;
/*    print_r("Unsetting session...");*/
    debug_to_console("Unsetting session... ");
}
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>

<?php

set_include_path($_SERVER["DOCUMENT_ROOT"] . "/film_buddy/src/includes/");
require_once('constants.php');
require_once('Connectify.php');

// SOLR instance is already running
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
header("X-XSS-Protection: 0");

$rows = 24;
$start = 0;
$category = isset($_REQUEST['c']) ? $_REQUEST['c'] : false;
$urlCategory = $category;
$df = "";
/* Get query anf filters */
$query = false;
if(isset($_REQUEST['q'])) {
    $query = $_REQUEST['q'];
} elseif (isset($_POST['q'])) {
    $query = $_POST['q'];
} elseif (isset($_SESSION['query'])) {
    $query = $_SESSION['query'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['query'] = $query;
    header('Location:'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
    die;
}

$query = preg_replace("/,/", " ", $query);
if($query) {
    $df = "semantics_plot";
    $df_letter = "q";
}
if($category) {
    $df = "categories";
    $df_letter = "c";
    $query = $category;
}
/* Keep original URL*/
$urlQuery = $query;
$_SESSION['urlQuery'] = $urlQuery;
$genres = isset($_REQUEST['genre']) ? $_REQUEST['genre'] : array();
$years = isset($_REQUEST['year']) ? $_REQUEST['year'] : array();
if(!is_array($years)) {
    $years = explode(',', $years);
}
$imdb = isset($_REQUEST['imdb']) ? $_REQUEST['imdb'] : array();
if(!is_array($imdb)) {
    $imdb = explode(',', $imdb);
}
$continents = isset($_REQUEST['continents']) ? $_REQUEST['continents'] : array();
/*$noPageQuery = preg_replace($pattern, '', $query);*/
/* Basic options for querying*/
$options = array();
/* File that includes all unique film genres */
$filename = "files/unique_genres.txt";
$allGenres = file($filename, FILE_IGNORE_NEW_LINES);
/* Continents */
const ALL_CONTINENTS = array("Africa", "Asia", "Europe", "N.America", "L.America", "Oceania");

$mongo = new Connectify(DBHOST, MONGOPORT);

if ($query) {

    // if magic quotes is enabled then stripslashes will be needed
    if (get_magic_quotes_gpc() == 1) {
        $query = stripslashes($query);
    }
    /* If the number of total results is not set yet, then make an extra file_get_contents call to identify the total
    number of results*/
    if(!isset($_SESSION['total']) || $_SESSION['total'] == 0) {
        /*print_r("Setting session variables!");*/
        $url_context = reformatQuery($query, $start, $rows, $years, $imdb, $genres, $continents);
        $url = $url_context[0];
        $context = $url_context[1];
        // Just to get the total number of objects returned
        try {
            debug_to_console("Starting fetching results!");
            $results = file_get_contents($url, false, $context);
            if(!$results) {
               // print_r("Problem 1");
                Redirect("./404.html");
            }
        } catch (Exception $e) {
            // in production you'd probably log or email this error to an admin
            // and then show a special message to the user but for this example
            // we're going to show the full exception
           Redirect("./404.html");
           //print_r($e);
            debug_to_console($e);
        }
        $results = json_decode($results, true);
        $_SESSION['total'] = (int)$results['response']['numFound']; // The number of relevant movies found
       // print_r($_SESSION['total']);
        /* Number of pages needed for all comments */
        $_SESSION['totalPages'] = ceil($_SESSION['total'] / $rows);
        $newSession = true;
    } else {
        $newSession = false;
    }

    /* Set current page and offset from the first comment (depends on current page) */
    if(isset($_GET['currentPage']) && !$newSession) {
        $currentPage = $_GET['currentPage'];
        /*print_r("Current Page: " . $currentPage . " Total Pages: " . $_SESSION['totalPages'] . " Total Results: " . $_SESSION['total']);*/

        /* Check if currentPage is within limits (In case the user tampers with the url)*/
        if ($currentPage < 1) {
            $currentPage = 1;

        } elseif ($currentPage > $_SESSION['totalPages'] ) {
            $currentPage = $_SESSION['totalPages'] ;
        }
        $start = ($currentPage - 1) * $rows; // Calculate the offset for the page
        /*print_r($start);*/
        if ($currentPage == $_SESSION['totalPages'] ) { // The rows number may change only for the last page
            $rows = $_SESSION['total'] - $start;
        }
    } else { // Uninitialized - First page results or Page after filtering
        $currentPage = 1;
        $start = 0;
        if ($currentPage == $_SESSION['totalPages'] ) {
            $start = ($currentPage - 1) * $rows; // Calculate the offset for the page
            $rows = $_SESSION['total'] - $start;
        }
    }
    /*print_r(" Start: " . $start);*/
    $results = false;
    /* Reformating query so that it takes into account the start and rows variables */
/*    print_r(" Query" . $query . " Start " . $start . " Rows " . $rows . " Years " . $years . " IMDb " . $imdb . " Genre " . $genres . " Continents " . $continents);*/
    $url_context = reformatQuery($query, $start, $rows, $years, $imdb, $genres, $continents);
    /*print_r("URL Context " . $url_context[0] . " " . $url_context[1]);*/
    $url = $url_context[0];
    $context = $url_context[1];
    try {
        $results = file_get_contents($url, false, $context);
        if(!$results) {
            Redirect("./404.html");
           // print_r("Problem 2!");
            debug_to_console("Results returned empty!");
        }
    } catch (Exception $e) {
        // in production you'd probably log or email this error to an admin
        // and then show a special message to the user but for this example
        // we're going to show the full exception
        Redirect("./404.html");
        //print_r($e);
        debug_to_console($e);
    }
}

function Redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}

function reformatLink($query, $years, $imdb, $genres, $continents, $pageNumber) {
    global $df_letter;
    if($df_letter == 'c') { // We don't need to post the category
        $link = $_SERVER['PHP_SELF'] . "?" . $df_letter . "=" . $query . "&currentPage=" . $pageNumber;
    } else { // We need to post the query
        $link = $_SERVER['PHP_SELF'] . "?currentPage=" . $pageNumber;
    }
    if (!empty($genres)) {
        foreach ($genres as $genre) {
            $link .= "&genre[]=" . $genre;
        }
    }
    if (!empty($years)) {
        $link .= "&year=" . $years[0] . "," . $years[1];
    }
    if (!empty($imdb)) {
        $link .= "&imdb=" . $imdb[0] . "," . $imdb[1];
    }
    if (!empty($continents)) {
        foreach ($continents as $continent) {
            $link .= "&continents[]=" . $continent;
        }
    }
    return $link;
}

function reformatQuery($query, $start, $rows, $years, $imdb, $genres, $continents) {
    $url = formatSimpleQuery($start, $rows);
    // Create the URL with GET parameters; Add filters
    if(!empty($genres)) {
        $url = addGenreFilter($genres, $url);
    }
    if(!empty($years)) {
        $url = addYearFilter($years, $url);
    }
    if(!empty($imdb)) {
        $url = addRatingFilter($imdb, $url);
    }
    if(!empty($continents)) {
        $url = addContinentFilter($continents, $url);
    }

    $postdata = http_build_query(
        array(
            'q' => $query
        )
    );

    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'ignore_errors' => 'true',
            'content' => $postdata
        )
    );

    $context  = stream_context_create($opts);

    return array($url,$context);
}

function formatSimpleQuery($start,$rows) {
    global $df;
    $url = "http://" . SOLRHOST . ":" . SOLRPORT . SOLRNAME . "/movies/select?";
    $options = array("df"=>$df, "hl.fl"=>$df, "hl"=> "on", "hl.snippets"=>3, "indent"=>"on",
        "rows"=>$rows,"start"=>$start,"wt"=>"json");
    $url .= http_build_query($options,'','&');
    return $url;
}

function addFilter($extra, $url) {
    $url .= "&";
    $url .= http_build_query($extra, '', '&');
    return $url;
}

function addGenreFilter($genres, $url) {
    $genresString = "";
    foreach ($genres as $genre) {
        $genresString .= $genre . "+";
    }
    $genresString = substr_replace($genresString ,"",-1);
    $extra = array("fq" => "genre:" . $genresString);
    return addFilter($extra, $url);
}

function addContinentFilter($continents, $url) {
    $continentsString = "(";
    foreach ($continents as $continent) {
        $continentsString .= $continent . " OR ";
    }
    $continentsString = substr($continentsString, 0, -4);
    $continentsString .= ")";
    $extra = array("fq" => "continents:" . $continentsString);
    return addFilter($extra, $url);
}

function addYearFilter($years, $url) {
    $extra = array("fq" => "year:[" . $years[0] . " TO " . $years[1] . "]");
    return addFilter($extra, $url);
}

function addRatingFilter($imdb, $url) {
    $extra = array("fq" => "imdb_rating:[" . $imdb[0] . " TO " . $imdb[1] . "]");
    return addFilter($extra, $url);
}

function get_highlights($snippets) {
    $interests = array();
    if(!empty($snippets)) {
        foreach ($snippets as $snippet) {
            preg_match_all("/!([a-z]*)!/", $snippet, $snippet_highlights);
            /*print_r($snippet_highlights);*/
            $interests = array_merge($interests,array_map("unserialize", array_map("serialize", $snippet_highlights[1])));
        }

    }
    return array_unique($interests);
}

function get_icon_name($doc) {
    if($doc['icon'] == "N/A") {
        return $doc['icon'];
    } else {
        preg_match('/\/[A-Z]\/(.*)/', $doc['icon'], $output_array);
        return $output_array[1];
    }
}

function debug_to_console( $data ) {

    if ( is_array( $data ) )
        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

    echo $output;
}
?>

<html lang="en">

<head>
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
    <link href="assets/css/3-col-portfolio.css" rel="stylesheet">
    <link href="assets/css/simple-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">

    <!-- Bootstrap Slider CSS-->
    <link href="assets/css/bootstrap-slider.min.css" rel="stylesheet">
    <!-- Bootstrap Core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">


    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>

    <!-- jQuery -->
    <script src="assets/js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="assets/js/bootstrap.min.js"></script>

    <!-- Bootstrap Slider-->
    <script src="assets/js/bootstrap-slider.min.js"></script>

    <![endif]-->
</head>

<body>
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

    <!-- All page content except for Navigation bar-->
    <div id="wrapper">

        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <ul class="sidebar-nav">
                <li class="sidebar-brand">
                    Filters
                </li>
                <!-- Form -->
                <form accept-charset="utf-8" action="results.php" method="get">


                    <!--Dropdown menu for film genres filter-->
                    <li class="dropdown">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="button-group">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Genres <b class="caret"></b></a>
                                        <ul class="dropdown-menu scrollable-menu" id="filters">
                                            <?php
                                            foreach($allGenres as $genre) {
                                                ?>
                                                <li>
                                                    <a href="#" class="small" data-value=<?php echo $genre?>
                                                    tabIndex="-1">
                                                        <input type="checkbox" name="genre[]" id="genre"
                                                               value="<?php echo $genre;?>"
                                                            <?php if(in_array($genre, $genres)) echo "checked='checked'"; ?>
                                                        />
                                                        &nbsp;<?php echo $genre ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            ?>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <!--End of film genres filter-->

                    <!--Slider for film year filter-->
                    <li class="dropdown">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="button-group">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Year <b class="caret"></b></a>
                                        <ul class="dropdown-menu" id="filters">
                                            <li id="slider_li"><input id="year" name="year" type="text" class="span2" value="" data-slider-min="1900"
                                                       data-slider-max="2016" data-slider-step="5"
                                                       data-slider-value="[<?php if(!empty($years)) echo $years[0];
                                                       else echo "1900";?>,<?php if(!empty($years)) echo $years[1];
                                                       else echo "2016";?>]"/></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <!--End of film year filter-->

                    <!--Slider for film IMDb Rating filter-->
                    <li class="dropdown">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="button-group">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">IMDb Rating <b class="caret"></b></a>
                                        <ul class="dropdown-menu" id="filters">
                                            <li id="slider_li"><input id="imdb" name="imdb" type="text" class="span2" value="" data-slider-min="0"
                                                       data-slider-max="10" data-slider-step="0.1"
                                                       data-slider-value="[<?php if(!empty($imdb)) echo $imdb[0];
                                                       else echo "0";?>,<?php if(!empty($imdb)) echo $imdb[1];
                                                       else echo "10";?>]"/></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <!--End of film IMDb Rating filter-->

                    <!--Dropdown menu for film continent filter-->
                    <li class="dropdown">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="button-group">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Continents<b class="caret"></b></a>
                                        <ul class="dropdown-menu scrollable-menu" id="filters">
                                            <?php
                                            foreach(ALL_CONTINENTS as $continent) {
                                                ?>
                                                <li><a href="#" class="small" data-value=<?php echo $continent?>
                                                    tabIndex="-1"><input type="checkbox" id="check" name="continents[]" id="continents"
                                                                         value="<?php echo $continent;?>"
                                                            <?php if(in_array($continent, $continents)) echo "checked='checked'"; ?>
                                                        />&nbsp;<?php echo $continent ?></a></li>
                                                <?php
                                            }
                                            ?>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <!--End of film continent filter-->

                    <li><button type="submit" class="btn btn-info btn-responsive" id="submit_button" role="button" name="submitbtn" value="Apply Magic!">Apply magic!</button></li>
                </form>
                <!-- /#form-wrapper -->
            </ul>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <a href="#menu-toggle" class="btn btn-info btn-responsive" id="menu-toggle">Filters</a>
                        <!-- Page Header -->
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header">Results
                                    <small>Movies that truly suit your interests!</small>
                                </h1>
                            </div>
                        </div>

                        <!-- /.row -->
                        <?php
                        // Display results
                        if ($results) {
                            $results = json_decode($results,true);
                            ?>
                            <div class="container"><?php
                                foreach ($results['response']['docs'] as $doc) {
                                    $highlights = get_highlights($results['highlighting'][$doc['id']][$df]);
                                    if(sizeof($highlights) > 5) {
                                        $highlights = array_slice($highlights, 0, 5); // return the first five elements
                                    }
                                    ?>
                                    <div id="height-adjust" class="browse-movie-wrap col-xs-12 col-sm-6 col-md-4 col-lg-2 portfolio-item hover10">
                                        <!-- todo Change URL for production -->
                                        <?php $movieURL = "http://snf-730593.vm.okeanos.grnet.gr/film_buddy/src/web/movie.php?id=" . $doc['id'];?>

                                            <figure class="exact">
                                                <a href="<?php echo $movieURL;?>">
                                                <?php $poster = get_icon_name($doc); // Poster name from IMDB
                                                if($poster == "N/A" || !file_exists("images/" . $poster)/*|| !strpos(@get_headers(urldecode($doc['icon']))[0],"200")*/) {
                                                    $poster = $mongo->getPosterURL($doc['id']); // Poster URL from Mongo
                                                    if($poster != "N/A" && strpos($poster, 'imbd')) {?>
                                                        <img class="img-responsive height-adjust" src="<?php echo $poster;?>"
                                                         alt="Movie poster thumbnail mongo"  height="255"><?php
                                                    } else { ?>
                                                        <img class="img-responsive height-adjust"
                                                             src="images/keep-calm-but-sorry-no-poster.jpg"
                                                             alt="Movie poster not available thumbnail" 
                                                             height="255"> <?php
                                                    }
                                                } else { ?>
                                                    <img class="img-responsive height-adjust" src="images/<?php echo $poster; ?>"
                                                         alt="Movie poster thumbnail imdb"   height="255"> <?php
                                                }
                                                ?>
                                                <figcaption class="hidden-xs hidden-sm">
                                                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                                                    <h4 class="rating"><?php echo $doc['imdb_rating'];?> / 10</h4>
                                                    <h4><?php echo $doc['genre'];?></h4>
                                                </figcaption>
                                                </a>
                                            </figure>

                                        <h3 id="title">
                                            <a href="<?php echo $movieURL;?>"><?php echo $doc['title'][0]; ?></a>
                                        </h3>
                                        <!--<p id="genre"><?php /*echo $doc['genre']; */?></p>-->
                                        <p id="highlights">Because of your interest in:
                                        <?php
                                        if($df_letter == 'q') {
                                            foreach (array_values($highlights) as $highlight) {
                                                echo $highlight . " ";
                                            }
                                        } else {
                                            echo $category;
                                        }
                                        ?>
                                        </p>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>

                            <script>
                                $(document).ready(function(){
                                    $('#next').click(function(event){
                                        event.preventDefault();
                                        document.getElementById("fake_form_next").submit();
                                    });
                                    $('#first').click(function(event){
                                        event.preventDefault();
                                        document.getElementById("fake_form_first").submit();
                                    });
                                    $('#previous').click(function(event){
                                        event.preventDefault();
                                        document.getElementById("fake_form_previous").submit();
                                    });
                                    $('#last').click(function(event){
                                        event.preventDefault();
                                        document.getElementById("fake_form_last").submit();
                                    });
                                    /*$('#magic').click(function(){
                                        event.preventDefault();
                                        document.getElementById("query").submit();
                                        document.getElementById("filters").submit();
                                    });*/
                                });

                            </script>

                            <!-- Pagination -->
                            <div class="row text-center">
                                <div class="col-lg-12">
                                    <?php
                                    /* URL for redirection */
                                    /*$url = reformatLink($urlQuery, $years, $imdb, $genres, $continents, 0);*/
                                    /*print_r($currentPage);*/
                                    /* Show previous pages' links */
                                    if ($currentPage > 1) { // First page doesn't have a previous page

                                        /* Fake Button First */
                                        $firstPage = 1;
                                        $first = reformatLink($urlQuery,$years, $imdb, $genres, $continents,$firstPage);
                                        ?>
                                        <form action="<?php echo htmlspecialchars($first);?>" method="post" id="fake_form_first" style="display: none;">
                                            <input type="hidden" name="q" value="<?php echo $urlQuery; ?>"/>
                                        </form>
                                        <?php
                                        /*echo "<form action='$first' method='post' id='fake_form_first' style='display: none;'><input type='hidden' name='q' value='$urlQuery'/></form>";*/
                                        echo " <a id='first' href=# class='btn btn-info btn-responsive ' role='button'>First</a> "; // Link to first page
                                        /* End of Fake Button First */

                                        /* Fake Button Previous */
                                        $previousPage = $currentPage - 1; // Previous page number
                                        $previous = reformatLink($urlQuery,$years, $imdb, $genres, $continents,$previousPage);
                                        ?>
                                        <form action="<?php echo htmlspecialchars($previous);?>" method="post" id="fake_form_previous" style="display: none;">
                                            <input type="hidden" name="q" value="<?php echo $urlQuery; ?>"/>
                                        </form>
                                        <?php
                                        echo " <a id='previous' href=# class='btn btn-info btn-responsive ' role='button'>Previous</a> "; // Link to previous page
                                        if ($currentPage == $_SESSION['totalPages'] ) {
                                            echo " <span class='btn btn-info btn-responsive  disabled' role='button'>Next</span> ";
                                            echo " <span class='btn btn-info btn-responsive  disabled' role='button'>Last</span> ";
                                        }
                                    }

                                    /* Show next pages' links */
                                    if ($currentPage != $_SESSION['totalPages'] ) { // Last page doesn't have a next page
                                        if ($currentPage == 1) {
                                            echo " <span class='btn btn-info btn-responsive  disabled'>First</span> ";
                                            echo " <span class='btn btn-info btn-responsive  disabled'>Previous</span> ";
                                        }
                                        ?>

                                        <!-- Fake Button Next -->
                                        <?php
                                        $nextPage = $currentPage + 1; // Next page number
                                        $next = reformatLink($urlQuery,$years, $imdb, $genres, $continents,$nextPage);
                                        ?>
                                        <form action="<?php echo htmlspecialchars($next);?>" method="post" id="fake_form_next" style="display: none;">
                                            <input type="hidden" name="q" value="<?php echo $urlQuery; ?>"/>
                                        </form>
<!--                                    echo "<form action='$next' method='post' id='fake_form_next' style='display: none;'><input type='hidden' name='q' value='$urlQuery'/></form>";
-->                                     <?php
                                        echo " <a id='next' href=# class='btn btn-info btn-responsive ' role='button'>Next</a> "; // Link to first page
                                        /*echo " <a href='$url&currentPage=$nextPage' class='btn btn-info btn-responsive ' role='button'>Next</a> "; // Link to next page*/
                                        ?>
                                        <!-- End of Fake Button Next -->

                                        <!-- Fake Button Last -->
                                        <?php
                                        $lastPage = $_SESSION['totalPages']; // Last page number
                                        $last = reformatLink($urlQuery,$years, $imdb, $genres, $continents,$lastPage);
                                        ?>
                                        <form action="<?php echo htmlspecialchars($last);?>" method="post" id="fake_form_last" style="display: none;">
                                            <input type="hidden" name="q" value="<?php echo $urlQuery; ?>"/>
                                        </form>
                                        <?php
                                        echo " <a id='last' href=# class='btn btn-info btn-responsive ' role='button'>Last</a>"; // Link to last page
                                        /* End of Fake Button Last */
                                    }
                                    ?>
                                </div>
                            </div>
                        <!-- /.row --> <?php
                        }
                        ?>
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
                    </div>
                </div>
            </div>
        </div>
        <!-- /#page-content-wrapper -->
    </div>
    <!-- /#wrapper -->

    <!-- Menu Toggle Script -->
    <script>
        $("#menu-toggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
        });
    </script>

    <!-- Sidebar Dropdown Animation Script -->
    <script>
        // Add slidedown animation to dropdown
        $('.dropdown').on('show.bs.dropdown', function(e){
            $(this).find('.dropdown-menu').first().stop(true, true).slideDown();
        });

        // Add slideup animation to dropdown
        $('.dropdown').on('hide.bs.dropdown', function(e){
            $(this).find('.dropdown-menu').first().stop(true, true).slideUp();
        });
    </script>

    <!-- Slider Functionality Script for tooltips-->
    <script>
        $("#year").slider({
            tooltip: 'always'
        });

        $("#imdb").slider({
            tooltip: 'always'
        });
    </script>

    <!-- Javascript for dropdown menus -->
    <script>
        var options = [];

        $( '.dropdown-menu a' ).on( 'click', function( event ) {

            var $target = $( event.currentTarget ),
                val = $target.attr( 'data-value' ),
                $inp = $target.find( 'input' ),
                idx;

            if ( ( idx = options.indexOf( val ) ) > -1 ) {
                options.splice( idx, 1 );
                setTimeout( function() { $inp.prop( 'checked', false ) }, 0);
            } else {
                options.push( val );
                setTimeout( function() { $inp.prop( 'checked', true ) }, 0);
            }

            $( event.target ).blur();

            console.log( options );
            return false;
        });
    </script>
</body>

</html>
