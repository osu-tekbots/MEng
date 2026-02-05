<?php
/**
 * This file is password protected on the Apache Web Server. It allows for local development of an authenticated
 * test user without the need for CAS or other OAuth authentication services, since these services do not permit
 * the use of localhost URLs.
 * 
 * Essentially, we are masquerading as another user while we do development offline.
 */
include_once '../bootstrap.php';

use DataAccess\UsersDao;

if (!isset($_SESSION)) {
    session_start();
}

$dao = new UsersDao($dbConn, $logger);

$redirect = "<script>location.replace('../index.php')</script>";

$masqerading = isset($_SESSION['masq']);
if ($masqerading) {
    $user = $dao->getUser($_SESSION['userID']);
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'start':
        $onid = $_POST['onid'];
        if ($onid . '' != '') {
            $user = $dao->getUserByOnid($onid);
            if ($user) {
                $ok = true;
                if (isset($ok) && $ok) {
                    stopMasquerade();
                    startMasquerade($user, $dao);
                    echo $redirect;
                    die();
                }
            } else {
                $message = 'User with the provided ONID not found';
            }
        }
        break;
        
    case 'stop':
        stopMasquerade();
        echo $redirect;
        die();

    default:
        break;
}

/**
 * Stops the current masquerade (if there is one) and restores the original user session variables.
 *
 * @return void
 */
function stopMasquerade() {
    if (isset($_SESSION['masq'])) {
        unset($_SESSION['userID']);
        unset($_SESSION['userIsAdmin']);
        unset($_SESSION['userIsStudent']);
        unset($_SESSION['userIsReviewer']);
        unset($_SESSION['userType']);
        if (isset($_SESSION['masq']['savedPreviousUser'])) {
            $_SESSION['site'] = $_SESSION['masq']['site'];
            $_SESSION['userID'] = $_SESSION['masq']['userID'];
            $_SESSION['userIsAdmin'] = $_SESSION['masq']['userIsAdmin'];
            $_SESSION['userIsStudent'] = $_SESSION['masq']['userIsStudent'];
            $_SESSION['userIsReviewer'] = $_SESSION['masq']['userIsReviewer'];
            $_SESSION['userType'] = $_SESSION['masq']['userType'];
        }
        unset($_SESSION['masq']);
    }
}

/**
 * Starts to masquerade as the provided user
 *
 * @param \Model\User $user the user to masquerade as
 * @return void
 */
function startMasquerade($user, $dao) {
    $_SESSION['masq'] = array('active' => true);
    if (isset($_SESSION['userID'])) {
        $_SESSION['masq']['savedPreviousUser'] = true;
        $_SESSION['masq']['site'] = $_SESSION['site'];
        $_SESSION['masq']['userID'] = $_SESSION['userID'];
        $_SESSION['masq']['userIsAdmin'] = $_SESSION['userIsAdmin'];
        $_SESSION['masq']['userIsStudent'] = $_SESSION['userIsStudent'];
        $_SESSION['masq']['userIsReviewer'] = $_SESSION['userIsReviewer'];
        $_SESSION['masq']['userType'] = $_SESSION['userType'];
    }
    $_SESSION['site'] = 'MEng';
    $_SESSION['userID'] = $user->getId();

    $userIsAdmin = $dao->userIsAdmin($user->getId());
    $userIsStudent = $dao->userIsStudent($user->getId());
    $userIsReviewer = $dao->userIsReviewer($user->getId());

    $_SESSION['userIsAdmin'] = $userIsAdmin;
    $_SESSION['userIsStudent'] = $userIsStudent;
    $_SESSION['userIsReviewer'] = $userIsReviewer;

    if ($userIsAdmin) {
        $_SESSION['userType'] = 'Admin';
    } else if ($userIsReviewer) {
        $_SESSION['userType'] = 'Reviewer';
    } else if ($userIsStudent) {
        $_SESSION['userType'] = 'Student';
    } else {
        $_SESSION['userType'] = 'Public';
    }
    
    $_SESSION['auth'] = array(
        'method' => 'onid',
        'id' => strtolower($user->getOnid()),
        'firstName' => $user->getFirstName(),
        'lastName' => $user->getLastName()
    );
}
?>

<h1>OSU MEng: Masquerade as Another User</h1>

<?php if ($masqerading): ?>
    <p>Currently masqerading as <strong><?php echo $user->getFirstName() . ' ' . $user->getLastName(); ?></strong></p>
<?php endif; ?>

<?php if (isset($message)): ?>
    <p><?php echo $message ?></p>
<?php endif; ?>

<h3>Masquerade as Existing</h3>
<form method="post">
    <input type="hidden" name="action" value="start" />
    <label for="onid">ONID</label>
    <input required type="text" id="eonid" name="onid" autocomplete="off" />
    <button type="submit">Start Masquerading</button>
</form>

<h3>Stop Masquerading</h3>
<form method="post">
    <input type="hidden" name="action" value="stop" />
    <button type="submit">Stop</button>
</form>



