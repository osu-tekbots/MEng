<?php
/**
 * Home page for the MEng site
 */
include_once '../bootstrap.php';

$css = array(
    
);

use DataAccess\UsersDao;
$usersDao = new UsersDao($dbConn, $logger);

$userTypeHtml = '';
$userType = '';

if ($isLoggedIn) {

    $userType .= $_SESSION['userType'];
    $user = $usersDao->getUserByOnid($_SESSION['auth']['id']);

    $userIsAdmin = $usersDao->userIsAdmin($user->getUuid());
    $userIsStudent = $usersDao->userIsStudent($user->getUuid());
    $userIsReviewer = $usersDao->userIsReviewer($user->getUuid());

    $userTypeHtml .= '<select class="form-select" id="changeUserType" onChange="onChangeUserType()">';
    if ($userIsAdmin) {
        $userTypeHtml .= '<option value="Admin"';
        if ($_SESSION['userType'] == 'Admin') {
            $userTypeHtml .= ' selected';
        }
        $userTypeHtml .= '>Admin</option>';
    }
    if ($userIsReviewer) {
        $userTypeHtml .= '<option value="Reviewer"';
        if ($_SESSION['userType'] == 'Reviewer') {
            $userTypeHtml .= ' selected';
        }
        $userTypeHtml .= '>Reviewer</option>';
    }
    if ($userIsStudent) {
        $userTypeHtml .= '<option value="Student"';
        if ($_SESSION['userType'] == 'Student') {
            $userTypeHtml .= ' selected';
        }
        $userTypeHtml .= '>Student</option>';
    } 
    $userTypeHtml .= '<option value="Public"';
    if ($_SESSION['userType'] == 'Public') {
        $userTypeHtml .= ' selected';
    }
    $userTypeHtml .= '>Public</option>';
    $userTypeHtml .= '</select>';
}

$js = array(
    'assets/js/home.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
?>

<div class="hero-home" style="text-align: center;">
    <h1 class="hero-title d-none d-lg-block">MEng</h1>
    <p>User Type: <?php echo $userType ?> </p>
    <?php echo $userTypeHtml ?>
</div>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>