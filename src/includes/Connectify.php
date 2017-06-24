<?php
/**
 * Created by IntelliJ IDEA.
 * User: philip
 * Date: 2016.01.15
 * Time: 10:53 PM
 */


require 'vendor/autoload.php';

define('_MOVIE_JSON_', 'movie');
define('_COLL_INDEX_', '_id');


class Connectify {

    /*private $manager;*/
    private $collection;

    function __construct($host, $port)
    {
        /*$this->manager = new MongoDB\Driver\Manager("mongodb://$host:$port");*/
        $this->collection = (new MongoDB\Client)->movies_component->all_movies_full;
    }

    function getMovie($movieID) {
        $result = $this->collection->findOne([
            'movie_id' => $movieID
        ]);
        if($result != null) {
            return $result['movie'];
        } else {
            return null;
        }
    }

    function getPosterURL($movieID) {
        $result = $this->collection->findOne([
            'movie_id' => $movieID
        ]);
        if($result != null) {
            return $result['movie']['poster'];
        } else {
            return "N/A";
        }
    }

}
