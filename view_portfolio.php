<?php
$stock = 'MTNU';
$url = 'https://www.use.or.ug/data.php?stock=' . $stock . '&callback=?';

// Fetch data from the URL
$jsonData = file_get_contents($url);

// Remove the callback function wrapper from the response (if present)
$jsonData = preg_replace('/^\?\(/', '', $jsonData);
$jsonData = preg_replace('/\);$/', '', $jsonData);

// Decode JSON data into a PHP array
$data = json_decode($jsonData, true);

// Check if decoding was successful
if ($data !== null) {
    // Iterate through the data and manipulate as needed
    foreach ($data as &$dataPoint) {
        // Convert timestamp to human-readable format
        $timestamp = $dataPoint[0];
        $date = (new DateTime('@' . ($timestamp / 1000)))->format('Y-m-d H:i:s');
        $dataPoint['date'] = $date;

        // Create associative keys for open, high, low, close, adjusted close, and volume
        $dataPoint['timestamp'] = $timestamp;
        $dataPoint['open'] = $dataPoint[1];
        $dataPoint['high'] = $dataPoint[2];
        $dataPoint['low'] = $dataPoint[3];
        $dataPoint['close'] = $dataPoint[4];
        $dataPoint['adjusted_close'] = $dataPoint[5];
        $dataPoint['volume'] = $dataPoint[6];

        // Unset the numeric keys if you no longer need them
        unset($dataPoint[0], $dataPoint[1], $dataPoint[2], $dataPoint[3], $dataPoint[4], $dataPoint[5], $dataPoint[6]);
    }

    // Create a new associative array with the manipulated data
    $result = [
        'stock' => $stock,
        'data' => $data,
        'latestSharePrice' => end($data)['close'],
    ];

    // Convert the result to JSON
    $resultJson = json_encode($result);

    // Set the appropriate HTTP headers
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *'); // Enable CORS if needed

    // Return the JSON data
    echo $resultJson;
} else {
    // Set appropriate HTTP headers for an error response
    header('Content-Type: application/json');
    header('HTTP/1.1 500 Internal Server Error');

    // Return an error JSON response
    echo json_encode(['error' => 'Error decoding JSON data']);
}
?>