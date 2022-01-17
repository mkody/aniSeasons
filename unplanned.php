<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

if (PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) {
    $shows = json_decode(file_get_contents(__DIR__ . '/unplanned.json'));
} else {
    require_once __DIR__ . '/funcs.php';
    require_once __DIR__ . '/config.php';

    // Create list of seasons
    $lists = [];
    foreach (range(2020, 2050) as $year) {
        $lists[] = $year . ' Winter';
        $lists[] = $year . ' Spring';
        $lists[] = $year . ' Summer';
        $lists[] = $year . ' Fall';
    }

    // Create object where our data is saved
    $shows = [];

    $query = file_get_contents(__DIR__ . '/plantowatch.graphql');
    $hasNextPage = true;
    $page = 0;
    while ($hasNextPage) {
        $page++;

        $variables = [
            "userName" => $user,
            "page" => $page
        ];

        sleep(2); // Go easy on their server
        $response = graphql('https://graphql.anilist.co', $query, json_encode($variables), $accessToken);
        if ($response->errors) echo json_encode($response, JSON_PRETTY_PRINT);

        $hasNextPage = $response->data->Page->pageInfo->hasNextPage;
        $wL = $response->data->Page->mediaList;
        foreach($wL as $s) {
            // Ignore if the entry didn't released yet,
            // if it's not into planning,
            // or if it's hidden from your status lists
            if ($s->media->status == 'NOT_YET_RELEASED' ||
                $s->status != 'PLANNING' ||
                $s->hiddenFromStatusLists == true) continue;

            // Ignore if it's already in a season list
            foreach($s->customLists as $lName => $lStatus) {
                if (in_array($lName, $lists) && $lStatus == true) {
                    continue 2;
                }
            }

            // If it's not to be ignored... saved it
            $shows[] = $s->media;
        }
    }

    // Save everything
    file_put_contents(__DIR__ . '/unplanned.json', json_encode($shows));

    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>unplanned</title>

    <link rel="stylesheet" href="node_modules/spectre.css/dist/spectre.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <ol>
<?php
foreach($shows as $show) {
    // Get English title with fallback to romaji
    $title = $show->title->english ? $show->title->english : $show->title->romaji;
    // Get season and year
    $season = $show->season . ' ' . $show->seasonYear;
    // And print the line
    echo '<li><a href="https://anilist.co/anime/' . $show->id . '">' . $title . '</a> (' . $season  . ')</li>';
}
?>
        </ol>
    </div>
</body>
</html>
