<!DOCTYPE html>
<!--Basic Search page-->
<html lang="en">
<head>
    <!-- Mandatory meta tags for Bootstrap -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Slabo+27px" rel="stylesheet">
    <title>Film Buddy: A Social Movie Recommender System using Semantics</title>
</head>
<body>
<div class="container-fluid">
    <div class="page-header">
        <h1>Film Buddy</h1>
    </div>


    <div class="jumbotron">
        <form accept-charset="utf-8" action="resultsV2.php" method="get">
            <label for="q">Search:</label>
            <input id="q" name="q" type="text" placeholder="Enter your search terms" value="<?php $query = "";
            echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
            <input type="submit" class="btn btn-default btn-md" role="button"/>
        </form>
    </div>

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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>