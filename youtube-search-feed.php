<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

header("Content-Type: application/rss+xml; charset=UTF-8");
date_default_timezone_set('UTC');

require_once __DIR__ . '/vendor/autoload.php';

use PicoFeed\Syndication\Rss20FeedBuilder;
use PicoFeed\Syndication\Rss20ItemBuilder;

if (!array_key_exists('q', $_GET) || !array_key_exists('api_key', $_GET)) {
    exit(0);
}

$API_KEY = $_GET['api_key'];
$SEARCH_TEXT = $_GET['q'];

$youtube = new Madcoda\Youtube\Youtube(array('key' => $API_KEY));
$videoResult = $youtube->searchAdvanced(array(
    'q' => $SEARCH_TEXT,
    'type' => 'video',
    'part' => 'id, snippet',
    'maxResults' => 10,
    'safeSearch' => 'none',
    'order' => 'date'
));

$feedBuilder = Rss20FeedBuilder::create()
    ->withTitle('YouTube feed for ' . htmlspecialchars($SEARCH_TEXT))
    ->withSiteUrl('https://www.youtube.com/results?q=' . urlencode($SEARCH_TEXT))
    ->withDate(new DateTime());

foreach ($videoResult as $video) {
    $date = new DateTime();
    $date->setTimestamp(strtotime($video->snippet->publishedAt));
    $feedBuilder
        ->withItem(Rss20ItemBuilder::create($feedBuilder)
            ->withId($video->id->videoId)
            ->withTitle(htmlspecialchars($video->snippet->title))
            ->withAuthor($video->snippet->channelTitle, $url = 'https://www.youtube.com/channel/'.$video->snippet->channelId)
            ->withUrl('https://www.youtube.com/watch?v=' . urlencode($video->id->videoId))
            ->withPublishedDate($date)
            ->withSummary($video->snippet->description)
            ->withContent('<iframe id="player" type="text/html" width="800" height="600"
  src="http://www.youtube.com/embed/' . $video->id->videoId . '"
  frameborder="0"></iframe>')
        );
}

echo $feedBuilder->build();
