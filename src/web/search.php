<!DOCTYPE html>
<!--Basic Search page-->
<html lang="en">
<head>
    <title>PHP Solr Client Example</title>
</head>
<body>
<form accept-charset="utf-8" action="results.php" method="get">
    <label for="q">Search:</label>
    <input id="q" name="q" type="text" placeholder="Enter your search terms" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
    <input type="submit"/>
</form>