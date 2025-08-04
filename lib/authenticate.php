<?php

use DataAccess\UsersDao;
$usersDao = new UsersDao($dbConn, $logger);

if (!session_id()) session_start();

$user = NULL;

// Get user & set $_SESSION user variables for this site
if(isset($_SESSION['site']) && $_SESSION['site'] == 'MEng') {
    // $_SESSION["site"] is this one! User info should be correct
    $logger->trace('Moved to another page while logged into this site');
} else {
    if(isset($_SESSION['auth']['method'])) {
        switch($_SESSION['auth']['method']) {
            case 'onid':
                // Logged in with ONID on another site; storing this site's user info in $_SESSION...
                
                $logger->trace('Updating $_SESSION for this site using ONID: '.$_SESSION['auth']['id'].' (came from '.($_SESSION['site'] ?? 'no site').')');
                $user = $usersDao->getUserByOnid($_SESSION['auth']['id']);
                $userIsAdmin = $usersDao->userIsAdmin($user->getUuid());
                $userIsStudent = $usersDao->userIsStudent($user->getUuid());
                
                $_SESSION['site'] = 'MEng';
                $_SESSION['userID'] = $user->getId();
                $_SESSION['userIsAdmin'] = $userIsAdmin;
                $_SESSION['userIsStudent'] = $userIsStudent;
                
                break;
            
            default:
                // Logged in with something not valid for this site; setting as not logged in
                $logger->trace('Authentication provider is '.$_SESSION['auth']['method'].', not something this site recognizes');

                $_SESSION['site'] = NULL;
                unset($_SESSION['userID']);
                unset($_SESSION['userIsAdmin']);
                unset($_SESSION['userIsStudent']);
        }
    } else {
        // Not logged in; still clear just to avoid the possibility of issues?
        $logger->trace('Switched from another site, but not logged in');
        $_SESSION['site'] = NULL;
        unset($_SESSION['userID']);
        unset($_SESSION['userIsAdmin']);
        unset($_SESSION['userIsStudent']);
    }
}

/**
 * Checks if the person who initiated the current request has one of the given access levels
 * 
 * @param string|string[] $allowedAccessLevels  The access level(s) that should be accepted. Options are:
 *      * "public"
 *      * "user"
 * 
 * @return bool Whether the person who initiated the current request has one of the given access levels
 */
function verifyPermissions($allowedAccessLevels, $userId, $isAdmin, $logger) {
    try {
        $isLoggedIn = isset($_SESSION['userID']) && !empty($_SESSION['userID']);
        $isExpectedUser = false;
        if ($userId != '') {
            if ($_SESSION['userID'] == $userId) {
                $isExpectedUser = true;
            }
        }

        $allowPublic        = (gettype($allowedAccessLevels)=='string') ? $allowedAccessLevels=='public'        : in_array('public',        $allowedAccessLevels);
        $allowUsers         = (gettype($allowedAccessLevels)=='string') ? $allowedAccessLevels=='user'          : in_array('user',          $allowedAccessLevels);
        $allowExpectedUsers = (gettype($allowedAccessLevels)=='string') ? $allowedAccessLevels=='expectedUser'  : in_array('expectedUser',  $allowedAccessLevels);
        $allowAdmins        = (gettype($allowedAccessLevels)=='string') ? $allowedAccessLevels=='admin'         : in_array('admin',         $allowedAccessLevels);

        if($allowPublic) {
            return true;
        }
        if($allowUsers && $isLoggedIn) {
            return true;
        }
        if($allowExpectedUsers && ($isExpectedUser || $isAdmin)) {
            return true;
        }
        if($allowAdmins && $isAdmin) {
            return true;
        }
    } catch(\Exception $e) {
        $logger->error('Failure while verifying user permissions: '.$e->getMessage());
    } 
    
    return false;
}