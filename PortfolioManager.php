<?php

include 'simple_html_dom.php';

class PortfolioManager
{
    private $portfolioName;

    // Constructor to set the portfolio name
    public function __construct()
    {
        
    }

    // Function to return all portfolio companies
    public function getAllPortfolioCompanies()
    {
        $url = 'https://www.use.or.ug/content/market-snapshot'; 
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
        return $jsonResult;
    }

    // Function to return company details
    public function getCompanyDetails($companyName)
    {
        $url = "https://www.use.or.ug/listed/" . $companyName;

        $html = file_get_html($url);

        // Get Company Name (Title)
        $companyName = "$companyName";

        // Get Company Logo URL
        $logoURL = $html->find('div.views-field-field-company-logo img', 0)->src;

        // Get Company Information
        $isin = $html->find('div.views-field-field-isin div.field-content', 0)->innertext;
        $listingDate = $html->find('div.views-field-field-listing-date-1 span.date-display-single', 0)->innertext;
        $sharesIssued = $html->find('div.views-field-php div.field-content', 0)->innertext;
        $marketCap = $html->find('div.views-field-php-1 div.field-content', 0)->innertext;
        $address = $html->find('div.views-field-field-address div.field-content', 0)->innertext;
        $phone = $html->find('div.views-field-field-phone div.field-content', 0)->innertext;
        $email = $html->find('div.views-field-field-email div.field-content', 0)->innertext;
        $website = $html->find('div.views-field-field-website div.field-content', 0)->innertext;

        // Release the memory
        $html->clear();
        unset($html);

        // Create JSON array
        $data = [
            'companyName' => $companyName,
            'logoURL' => $logoURL,
            'isin' => $isin,
            'listingDate' => $listingDate,
            'sharesIssued' => $sharesIssued,
            'marketCap' => $marketCap,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'website' => $website,
        ];

        // Output the results as JSON
        return json_encode($data);
    }

    // Function to return portfolio company data
    public function getPortfolioCompanyData($companyName)
    {
        $url = 'https://www.use.or.ug/data.php?stock=' . $companyName . '&callback=?';

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
                'stock' => $companyName,
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
            return json_encode(['error' => 'Error decoding JSON data']);
        }
    }

    // Function to return exchange rate details
    public function getExchangeRateDetails()
    {
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
                return json_encode($result, JSON_PRETTY_PRINT);
            } else {
                return "Error: Couldn't find the target ul.\n";
            }

            // Clean up the DOM object
            $html->clear();
        } else {
            return "Error: Couldn't load the HTML.\n";
        }
    }
}

// Example usage:
$portfolioManager = new PortfolioManager();

// Get all portfolio companies
//$allCompanies = $portfolioManager->getAllPortfolioCompanies();
//print_r($allCompanies);

// Get details for a specific company
//$companyDetails = $portfolioManager->getCompanyDetails('BOBU');
// print_r($companyDetails);

// // Get data for a specific portfolio company
// $companyData = $portfolioManager->getPortfolioCompanyData('BOBU');
// print_r($companyData);

// // Get exchange rate details
 $exchangeRates = $portfolioManager->getExchangeRateDetails();
 print_r($exchangeRates);
?>
