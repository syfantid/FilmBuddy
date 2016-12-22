<!DOCTYPE html>

<?php

function Redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}

set_include_path($_SERVER["DOCUMENT_ROOT"] . "/film_buddy/src/includes/");
require_once('constants.php');
require_once('Connectify.php');

// SOLR instance is already running
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$movieID = isset($_REQUEST['id']) ? $_REQUEST['id'] : false;
$mongo = new Connectify(DBHOST, MONGOPORT);
$imdbURL = "";

if ($movieID) { // If the query is correct

    // if magic quotes is enabled then stripslashes will be needed
    if (get_magic_quotes_gpc() == 1) {
        $movieID = stripslashes($movieID);
    }

    $movie = $mongo->getMovie($movieID);
    if($movie == null) {
        Redirect("./404.php");
    }
    $imdbURL = "http://www.imdb.com/title/" . $movie['imdbID'];
    $categories = getCategories($movieID);
} else {
    Redirect("./404.php");
}

function getCategories($movieID) {
    $conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);

    // Check connection
    if ($conn->connect_error) {
        Redirect("./404.php");
    }
    $sql = "SELECT categories FROM all_movies WHERE id=" . $movieID;
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        $categories = $result->fetch_assoc()["categories"];
        return explode(',', $categories);;
    } else {
        return "";
    }
    $conn->close();
}

function getPoster($movieID) {
    $conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);

    // Check connection
    if ($conn->connect_error) {
        return "N/A";
    }
    $sql = "SELECT icon FROM all_movies WHERE id=" . $movieID;
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        $icon = $result->fetch_assoc()["icon"];
        preg_match('/\/[A-Z]\/(.*)/', $icon, $output_array);
        if(sizeof($output_array) > 1) {
            return $output_array[1];
        } else {
            return "N/A";
        }
    } else {
        return "N/A";
    }
    $conn->close();
}

function arrayToString($array) {
    $arrayString = "";
    foreach ($array as $item) {
        $arrayString .= $item . " ";
    }
    return $arrayString;
}

function getCategoryURL($category) {
    $url = "http://snf-730593.vm.okeanos.grnet.gr/film_buddy/src/web/results.php?c=" . $category;
    return $url;
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

    <title><?php echo $movie['title'];?> Page</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">

    <!-- Bootstrap Core CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/portfolio-item.css" rel="stylesheet">

    <!-- Font Awesome -->
    <script src="https://use.fontawesome.com/5c5a712d84.js"></script>


    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
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
                        <a href="privacypolicy.html">Privacy Policy</a>
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

    <!-- Page Content -->
    <div class="container">

        <!-- Portfolio Item Heading -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header"><?php echo $movie['title'];?>
                    <small><?php echo $movie['genre'];?></small>
                </h1>
            </div>
        </div>
        <!-- /.row -->

        <!-- Portfolio Item Row -->
        <div class="row">

            <div class="col-md-4">
                <?php
                $src = "images/keep-calm-but-sorry-no-poster.jpg";
                if($movie['poster'] != "N/A" && strpos($movie['poster'], 'imbd')) {
                    $src = $movie['poster'];
                } else if(getPoster($movieID) != "N/A" && getPoster($movieID) != "") {
                    $src = "images/" . getPoster($movieID);
                }
                ?>
                <img class="img-responsive" src="<?php echo $src;?>" alt="Movie poster for movie <?php echo $movie['title'];?>">
            </div>

            <div class="col-md-8">
                <h3>Synopsis</h3>
                <p><?php echo $movie['plot'];?></p>
                <h3>Movie Ratings</h3>
                <ul id="details">
                    <li class="detailWrapper">
                        <span class="imdbRatingPlugin" data-user="ur26106263"
                              data-title="<?php echo $movie['imdbID'];?>" data-style="p3">
                            <a href="http://www.imdb.com/title/<?php echo $movie['imdbID'];?>/?ref_=plg_rt_1">
                                <img src="http://g-ecx.images-amazon.com/images/G/01/imdb/plugins/rating/images/imdb_37x18.png"
                                     alt=" <?php echo $movie['title'];?> on IMDb" />
                            </a>
                        </span>
                        <script>
                            (function(d,s,id){var js,stags=d.getElementsByTagName(s)[0];
                                if(d.getElementById(id)){return;}js=d.createElement(s);
                                js.id=id;
                                js.src="http://g-ec2.images-amazon.com/images/G/01/imdb/plugins/rating/js/rating.min.js";
                                stags.parentNode.insertBefore(js,stags);})(document,'script','imdb-rating-api');
                        </script>
                    </li>
                    <?php
                    if($movie['tomatoImage'] != "N/A") { ?>
                        <li class="detailWrapper">
                            <a href="<?php echo $movie['tomatoURL'];?>">
                        <?php
                        if ($movie['tomatoImage'] == "rotten") { ?>
                                <img style="vertical-align:middle" src="images/rt-rotten.png" alt="Rotten icon">
                        <?php } else if ($movie['tomatoImage'] == "fresh") { ?>
                                <img style="vertical-align:middle" src="images/rt-fresh.png" alt="Fresh icon">
                        <?php } else { ?>
                                <img style="vertical-align:middle" src="images/rt-certified-fresh.png"
                                           alt="Certified Fresh icon">
                        <?php }?>
                            </a>
                            <span><strong><?php echo $movie['tomatoMeter']; ?></strong><small>%</small>
                                <span class="votes">  <?php echo $movie['tomatoReviews'];?> critics votes</span></span>
                        </li>
                    <?php
                    }
                    ?>
                    <?php
                    if($movie['tomatoUserRating'] != "N/A") { ?>
                        <li class="detailWrapper">
                            <a href="<?php echo $movie['tomatoURL'];?>">
                                <?php
                                if ($movie['tomatoUserRating'] >= (float)"3.5") { ?>
                                        <img style="vertical-align:middle" src="images/rt-upright.png"
                                                   alt="Upright Popcorn icon">
                                <?php } else { ?>
                                        <img style="vertical-align:middle" src="images/rt-spilled.png"
                                                   alt="Spilled Popcorn icon">
                                <?php } ?>
                             </a>
                            <span><strong><?php echo $movie['tomatoMeter']; ?></strong><small>%</small>
                                <span class="votes">  <?php echo $movie['tomatoUserReviews'];?> audience votes</span></span>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <h3>Movie Details</h3>
                <ul id="details">
                    <!--<h4>Release Date</h4>-->
                    <li class="detailWrapper">
                        <i class="fa fa-calendar-o" aria-hidden="true"></i>
                        <span><?php echo $movie['released'];?></span>
                    </li>
                    <!--<h4>Runtime</h4>-->
                    <li class="detailWrapper">
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <span><?php echo $movie['runtime'];?></span>
                    </li>
                    <li class="detailWrapper">
                        <i class="fa fa-volume-up" aria-hidden="true"></i>
                        <span><?php echo arrayToString($movie['languages']);?></span>
                    </li>
                    <li class="detailWrapper">
                        <i class="fa fa-globe" aria-hidden="true"></i>
                        <span><?php echo arrayToString($movie['countries']);?></span>
                    </li>
                    <?php
                    if($movie['awards']!="N/A") { ?>
                        <li class="detailWrapper">
                            <i class="fa fa-trophy" aria-hidden="true"></i>
                            <span><?php echo $movie['awards'];?></span>
                        </li>
                    <?php
                    } ?>
                    <h4>Writers</h4>
                    <li class="detailWrapper">
                        <i class="fa fa-pencil" aria-hidden="true"></i>
                        <span><?php echo $movie['writer'];?></span>
                    </li>
                    <h4>Director</h4>
                    <li class="detailWrapper">
                        <i class="fa fa-video-camera" aria-hidden="true"></i>
                        <span><?php echo $movie['director'];?></span>
                    </li>
                    <h4>Actors</h4>
                    <li class="detailWrapper">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        <span><?php echo $movie['actors'];?></span>
                    </li>
                    <h4>Categories</h4>
                    <li class="detailWrapper">
                        <i class="fa fa-film" aria-hidden="true"></i>
                        <span>
                            <?php
                            foreach ($categories as $category) {
                                $categoryURL = getCategoryURL($category);
                                echo "<a id='categoryURL' href='$categoryURL'>" . $category . " | " . "</a>";
                            }
                            ?>
                        </span>
                    </li>
                </ul>
            </div>

        </div>
        <!-- /.row -->

        <!-- Related Projects Row -->
        <div class="row">

            <div class="col-lg-12">
                <h3 class="page-header">Related Projects</h3>
            </div>

            <div class="col-sm-3 col-xs-6">
                <!--<iframe src="http://m.imdb.com/title/<?php /*echo $movie['imdbID'];*/?>/videogallery" target="_parent"></iframe>-->
                <a href="http://m.imdb.com/title/<?php echo $movie['imdbID'];?>/videogallery" target="popup">Watch trailer</a>
                <!--<a href="#">
                    <img class="img-responsive portfolio-item" src="http://placehold.it/500x300" alt="">
                </a>-->
            </div>

            <div class="col-sm-3 col-xs-6">
                <a href="#">
                    <img class="img-responsive portfolio-item" src="http://placehold.it/500x300" alt="">
                </a>
            </div>

            <div class="col-sm-3 col-xs-6">
                <a href="#">
                    <img class="img-responsive portfolio-item" src="http://placehold.it/500x300" alt="">
                </a>
            </div>

            <div class="col-sm-3 col-xs-6">
                <a href="#">
                    <img class="img-responsive portfolio-item" src="http://placehold.it/500x300" alt="">
                </a>
            </div>

        </div>
        <!-- /.row -->

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
    <!-- /.container -->

    <!-- jQuery -->
    <script src="assets/js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="assets/js/bootstrap.min.js"></script>

</body>

</html>
