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

