<?php
require '../bootstrap.php'; 
use Util\ExcelExporter;
require_once '../vendor/autoload.php';

// Read POSTed JSON data
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

// Validate input
if (!is_array($payload) || empty($payload['data'])) {
    http_response_code(400);
    echo "Invalid or missing data.";
    error_log("SHOULDNT BE HERE");

    exit;
}

// Determine filename
$filename = !empty($payload['filename']) ? preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $payload['filename']) : 'report.xlsx';

// Generate Excel binary using your ExcelExporter class
// Assumes $payload['data'] is an array of associative arrays or arrays
$binary = ExcelExporter::getXlsxBinary(json_decode($payload['data'], true));
error_log("Generated Excel binary of length: " . strlen($binary));

$filename = "export.xlsx";

// Prevent any prior output
if (ob_get_length()) {
    ob_end_clean();
}

// Send proper headers
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Content-Length: ' . strlen($binary));
header('Cache-Control: max-age=0');

// Flush buffers and stream
echo $binary;
flush();
exit;

