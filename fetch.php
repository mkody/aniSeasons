<?php
// if (PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) die('cli only');
error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once __DIR__ . '/funcs.php';
require_once __DIR__ . '/config.php';

// Create list of seasons
$lists = [];
foreach (range(2020, 2030) as $year) {
    $lists[] = $year . ' Winter';
    $lists[] = $year . ' Spring';
    $lists[] = $year . ' Summer';
    $lists[] = $year . ' Fall';
}

// Find current season
$query = file_get_contents(__DIR__ . '/airingSeasons.graphql');
$variables = [
    "now" => strtotime('now')
];
$response = graphql('https://graphql.anilist.co', $query, json_encode($variables));
$season = [
    "int" => 0,
    "string" => ''
];
foreach ($response->data->Page->airingSchedules as $s) {
    if ($s->media->seasonInt > $season['int']) {
        $season = [
            "int" => $s->media->seasonInt,
            "string" => $s->media->seasonYear . '-' . ucwords(strtolower($s->media->season))
        ];
    }
}
file_put_contents(__DIR__ . '/currentSeason.txt', $season['string']);

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
        $j[$l->name] = $l->entries;
    }
}

// Re-order
$o = [];
foreach ($lists as $l) {
    $o[$l] = $j[$l];
}

// Save everything
file_put_contents(__DIR__ . '/shows.json', json_encode($o));
