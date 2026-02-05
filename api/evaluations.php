<?php
include_once '../bootstrap.php';

use Api\Response;
use DataAccess\EvaluationsDao;
use DataAccess\UploadsDao;
use DataAccess\UsersDao;
use DataAccess\RubricsDao;
use Api\EvaluationActionHandler;

// Initialize DAOs
$evaluationsDao = new EvaluationsDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);
$rubricsDao = new RubricsDao($dbConn, $logger);
$usersDao = new UsersDao($dbConn, $logger);

// Initialize Handler
$handler = new EvaluationActionHandler($evaluationsDao, $rubricsDao, $uploadsDao, $usersDao, $logger);

// Authenticate
if ($isLoggedIn) {
    $handler->handleRequest();
} else {
    $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
}
?>