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
    $userTypeHtml .= '<h1 class="hero-title d-none d-lg-block">COE Graduate Document Review System</h1>';
    
	$userTypeHtml .= '<div class="row justify-content-sm-center"><div class="col-sm-10" style="text-align: left;"><p>The COE Graduate Document Review System is a tool for reviewing student work from various graduate programs across the College of Engineering. If requested, graduate students are asked to upload a copy of their final thesis, project document, WR545 document, or similar. These are then reviewed internally for the purpose of accreditation using internal rubrics.</p>';
    
	$userTypeHtml .= '<p><strong>Students</strong> all you need to do on this site is visit the <a href="profile">Profile page</a> in the upper right and confirm/update all fields. On the same page you will be able to upload your final document as requested.</p>
	
	<p><strong>Reviewers</strong>, you may access the documents you have been requested to review via the <a href="reviewerAssignments.php">Review menu</a> in the upper right (only visible to reviewers).</p></div></div>';
	
	$userType .= '<p>User Type: $userType </p>';

    $userType .= $_SESSION['userType'];
    
    $user = $usersDao->getUserByOnid($_SESSION['auth']['id']);

    $userIsAdmin = $usersDao->userIsAdmin($user->getId());
    $userIsStudent = $usersDao->userIsStudent($user->getId());
    $userIsReviewer = $usersDao->userIsReviewer($user->getId());

    /*
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
	
	*/
} else {
    $userTypeHtml .= '
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center p-4 p-md-8 bg-light border rounded-3 shadow-sm">
                        <h1 class="display-5 fw-bold mb-3">Welcome to the COE Graduate Document Review System</h1>
                        <p class="lead mb-3">
                            This site is an internal tool used by College of Engineering faculty
                            to review the work of graduate students.
                        </p>
                        <p class="mb-0">
                            <a href="signin">Please log in to access more features.</a>
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