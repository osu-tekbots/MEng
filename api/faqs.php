<?php
/**
 * This is the endpoint for API requests on FAQs. The requests are handled inside the
 * `FaqActionHandler`, but the handler and required DAOs are initialized in this file.
 */
include_once '../bootstrap.php';

use Api\Response;
use Api\FaqActionHandler;

$handler = new FaqActionHandler($dbConn, $logger);

if ($isLoggedIn) {
    $handler->handleRequest();
} else {
    $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
}
?>
