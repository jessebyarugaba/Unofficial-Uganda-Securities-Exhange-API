<?php
header('Content-Type: application/json');

include('simple_html_dom.php');

// Load HTML content
$stock = "MTNU";
$url = "https://www.use.or.ug/listed/" . $stock;

$html = file_get_html($url);

// Get Company Name (Title)
$companyName = "$stock";

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
echo json_encode($data);
?>
