<?php

require_once('constants.php');

// SOLR instance is already running
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$rows = 10;
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
        $start = ($currentPage - 1) * 10; // Calculate the offset for the next page
        if ($currentPage == $totalPages) { // The rows number may change only for the last page
            $rows = $total - $start;
        }
    } else { // Uninitialized - First page results
        $currentPage = 1;
        $start = 0;
        if ($currentPage == $totalPages) {
            $start = ($currentPage - 1) * 10; // Calculate the offset for the next page
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

<?php
// display results
if ($results)
{
    $results = json_decode($results,true);
    ?>
    <html>
    <html lang="en">
    <head>
        <!-- Mandatory meta tags for Bootstrap -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Slabo+27px">
        <title>Film Buddy: A Social Movie Recommender System using Semantics</title>
    </head>
    <body>
    <div class="container-fluid">
        <div class="row">
            <ul class="nav nav-pills nav-stacked">

            </ul>
            <div class="col-md-12">Results <?php echo $start+1; ?> - <?php echo $start+$rows;?> of <?php echo $total; ?>:</div>
            <div class="col-md-12">
                <ul>
                    <?php
                    // iterate result documents
                    foreach ($results['response']['docs'] as $doc)
                    {
                        ?>
                        <li>
                            <div class="table-responsive">
                                <table class="table-bordered table-striped table-hover">
                                    <?php
                                    // iterate document fields / values
                                    foreach ($doc as $field => $value) {
                                        if (is_array($value)) { // $value is an array (id, version, not useful information)
                                        ?>
                                            <tr>
                                                <th class="text-left"><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?></th>
                                                <td class="text-center"><?php echo htmlspecialchars($value[0], ENT_NOQUOTES, 'utf-8'); ?></td>
                                            </tr>
                                        <?php
                                        } elseif ($field != "id" && $field != "_version") {
                                        ?>
                                            <tr>
                                                <th class="text-left"><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?></th>
                                                <td class="text-center"><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></td>
                                            </tr>
                                        <?php
                                        }
                                    }
                                    ?>
                                </table>
                            </div>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
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
            }
            ?>
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
</body>
</html>