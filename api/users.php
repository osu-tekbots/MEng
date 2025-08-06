<?php
/**
 * This is the endpoint for API requests on users. The requests are handled inside the
 * `UserActionHandler`, but the handler, mailer, and required DAOs are initialized in this file.
 */
include_once '../bootstrap.php';

use Api\Response;
use DataAccess\UsersDao;
use Api\UserActionHandler;

$usersDao = new UsersDao($dbConn, $logger);
$handler = new UserActionHandler($usersDao, $logger);

if ($isLoggedIn) {
    $handler->handleRequest();
} else {
    $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
}
