<?php
/**
 * This page handles the login process for a user. We use OSU's CAS method of authentication.
 * It updates the Last Login time and ensures $_SESSION['userID'] is set.
 */
include_once '../bootstrap.php';
include_once PUBLIC_FILES . '/lib/auth-onid.php';

use DataAccess\UsersDao;
use Model\User; // Needed if we create a new user

//Authenticate (Populates $_SESSION['auth'] and the database with the user)
authenticate();

//Initialize DAO
$usersDao = new UsersDao($dbConn, $logger);
//Get ONID from the session structure we saw in the debugger
$onid = $_SESSION['auth']['id'];
//Output: {"method":"onid","id":"arorae","firstName":"Ekansh","lastName":"Arora","email":"arorae@oregonstate.edu"}
//Attempt to fetch the user from the database and check to make sure the user was properly updated or created
$user = $usersDao->getUserByOnid($onid);
if ($user) {
    // Update Last Login
    $user->setLastLogin(new DateTime());
    $usersDao->updateUser($user);

    // CRITICAL: Set the session variable the rest of the app expects
    $_SESSION['userID'] = $user->getId();

} else {
    $logger->error("Failed to load user '{$onid}' after authentication");
    die("Error: Could not find the authenticated user in the database. Please contact an administrator.");
}

//Redirect
$redirect = $configManager->getBaseUrl();
echo "<script>window.location.replace('$redirect');</script>";
die();
?>