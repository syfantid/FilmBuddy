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

    private $manager;
    private $collection;

    function __construct($host, $port, $prefix)
    {
        $this->manager = new MongoDB\Driver\Manager("mongodb://$host:$port");
        $this->collection = new MongoDB\Collection($this->manager, "movies_component.all_movies");
    }

    /**
     * @return mixed[] A sorted array of all trends from the database, values are distinct.
     */
    function getTopTrends() {
        $collection = new MongoDB\Collection($this->manager, "vakali." . $this->prefix . "_closed");
        $result = $collection->distinct(_TREND_NAME_);

        sort($result);

        return $result;
    }

    /**
     * @param $trend string The trend that we want to get the active time frames.
     * @return array An array of arrays with the active times
     *      [0] => [start => 123, end => 126],
     *      [1] => [start => 235, end => 512]
     */
    function getTimeIntervals($trend)
    {

        $collection = new MongoDB\Collection($this->manager, "vakali." . $this->prefix . "_closed");

        $result = $collection->find(["trendName" => $trend]);

        $activeTimes = array();

        $twoHours = 1000/*to seconds*/* 60/*seconds*/* 60/*minutes*/* 2/*hours*/;
        $start = 0;
        $end = 0;
        foreach ($result as $entry) {
            $aEntry = json_decode(json_encode($entry), true); /* Removes the _id field */

            $cStart = $aEntry[_TOPTREND_START_TIME_];
            $cEnd = $aEntry[_TOPTREND_END_TIME_] + $twoHours;

            if ($start == 0) {
                $start = $cStart;
                $end = $cEnd;
                continue;
            }

            /* 2h hours passed since last time topTrend */
            if ($end < $cStart) {
                /* And the active session */
                $activeTimes[] = array("start" => $start, "end" => $end);

                /* Update the variables */
                $start = $cStart;
                $end = $cEnd;
            } else {
                /* Update only the end variable */
                $end = $cEnd;
            }
        }

        if ($start != 0) {
            $activeTimes[] = array("start" => $start, "end" => $end);
        }

        return $activeTimes;
    }

    /**
     * @param $trend string The trend of the tweets that we want to take
     * @param $timeInterval array Then time frame of the tweets that we are looking. K-V array,
     *      "start" => from_time_in_millis
     *      "end" => until_time_in_millis
     * @return array Returns an array in a Key Value style
     *      The keys returned are:
     *          "text" => This is the parsedText of the tweet
     *          "timestamp" => The timestamp is in milliseconds
     *          "feelings" => A Key Value array, for key it has an emotion name and for value the
     * emotion's score. The exact keys follow:
     *              "ANGER"
     *              "DISGUST"
     *              "FEAR"
     *              "JOY"
     *              "SADNESS"
     *              "SURPRISE"
     *          "user" => The person that tweeted
     */
    function getTweets($trend, $timeInterval)
    {
        $_TWITTER_TWEET_TIMESTAMP = 'timestamp_ms';
        $start = strval($timeInterval['start']);
        $end = strval($timeInterval['end']);

        set_time_limit(600);


        $collection = new MongoDB\Collection($this->manager, "vakali." . $this->prefix . "_tweets_".$trend);

        $result = $collection->find([
            _TWEET_JSON_.".".$_TWITTER_TWEET_TIMESTAMP => [
                '$gte' => $start,
                '$lte' => $end
            ]
        ]);


        $tweets = array();

        foreach ($result as $entry) {
            /* Converts it to array but removes the _id field */
            $anEntry = json_decode(json_encode($entry), true);

            /* Things to return */
            $parsedText = $anEntry[_TWEET_PARSED_STRING_];
            $user = $anEntry["tweet"]["user"]["screen_name"];
            $timestamp = $anEntry["tweet"]["timestamp_ms"];
            $feelings = array();

            foreach($this->emotions as $emotion) {
                /* TODO: We suppress here the errors */
                @$feelings[$emotion] = $anEntry[_TWEET_EMOTION_SCORES_][$emotion];
            }

            $tweets[] = array(
                "text" => $parsedText,
                "timestamp" => $timestamp,
                "feelings" => $feelings,
                "user" => $user
            );
        }

        return $tweets;
        //return parsed text, timestamp, feelings, user
    }

    function getMovie($movieID) {
        $result = $this->collection->find([
            _MOVIE_JSON_.".".$_TWITTER_TWEET_TIMESTAMP => [
                '$gte' => $start,
                '$lte' => $end
            ]
        ]);
    }

}
