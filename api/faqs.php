<?php
/**
 * This is the endpoint for API requests on FAQs. The requests are handled inside the
 * `FaqActionHandler`, but the handler and required DAOs are initialized in this file.
 * 
 * Note: getFaqsByCategory is publicly accessible (no login required) since FAQs
 * are displayed to all users. All other actions require authentication.
 */
include_once '../bootstrap.php';

use Api\Response;
use Api\FaqActionHandler;

$handler = new FaqActionHandler($dbConn, $logger);

// Allow public access for reading FAQs; require login for create/update/delete
$requestBody = json_decode(file_get_contents('php://input'), true);
$action = isset($requestBody['action']) ? $requestBody['action'] : '';

if ($action === 'getFaqsByCategory') {
    $handler->handleRequest();
} elseif ($isLoggedIn) {
    $handler->handleRequest();
} else {
    $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
}
?>
