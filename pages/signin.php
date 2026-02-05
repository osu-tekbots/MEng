<?php
/**
 * This page handles the login process for a user. We use OSU's CAS method of authentication.
 * It updates the Last Login time and ensures $_SESSION['userID'] is set.
 */
include_once '../bootstrap.php';
include_once PUBLIC_FILES . '/lib/auth-onid.php';

use DataAccess\UsersDao;
use Model\User; // Needed if we create a new user

// 1. Authenticate (Populates $_SESSION['auth'])
authenticate();

// 2. Initialize DAO
$usersDao = new UsersDao($dbConn, $logger);

// 3. Get ONID from the session structure we saw in the debugger
$onid = $_SESSION['auth']['id'];

// 4. Attempt to fetch the user from the database
$user = $usersDao->getUserByOnid($onid);

if ($user) {
    // --- EXISTING USER ---
    
    // Update Last Login
    $user->setLastLogin(new DateTime());
    $usersDao->updateUser($user);

    // CRITICAL: Set the session variable the rest of the app expects
    $_SESSION['userID'] = $user->getId();

} else {
    // --- NEW USER (First time logging in) ---
    // You might want to handle user creation here if they don't exist yet
    
    $newUser = new User();
    $newUser->setOnid($onid);
    $newUser->setFirstName($_SESSION['auth']['firstName']);
    $newUser->setLastName($_SESSION['auth']['lastName']);
    $newUser->setEmail($_SESSION['auth']['email']);
    $newUser->setLastLogin(new DateTime());
    
    // Determine how you generate IDs or UUIDs. 
    // If your DB auto-increments ID, you might need to insert first then fetch ID.
    // If you generate UUIDs in PHP:
    // $newUser->setUuid(\Ramsey\Uuid\Uuid::uuid4()->toString()); 
    
    if ($usersDao->addNewUser($newUser)) {
         // Re-fetch to get the auto-incremented ID
         $user = $usersDao->getUserByOnid($onid);
         $_SESSION['userID'] = $user->getId();
    } else {
        die("Error creating new user account.");
    }
}

// 5. Redirect
$redirect = $configManager->getBaseUrl();
echo "<script>window.location.replace('$redirect');</script>";
die();
?>