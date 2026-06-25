<?php
use DataAccess\UsersDao;
use DataAccess\ShowcaseProfilesDao;
use Model\User;
use Model\ShowcaseProfile;
use Model\UserAuthProvider;

$baseUrl = $configManager->getBaseUrl();

/**
 * Uses ONID to authenticate the user. 
 * 
 * When the function returns, the user will have been authenticated and the SESSION variable will have been set
 * accordingly.
 *
 * @return void
 */
function authenticate() {
    global $isLoggedIn, $baseUrl, $dbConn, $logger;
    $logger->info("Login status: ".($isLoggedIn?'true':'false')."; userID: ".(isset($_SESSION['userID']) ? $_SESSION['userID'] : "N/A"));
    if (!$isLoggedIn) {
        $logger->info('Logging in...');
        include_once PUBLIC_FILES . '/lib/auth/onid.php';
        $onid = authenticateWithONID();
    
        try {
            $ok = setUserInformation($dbConn, $logger, $onid);
        } catch(\Exception $e) {
            $logger->error("setUserInformation() failed for ONID ".$onid.". Exception: ".$e);
            $ok = false;
        }

        if (!$ok) {
            $_SESSION['error'] = '
                We were unable to authenticate your sign-in request successfully. Please try again later or contact
                the Tekbots Webdev team if the problem persists.
            ';
            $redirect = $baseUrl . 'error';
            echo "<script>window.location.replace('$redirect');</script>";
            die();
        }
    }
}


/**
 * Creates a new user entry if needed, updates user information if they exist.
 * 
 * This function utilizes the `$_SESSION['auth']` variables set by authentication providers. Therefore it must be
 * called after successful authentication to work properly.
 *
 * @param \DataAccess\DatabaseConnect $dbConn
 * @param \Util\Logger $logger
 * @param string $onid the ID provided by ONID
 * @return bool true if an entry was created or one exists, false otherwise
 */
function setUserInformation($dbConn, $logger, $onid) {
    // First check if the user was created
    $usersDao = new UsersDao($dbConn, $logger);
    $user = $usersDao->getUserByOnid($onid);
    if (!$user) {
        $user = new User();
        $logger->info('Creating new user '.$user->getID());
        $user
            ->setOnid($onid)
            ->setFirstName($_SESSION['auth']['firstName'])
            ->setLastName($_SESSION['auth']['lastName'])
            ->setEmail($_SESSION['auth']['email']);
        $ok = $usersDao->addNewUser($user);
        if (!$ok) {
            $logger->error('Could not create new user');
            return false;
        }
    } else {
        //User exists but we're gonna update any fields that are given to their newest version, otherwise they stay the same
        if (!empty($_SESSION['auth']['firstName'])) $user->setFirstName($_SESSION['auth']['firstName']);
        if (!empty($_SESSION['auth']['lastName'])) $user->setLastName($_SESSION['auth']['lastName']);
        if (!empty($_SESSION['auth']['email'])) $user->setEmail($_SESSION['auth']['email']);
        if (!empty($_SESSION['auth']['uuid'])) $user->setUuid($_SESSION['auth']['uuid']);
        $usersDao->updateUser($user);
    }

    return true;
}
