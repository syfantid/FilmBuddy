<!DOCTYPE html>

<?php

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
        //todo Create a 404 problem page IMPORTANT!
        die("<html><head><title>SEARCH EXCEPTION</title><body>" . phpinfo() . "</body></html>");
    }
    $imdbURL = "http://www.imdb.com/title/" . $movie['imdbID'];
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
                <img class="img-responsive" src="<?php echo $movie['poster'];?>" alt="Movie poster for movie <?php echo $movie['title'];?>">
            </div>

            <div class="col-md-8">
                <h3>Synopsis</h3>
                <p><?php echo $movie['plot'];?></p>
                <h3>Movie Ratings</h3>
                <ul id="details">
                    <li class="detailWrapper">
                        <a href="<?php echo $imdbURL;?>">
                            <img style="vertical-align:middle" src="images/logo-imdb.svg" alt="IMDb logo">
                        </a>
                        <span><?php echo $movie['imdbRating'];?></span>
                        <i class="fa fa-star" aria-hidden="true"></i>
                    </li>
                    <!--todo Remove != "" condition with new database-->
                    <?php
                    if($movie['tomatoImage'] != "" && $movie['tomatoImage'] != "N/A") { ?>
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
                            <span><?php echo $movie['tomatoMeter']; ?></span>
                             </a>
                        </li>
                    <?php
                    }
                    ?>
                    <?php
                    if($movie['tomatoUserRating'] != "" && $movie['tomatoUserRating'] != "N/A") { ?>
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
                        <span><?php echo $movie['tomatoMeter']; ?></span>
                             </a>
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
                    <?php
                    if($movie['awards']!="N/A") { ?>
                        <li class="detailWrapper">
                            <i class="fa fa-trophy" aria-hidden="true"></i>
                            <span><?php echo $movie['awards'];?></span>
                        </li>
                    <?php
                    } ?>
                    <h4>Writer</h4>
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
                    <p>Copyright &copy; Your Website 2014</p>
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
