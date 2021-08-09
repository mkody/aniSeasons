<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/funcs.php';

// This enables a JS smooth scroll to the set ID on load
// @TODO Make this automatic
$curr = '2021-Summer';

if (time() - filemtime(__DIR__ . '/shows.json') > 12 * 3600) {
    // refresh old shows.json after 12 hours
    require_once  __DIR__ . '/fetch.php';
    $data = $o;
} else {
    $data = json_decode(file_get_contents(__DIR__ . '/shows.json'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>aniSeasons</title>

    <link rel="stylesheet" href="node_modules/spectre.css/dist/spectre.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="navbar">
        <section class="navbar-section">
            <!-- Left for alignment -->
        </section>
        <section class="navbar-center">
            <h1>aniSeasons</h1>
        </section>
        <section class="navbar-section custom-links">
<?php foreach($customLinks as $title => $href) { ?>
            <span><a href="<?= $href ?>"><?= $title ?></a></span>
<?php } ?>
        </section>
    </header>
    <div class="container">
<?php
// Loop on our seasons
foreach ($data as $season => $shows) {
    $movies = _countMovies($shows);
    $current = _countCurrent($shows, $season);
    $oldies = _countOldies($shows, $season);
    // Don't show empty lists
    if ($shows && count($shows) > 0) {
        // Sort entries by title
        usort($shows, fn($a, $b) => strcmp(
            $a->media->title->english ? $a->media->title->english : $a->media->title->romaji,
            $b->media->title->english ? $b->media->title->english : $b->media->title->romaji
        ));

?>
        <h3 id="<?= str_replace(' ', '-', $season) ?>"><?= $season ?> <small>(<?= count($shows) ?> entries)</small></h3>
<?php
        // We check if there are previous shows
        if ($current > 0) {
?>
        <h4>&gt; <?= $current ?> current season</h4>
        <div class="show-list">
<?php
            // Loop on our non-movie entries relasing this season
            foreach ($shows as $show) {
                if ($show->media->format == 'MOVIE') continue;
                if (strtoupper($season) != (string)$show->media->seasonYear . ' ' . $show->media->season) continue
?>
            <a target="_blank" rel="noreferrer noopener" href="https://anilist.co/anime/<?= $show->media->id ?>"
               title="<?= str_replace('"', '\'', $show->media->title->romaji ? $show->media->title->romaji : $show->media->title->english) ?>">
                <div class="show-card status-<?= $show->status ?>">
                    <div class="show-cover" style="background-color: <?= $show->media->coverImage->color ?>; background-image: url(<?= $show->media->coverImage->large ?>);">&nbsp;</div>
                    <div class="show-details">
                        <?= $show->media->title->english ? $show->media->title->english : $show->media->title->romaji ?><br/>
                        <small><?= _showCounts($show->media) ?></small>
                    </div>
                </div>
            </a>
<?php
            }
?>
        </div>
<?php
        }

        // We check if there are previous shows
        if ($oldies > 0) {
?>
        <h4>&gt; <?= $oldies ?> previous seasons or ongoing</h4>
        <div class="show-list">
<?php
            // Loop on our non-movie entries out of season
            foreach ($shows as $show) {
                if ($show->media->format == 'MOVIE') continue;
                if (strtoupper($season) == (string)$show->media->seasonYear . ' ' . $show->media->season) continue
?>
            <a target="_blank" rel="noreferrer noopener" href="https://anilist.co/anime/<?= $show->media->id ?>"
               title="<?= str_replace('"', '\'', $show->media->title->romaji ? $show->media->title->romaji : $show->media->title->english) ?>">
                <div class="show-card status-<?= $show->status ?>">
                    <div class="show-cover" style="background-color: <?= $show->media->coverImage->color ?>; background-image: url(<?= $show->media->coverImage->large ?>);">&nbsp;</div>
                    <div class="show-details">
                        <?= $show->media->title->english ? $show->media->title->english : $show->media->title->romaji ?><br/>
                        <small><?= _showCounts($show->media) ?></small>
                    </div>
                </div>
            </a>
<?php
            }
?>
        </div>
<?php
        }

        // We check if there are any movie in that list before
        if ($movies > 0) {
?>
        <h4>&gt; <?= $movies ?> Movies</h4>
        <div class="show-list">
<?php
            // Loop on our movie entries
            foreach ($shows as $show) {
                if ($show->media->format != 'MOVIE') continue;
?>
            <a target="_blank" rel="noreferrer noopener" href="https://anilist.co/anime/<?= $show->media->id ?>"
               title="<?= str_replace('"', '\'', $show->media->title->romaji ? $show->media->title->romaji : $show->media->title->english) ?>">
                <div class="show-card status-<?= $show->status ?>">
                    <div class="show-cover" style="background-color: <?= $show->media->coverImage->color ?>; background-image: url(<?= $show->media->coverImage->large ?>);">&nbsp;</div>
                    <div class="show-details">
                        <?= $show->media->title->english ? $show->media->title->english : $show->media->title->romaji ?><br/>
                        <small><?= _showCounts($show->media) ?></small>
                    </div>
                </div>
            </a>
<?php
            }
?>
        </div>
<?php
        }
    }
}
?>
    </div>
    <script>
      const oT = document.getElementById('<?= $curr ?>').offsetTop
      scroll({
        top: oT,
        behavior: 'smooth'
      })
    </script>
</body>
</html>
