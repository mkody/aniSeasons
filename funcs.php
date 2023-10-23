<?php
function graphql($host, $query, $variables) {
    // Cleanup
    $query = preg_replace('/\s+/S', ' ', $query);

    $curl = curl_init();
    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => $host,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{"query": "' . trim($query) . '", "variables": ' . $variables . '}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json; charset=utf-8'
            )
        )
    );

    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response);
}

function _signImage($url) {
    global $imgPrefixBase, $imgPrefixSecret;
    $transform = '150x210/filters:format(jpeg)/';

    if ($imgPrefixSecret) {
        $hash = hash_hmac('sha1', $transform . $url, $imgPrefixSecret, true);
        $bHash = str_replace('+', '-', str_replace('/', '_', base64_encode($hash)));

        return $imgPrefixBase . $bHash . '/' . $transform . $url;
    } else if ($imgPrefixBase) {
        return $imgPrefixBase . 'unsafe/' . $transform . $url;
    } else {
        return $url;
    }
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

function _showCard($show) {
?>
            <a target="_blank" rel="noreferrer noopener" href="https://anilist.co/anime/<?= $show->media->id ?>"
               title="<?= str_replace('"', '\'', $show->media->title->romaji ? $show->media->title->romaji : $show->media->title->english) ?> (<?= $show->status  ?>)">
                <div class="show-card status-<?= $show->status ?>">
                    <div class="show-cover" style="background-color: <?= $show->media->coverImage->color ?>; background-image: url('<?= $show->media->coverImage->large ?>');">&nbsp;</div>
                    <div class="show-details">
                        <?= $show->media->title->english ? $show->media->title->english : $show->media->title->romaji ?><br/>
                        <small><?= _showCounts($show->media) ?></small>
                    </div>
                </div>
            </a>
<?php
}
