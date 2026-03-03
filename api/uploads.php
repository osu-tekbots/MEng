<?php
/**
 * This is the endpoint for API requests on uploads. The requests are handled inside the
 * `UploadActionHandler`, but the handler, mailer, and required DAOs are initialized in this file.
 */
include_once '../bootstrap.php';

use Api\Response;
use DataAccess\UsersDao;
use DataAccess\UploadsDao;
use Api\UploadActionHandler;

$handler = new UploadActionHandler($dbConn, $configManager, $logger);

if ($isLoggedIn) {
    $handler->handleRequest();
} else {
    $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
}
