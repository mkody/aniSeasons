<?php
// if (PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) die('cli only');
error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once __DIR__ . '/funcs.php';
require_once __DIR__ . '/config.php';

// Create list of seasons
$lists = [];
foreach (range(2010, 2050) as $year) {
    $lists[] = $year . ' Winter';
    $lists[] = $year . ' Spring';
    $lists[] = $year . ' Summer';
    $lists[] = $year . ' Fall';
}

// Create object where our data is saved
$j = [];

// Ger our watchlist and parse on the customLists
$query = file_get_contents(__DIR__ . '/lists.graphql');
$variables = [
    "userName" => $user
];
$response = graphql('https://graphql.anilist.co', $query, json_encode($variables));

foreach ($response->data->MediaListCollection->lists as $l) {
    if (in_array($l->name, $lists)) {
        foreach($l->entries as $entry) {
            $entry->media->coverImage->large = _signImage($entry->media->coverImage->large);
            $j[$l->name][] = $entry;
        }
    }
}

// Re-order
$o = [];
foreach ($lists as $l) {
    if (array_key_exists($l, $j)) {
        $o[$l] = $j[$l];
    }
}

// Save everything
file_put_contents(__DIR__ . '/shows.json', json_encode($o));
