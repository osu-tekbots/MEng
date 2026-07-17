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
 * Calls setUserInformation to populate database with information
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
            $ok = setUserInformationInDatabase($dbConn, $logger, $onid);
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
function setUserInformationInDatabase($dbConn, $logger, $onid) {
    // First check if the user was created
    $usersDao = new UsersDao($dbConn, $logger);
    $user = $usersDao->getUserByOnid($onid);

    // Extract auth session fields once to avoid repetition
    $firstName = $_SESSION['auth']['firstName'] ?? null;
    $lastName  = $_SESSION['auth']['lastName'] ?? null;
    $email     = $_SESSION['auth']['email'] ?? null;
    $uuid      = $_SESSION['auth']['uuid'] ?? null;

    //Completely new user just logged in for the first time
    if (!$user) {
        $user = new User();
        $logger->info('Creating new user '.$user->getID());
        $user
            ->setOnid($onid)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setUuid($uuid);
            
        $ok = $usersDao->addNewUser($user) && $usersDao->addUserFlag($user->getId(), 2);
        //Default user is set to student (flag 2)

        if (!$ok) {
            $logger->error('Could not create new user');
            return false;
        }
        return true;
    }

    //Check if the user we already have in the database is refering to someone whos ONID got reused (needs to be deactivated)
    if (!empty($uuid) && !empty($user->getUuid()) && $user->getUuid() !== $uuid) {
        $logger->warn('SECURITY: Possible New ONID User detected. ONID: '.$onid
            .' | Old UUID: '.$user->getUuid().' | New UUID: '.$uuid);

        // Deactivate the compromised user and create a new account for the new ONID owner
        $usersDao->deactivateUser($user);

        $newUser = new User();
        $newUser
            ->setOnid($onid)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setUuid($uuid);

        $ok = $usersDao->addNewUser($user) && $usersDao->addUserFlag($user->getId(), 2);
        //Default user is set to student (flag 2)

        if (!$ok) {
            $logger->error('Could not create replacement user after ONID compromise');
            return false;
        }
        return true;
    }

    //User exists but we're gonna update any fields that are given to their newest version, otherwise they stay the same
    //UUID gets updated here only if the user's is empty
    if (!empty($uuid)) $user->setUuid($uuid);
    if (!empty($firstName)) $user->setFirstName($firstName);
    if (!empty($lastName)) $user->setLastName($lastName);
    if (!empty($email)) $user->setEmail($email);

    $usersDao->updateUser($user);
    return true;
}
