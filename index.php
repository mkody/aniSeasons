<?php
    // This enables a JS smooth scroll to the set ID on load
    $curr = '2021-Summer';
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
        <section class="navbar-section">
            <a href="/aniSched">aniSched</a>
        </section>
    </header>
    <div class="container">
<?php
if (time() - filemtime(__DIR__ . '/shows.json') > 12 * 3600) {
    // refresh old shows.json after 12 hours
    require_once  __DIR__ . '/fetch.php';
    $data = $o;
} else {
    $data = json_decode(file_get_contents(__DIR__ . '/shows.json'));
}


function _duration($minutes) {
    if ($minutes < 60) return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    $h = floor($minutes / 60);
    $m = ($minutes % 60);
    return $h . ' hour' . ($h > 1 ? 's' : '') . ($m != 0 ? ' ' . $m . ' minute' . ($m > 1 ? 's' : '') : '');
}

function _showCounts($media) {
    if ($media->format == "MOVIE") {
        // Don't try to count a number of episodes
        if ($media->duration) return _duration($media->duration);
    } else if ($media->episodes == 1 && $media->duration) {
        // If there's only one episode, show only duration
        return _duration($media->duration);
    } else if ($media->episodes && $media->duration) {
        // Complete data: number of episodes and duration each
        return $media->episodes . 'x' . _duration($media->duration);
    } else if ($media->episodes) {
        // Sometimes we don't know the duration but we have the number of episodes
        return $media->episodes . ' episode' . ($media->episodes > 1 ? 's' : '');
    } else if ($media->duration) {
        // Sometimes we don't know how many episodes we get but we have the duration
        return '?x' . _duration($media->duration);
    }
    // If there's nothing matching yet, then we just leave the line empty
    return '<!-- TBD -->';
}

function _countMovies($shows) {
    // Go through our list and return the number of movies
    $i = 0;
    foreach($shows as $show) {
      if ($show->media->format == 'MOVIE') $i++;
    }
    return $i;
}
function _countCurrent($shows, $season) {
    // Go through our list and return the number of shows in season
    $i = 0;
    foreach ($shows as $show) {
      if ($show->media->format != 'MOVIE' && strtoupper($season) == (string)$show->media->seasonYear . ' ' . $show->media->season) $i++;
    }
    return $i;
}
function _countOldies($shows, $season) {
    // Go through our list and return the number of shows out of season
    $i = 0;
    foreach ($shows as $show) {
      if ($show->media->format != 'MOVIE' && strtoupper($season) != (string)$show->media->seasonYear . ' ' . $show->media->season) $i++;
    }
    return $i;
}

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
