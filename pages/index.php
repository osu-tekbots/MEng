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
    $userTypeHtml .= '<h1 class="hero-title d-none d-lg-block">MEng</h1>';
    $userType .= '<p>User Type: $userType </p>';

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
} else {
    $userTypeHtml .= '
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center p-4 p-md-5 bg-light border rounded-3 shadow-sm">
                        <h1 class="display-5 fw-bold mb-3">Welcome to the MEng Site</h1>
                        <p class="lead mb-3">
                            This site is an internal tool used by College of Engineering faculty
                            to review the work of graduate students.
                        </p>
                        <p class="mb-0">
                            Please log in to access more features.
                        </p>
                    </div>
                </div>
            </div>
        </div>';

}

$js = array(
    'assets/js/home.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
?>

<div class="hero-home" style="text-align: center;">
    
    <?php echo $userTypeHtml ?>
</div>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>