<?php

// Include the Simple HTML DOM Parser library
include 'simple_html_dom.php';

// Function to scrape and parse the table from a given URL
function scrapeTableFromURL($url) {
    // Create DOM object
    $html = file_get_html($url);

    // Check if the HTML was successfully loaded
    if (!$html) {
        die('Unable to load HTML from the specified URL.');
    }

    // Find the table on the page (you need to inspect the HTML structure to determine the correct selector)
    $table = $html->find('table', 0);

    // Check if the table was found
    if (!$table) {
        die('No table found on the page.');
    }

    // Extract data from the table
    $tableData = [];
    
    foreach ($html->find('tr') as $row) {
        // Initialize an empty array to store row data
        $rowData = [];

        // Find all columns in the current row
        $columns = $row->find('td');

        // Loop through each column in the row
        foreach ($columns as $column) {
            // Check if the cell contains any text
            $cellText = trim($column->plaintext);
            
            if (!empty($cellText)) {
                // Add the cell text to the row data array
                $rowData[] = $cellText;
            }
        }

        // Only add the row to the tableData if it contains non-empty cells
        if (!empty($rowData)) {
            // Add the desired URL as the third element in the sub-array
            $rowData[] = 'https://www.use.or.ug/listed/' . $rowData[0];
            // Add the row data to the tableData array
            $tableData[] = $rowData;
        }
    }

    // Release the memory occupied by the DOM object
    $html->clear();
    unset($html);

    // Convert the tableData array to JSON
    $jsonResult = json_encode($tableData, JSON_PRETTY_PRINT);

    // Output the JSON result
    header('Content-Type: application/json');
    echo $jsonResult;

}

// Example usage
$url = 'https://www.use.or.ug/content/market-snapshot';  // Replace with the actual URL
$tableData = scrapeTableFromURL($url);

?>
