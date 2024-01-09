<?php
// Include the simple_html_dom library
include('simple_html_dom.php');


// Record the start time
$startTime = microtime(true);

// URL to scrape
$url = 'https://africanfinancials.com/currency/ug-ugx/';

// Set the user agent
$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

// Create context options with the user agent
$options = [
    'http' => [
        'header' => 'User-Agent: ' . $userAgent,
    ],
];

// Create a stream context
$context = stream_context_create($options);

// Try to load the HTML with the specified context
$html = file_get_html($url, false, $context);

// Check if the HTML is loaded successfully
if ($html) {
    // Find the target ul by class name
    $overviewUl = $html->find('.mod-tearsheet-overview_quote_bar', 0);

    // Check if the ul is found
    if ($overviewUl) {
        // Get all values inside li with class mod-ui-data-list_value
        $allValues = [];
        $labels = ['VALUE/US$', "TODAY'S CHANGE", '1 YEAR CHANGE', '52 WEEK RANGE'];

        foreach ($overviewUl->find('li span.mod-ui-data-list_value') as $index => $valueElement) {
            $key = isset($labels[$index]) ? $labels[$index] : "Key $index";
            $allValues[$key] = $valueElement->plaintext;
        }

        // Find the div by class name
        $disclaimerDiv = $html->find('div.mod-disclaimer', 0);
        $disclaimerContent = $disclaimerDiv ? $disclaimerDiv->innertext : '';

        // Record the end time
        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        // Display the extracted values and disclaimer content as JSON
        $result = [
            'values' => $allValues,
            'disclaimer' => $disclaimerContent,
            'source' => 'https://africanfinancials.com/currency/ug-ugx/',
            'execution_time' => $executionTime,
            'time in seconds' => round($executionTime, 4) . " seconds",
        ];

        // Set the JSON header
        header('Content-Type: application/json');

        // Output the JSON response
        echo json_encode($result, JSON_PRETTY_PRINT);
    } else {
        echo "Error: Couldn't find the target ul.\n";
    }

    // Clean up the DOM object
    $html->clear();
} else {
    echo "Error: Couldn't load the HTML.\n";
}
?>
