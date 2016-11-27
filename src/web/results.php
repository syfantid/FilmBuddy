<?php
session_start();
if(isset($_GET['submit'])) {
    session_unset();
}
?>
<!DOCTYPE html>

<?php

require_once('constants.php');
require_once('Connectify.php');

// SOLR instance is already running
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$rows = 16;
$start = 0;
$category = isset($_REQUEST['c']) ? $_REQUEST['c'] : false;
$urlCategory = $category;
$df = "";
/* Get query anf filters */
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
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
const ALL_CONTINENTS = array("Africa", "Asia", "Europe", "N.America", "S.America", "Oceania");

$mongo = new Connectify(DBHOST, MONGOPORT);


if ($query) {

    // if magic quotes is enabled then stripslashes will be needed
    if (get_magic_quotes_gpc() == 1) {
        $query = stripslashes($query);
    }
    /* If the number of total results is not set yet, then make an extra file_get_contents call to identify the total
    number of results*/
    if(!isset($_SESSION['total'])) {
        /*print_r("Setting session variables!");*/
        $url = reformatQuery($query, $start, $rows, $years, $imdb, $genres, $continents);

        // Just to get the total number of objects returned
        try {
            $results = file_get_contents($url);
        } catch (Exception $e) {
            // in production you'd probably log or email this error to an admin
            // and then show a special message to the user but for this example
            // we're going to show the full exception
            Redirect("./404.php");
        }
        $results = json_decode($results, true);
        $_SESSION['total'] = (int)$results['response']['numFound']; // The number of relevant movies found
        /* Number of pages needed for all comments */
        $_SESSION['totalPages'] = ceil($_SESSION['total'] / $rows);
        $newSession = true;
    } else {
        $newSession = false;
    }

    /* Set current page and offset from the first comment (depends on current page) */
    if(isset($_GET['currentPage']) && !$newSession) {
        $currentPage = $_GET['currentPage'];
        /* Check if currentPage is within limits (In case the user tampers with the url)*/
        if ($currentPage < 1) {
            $currentPage = 1;

        } elseif ($currentPage > $_SESSION['totalPages'] ) {
            $currentPage = $_SESSION['totalPages'] ;
        }
        $start = ($currentPage - 1) * $rows; // Calculate the offset for the page
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
    $results = false;
    /* Reformating query so that it takes into account the start and rows variables */
    $url = reformatQuery($query, $start, $rows, $years, $imdb, $genres, $continents);
    // in production code you'll always want to use a try /catch for any
    // possible exceptions emitted  by searching (i.e. connection
    // problems or a query parsing error)
    try {
        $results = file_get_contents($url);
    } catch (Exception $e) {
        // in production you'd probably log or email this error to an admin
        // and then show a special message to the user but for this example
        // we're going to show the full exception
        Redirect("./404.php");
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

function reformatLink($query, $years, $imdb, $genres, $continents) {
    global $df_letter;
    $link = $_SERVER['PHP_SELF'] . "?" . $df_letter ."=" . $query;
    if(!empty($genres)) {
        foreach ($genres as $genre) {
            $link .= "&genre[]=" . $genre;
        }
    }
    if(!empty($years)) {
        $link .= "&year=" . $years[0] . "," . $years[1];
    }
    if(!empty($imdb)) {
        $link .= "&imdb=" . $imdb[0] . "," . $imdb[1];
    }
    if(!empty($continents)) {
        foreach ($continents as $continent) {
            $link .= "&continents[]=" . $continent;
        }
    }
    return $link;
}

function reformatQuery($query, $start, $rows, $years, $imdb, $genres, $continents) {
    $url = formatSimpleQuery($query, $start, $rows);
    // Add filters
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
    return $url;
}

function formatSimpleQuery($query,$start,$rows) {
    global $df;
    $url = "http://" . SOLRHOST . ":" . SOLRPORT . SOLRNAME . "/movies/select?";
    $options = array("df"=>$df, "hl.fl"=>$df, "hl"=> "on", "hl.snippets"=>3, "indent"=>"on",
        "q"=>$query,"rows"=>$rows,"start"=>$start,"wt"=>"json");
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

    <!-- Custom CSS -->
    <link href="assets/css/3-col-portfolio.css" rel="stylesheet">
    <link href="assets/css/simple-sidebar.css" rel="stylesheet">

    <!-- Bootstrap Slider CSS-->
    <link href="assets/css/bootstrap-slider.min.css" rel="stylesheet">

    <!-- Bootstrap Core CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

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
                        <a href="#">About</a>
                    </li>
                    <li>
                        <a href="#">Services</a>
                    </li>
                    <li>
                        <a href="#">Contact</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>

    <!-- All page content except for Navigation bar-->
    <div id="wrapper" class="toggled">

        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <ul class="sidebar-nav">
                <li class="sidebar-brand">
                    Filters
                </li>
                <!-- Form -->
                <form accept-charset="utf-8" action="results.php" method="get">
                    <!--Recreate the previous query link to prepend to form filtering results-->
                    <input type="hidden" name=<?php echo $df_letter;?> value="<?php echo $urlQuery;?>">

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

                    <li><input type="submit" class="btn btn-primary" id="submit_button" role="button" name="submit" value="Apply magic!"/></li>
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
                        <a href="#menu-toggle" class="btn btn-primary" id="menu-toggle">Filters</a>
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
                                    ?>
                                    <div class="browse-movie-wrap col-xs-7 col-sm-4 col-md-3 col-lg-3 portfolio-item hover10">
                                        <?php $movieURL = "http://" . DBHOST . ":" . WEBPORT . "/FilmBuddy/web/movie.php?id=" . $doc['id'];?>

                                            <figure class="exact">
                                                <a href="<?php echo $movieURL;?>">
                                                <?php if($doc['icon'] == "N/A" || !strpos(@get_headers(urldecode($doc['icon']))[0],"200")) {
                                                    $poster = $mongo->getPosterURL($doc['id']);
                                                    if($poster != "N/A") {?>
                                                        <img class="img-responsive height-adjust" src="<?php echo $poster;?>"
                                                         alt="Movie poster thumbnail" width="180" height="255"><?php
                                                    } else { ?>
                                                        <img class="img-responsive height-adjust"
                                                             src="images/keep-calm-but-sorry-no-poster.jpg"
                                                             alt="Movie poster not available thumbnail" width="180"
                                                             height="255"> <?php
                                                    }
                                                } else { ?>
                                                    <img class="img-responsive height-adjust" src="<?php echo $doc['icon']; ?>"
                                                         alt="Movie poster thumbnail"  width="180" height="255"> <?php
                                                }
                                                ?>
                                                <figcaption class="hidden-xs hidden-sm">
                                                    <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
                                                    <h4 class="rating"><?php echo $doc['imdb_rating'];?> / 10</h4>
                                                    <h4><?php echo $doc['genre'];?></h4>
                                                </figcaption>
                                                </a>
                                            </figure>

                                        <h3>
                                            <a href="<?php echo $movieURL;?>"><?php echo $doc['title'][0]; ?></a>
                                        </h3>
                                        <p id="genre"><?php echo $doc['genre']; ?></p>
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


                            <hr>

                            <!-- Pagination -->
                            <div class="row text-center">
                                <div class="col-lg-12">
                                    <?php
                                    /* URL for redirection */
                                    $url = reformatLink($urlQuery, $years, $imdb, $genres, $continents);

                                    /* Show previous pages' links */
                                    if ($currentPage > 1) { // First page doesn't have a previous page
                                        echo " <a href='$url&currentPage=1' class='btn btn-primary btn-lg' role='button'>First</a> "; // Link to first page
                                        $previousPage = $currentPage - 1; // Previous page number
                                        echo " <a href='$url&currentPage=$previousPage' class='btn btn-primary btn-lg' role='button'>Previous</a> "; // Link to previous page
                                        if ($currentPage == $_SESSION['totalPages'] ) {
                                            echo " <span class='btn btn-primary btn-lg disabled' role='button'>Next</span> ";
                                            echo " <span class='btn btn-primary btn-lg disabled' role='button'>Last</span> ";
                                        }
                                    }

                                    /* Show next pages' links */
                                    if ($currentPage != $_SESSION['totalPages'] ) { // Last page doesn't have a next page
                                        if ($currentPage == 1) {
                                            echo " <span class='btn btn-primary btn-lg disabled'>First</span> ";
                                            echo " <span class='btn btn-primary btn-lg disabled'>Previous</span> ";
                                        }
                                        $nextPage = $currentPage + 1; // Next page number
                                        echo " <a href='$url&currentPage=$nextPage' class='btn btn-primary btn-lg' role='button'>Next</a> "; // Link to next page
                                        echo " <a href='$url&currentPage={$_SESSION['totalPages']} ' class='btn btn-primary btn-lg' role='button'>Last</a>"; // Link to last page
                                    }
                                    ?>
                                </div>
                            </div>
                        <!-- /.row --> <?php
                        } ?>
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
