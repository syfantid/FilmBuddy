<!DOCTYPE html>

<?php

require_once('constants.php');

// SOLR instance is already running
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$rows = 9;
$start = 0;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$urlQuery = $query;
$options = array();

if ($query)

    // if magic quotes is enabled then stripslashes will be needed
    if (get_magic_quotes_gpc() == 1)
    {
        $query = stripslashes($query);
    }
{
    // Just to get the total number of objects returned
    try{
        $results = file_get_contents(formatSimpleQuery($query,$start,1));
    } catch (Exception $e) {
        // in production you'd probably log or email this error to an admin
        // and then show a special message to the user but for this example
        // we're going to show the full exception
        die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
    }
    $results = json_decode($results,true);
    $total = (int) $results['response']['numFound']; // The number of relevant movies found
    /* Number of pages needed for all comments */
    $totalPages = ceil($total / $rows);

    /* Set current page and offset from the first comment (depends on current page) */
    if(isset($_GET['currentPage'])) {
        $currentPage = $_GET['currentPage'];
        /* Check if currentPage is within limits (In case the user tampers with the url)*/
        if ($currentPage < 1) {
            $currentPage = 1;

        } elseif ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $start = ($currentPage - 1) * $rows; // Calculate the offset for the page
        if ($currentPage == $totalPages) { // The rows number may change only for the last page
            $rows = $total - $start;
        }
    } else { // Uninitialized - First page results
        $currentPage = 1;
        $start = 0;
        if ($currentPage == $totalPages) {
            $start = ($currentPage - 1) * $rows; // Calculate the offset for the page
            $rows = $total - $start;
        }
    }
    $results = false;

    // in production code you'll always want to use a try /catch for any
    // possible exceptions emitted  by searching (i.e. connection
    // problems or a query parsing error)
    try
    {
        $results = file_get_contents(formatSimpleQuery($query,$start,$rows));
    } catch (Exception $e) {
        // in production you'd probably log or email this error to an admin
        // and then show a special message to the user but for this example
        // we're going to show the full exception
        die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
    }
}

function formatSimpleQuery($query,$start,$rows) {
    $url = "http://" . SOLRHOST . ":" . SOLRPORT . SOLRNAME . "/movies/select?";
    $options = array("df"=>"semantics_plot","indent"=>"on","q"=>$query,"rows"=>$rows,"start"=>$start,"wt"=>"json");
    $url .= http_build_query($options,'','&');
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

    <title>Film Buddy: A Social Movie Recommender Engine using Semantics</title>

    <!-- Bootstrap Core CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="assets/css/3-col-portfolio.css" rel="stylesheet">

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
            $i = 0; // Results printed so far counter
            foreach ($results['response']['docs'] as $doc) {
                if($i%3==0) {
                    echo "<div class='row'>";
                } ?>
                <div class="col-md-4 portfolio-item">
                    <a href="#">
                        <?php if($doc['icon'] == "N/A" || !strpos(@get_headers(urldecode($doc['icon']))[0],"200")) { ?>
                            <img class="img-responsive" src="images/keep-calm-but-sorry-no-poster.png"
                                 alt="Movie poster thumbnail"> <?php
                        } else { ?>
                            <img class="img-responsive" src="<?php echo $doc['icon']; ?>"
                                 alt="Movie poster thumbnail"> <?php
                        }
                    ?>
                    </a>
                    <h3>
                        <a href="#"><?php echo $doc['title'][0]; ?></a>
                    </h3>
                    <p><?php echo $doc['genre']; ?></p>
                </div>
                <?php
                if($i%3 == 2) {
                    echo "</div>";
                }
                $i++;
            }
            ?>


            <hr>

            <!-- Pagination -->
            <div class="row text-center">
                <div class="col-lg-12">
                    <?php

                    /* Show previous pages' links */
                    if ($currentPage > 1) { // First page doesn't have a previous page
                        echo " <a href='{$_SERVER['PHP_SELF']}?q=$urlQuery&currentPage=1' class='btn btn-default btn-lg' role='button'>First</a> "; // Link to first page
                        $previousPage = $currentPage - 1; // Previous page number
                        echo " <a href='{$_SERVER['PHP_SELF']}?q=$urlQuery&currentPage=$previousPage' class='btn btn-default btn-lg' role='button'>Previous</a> "; // Link to previous page
                        if ($currentPage == $totalPages) {
                            echo " <span class='btn btn-default btn-lg disabled' role='button'>Next</span> ";
                            echo " <span class='btn btn-default btn-lg disabled' role='button'>Last</span> ";
                        }
                    }

                    /* Show next pages' links */
                    if ($currentPage != $totalPages) { // Last page doesn't have a next page
                        if ($currentPage == 1) {
                            echo " <span class='btn btn-default btn-lg disabled'>First</span> ";
                            echo " <span class='btn btn-default btn-lg disabled'>Previous</span> ";
                        }
                        $nextPage = $currentPage + 1; // Next page number
                        echo " <a href='{$_SERVER['PHP_SELF']}?q=$urlQuery&currentPage=$nextPage' class='btn btn-default btn-lg' role='button'>Next</a> "; // Link to next page
                        echo " <a href='{$_SERVER['PHP_SELF']}?q=$urlQuery&currentPage=$totalPages' class='btn btn-default btn-lg' role='button'>Last</a>"; // Link to last page
                    }
                    echo "
                </div>
            </div>
            <!-- /.row -->";
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
    <!-- /.container -->

    <!-- jQuery -->
    <script src="assets/js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="assets/js/bootstrap.min.js"></script>

</body>

</html>