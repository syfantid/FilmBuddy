<?php

require_once('constants.php');

// SOLR instance is already running
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$rows = 10;
$start = 0;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$urlQuery = $query;

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
    $url = "http://" . SOLRHOST . ":" . SOLRPORT . SOLRNAME . "/movies/select?df=semantics_plot&indent=on&q=(" . $query
        . ")&rows=" . $rows . "&start=" . $start . "&wt=json";
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
    <body>
    <div>Results <?php echo $start+1; ?> - <?php echo $start+$rows;?> of <?php echo $total; ?>:</div>
    <ol>
        <?php
        // iterate result documents
        foreach ($results['response']['docs'] as $doc)
        {
            ?>
            <li>
                <table style="border: 1px solid black; text-align: left">
                    <?php
                    // iterate document fields / values
                    foreach ($doc as $field => $value) {
                        if (is_array($value)) { // $value is an array (id, version, not useful information)
                        ?>
                            <tr>
                                <th><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?></th>
                                <td><?php echo htmlspecialchars($value[0], ENT_NOQUOTES, 'utf-8'); ?></td>
                            </tr>
                        <?php
                        }
                    }
                    ?>
                </table>
            </li>
            <?php
        }
        ?>
    </ol>
    <?php

    /* Show previous pages' links */
    if ($currentPage > 1) { // First page doesn't have a previous page
        echo " <a href='{$_SERVER['PHP_SELF']}?q=$urlQuery&currentPage=1' class='button'>First</a> "; // Link to first page
        $previousPage = $currentPage - 1; // Previous page number
        echo " <a href='{$_SERVER['PHP_SELF']}?q=$urlQuery&currentPage=$previousPage' class='button'>Previous</a> "; // Link to previous page
        if ($currentPage == $totalPages) {
            echo " <span class='disabled button'>Next</span> ";
            echo " <span class='disabled button'>Last</span> ";
        }
    }

    /* Show next pages' links */
    if ($currentPage != $totalPages) { // Last page doesn't have a next page
        if ($currentPage == 1) {
            echo " <span class='disabled button'>First</span> ";
            echo " <span class='disabled button'>Previous</span> ";
        }
        $nextPage = $currentPage + 1; // Next page number
        echo " <a href='{$_SERVER['PHP_SELF']}?q=$urlQuery&currentPage=$nextPage' class='button'>Next</a> "; // Link to next page
        echo " <a href='{$_SERVER['PHP_SELF']}?q=$urlQuery&currentPage=$totalPages' class='button'>Last</a>"; // Link to last page
    }
}
?>
</body>
</html>