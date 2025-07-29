<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
$usersDao = new UsersDao($dbConn, $logger);

include_once PUBLIC_FILES . '/modules/header.php';

// $pHasUpload = $profile->isResumeUploaded();
// $pUploadButtonsStyle = $pHasUpload ? '' : "style='display: none;'";
// $pUploadHtml = $pHasUpload ? "
//     <p id='uploadText'>You have uploaded a file.</p>
// " : "
//     <p id='uploadText'>No file has been uploaded</p>
// ";

// $user = $usersDao->getUser($_SESSION['userID']);
// $test = $usersDao->userIsStudent($user->getUuid());
// echo $test;

?>

<form>
    <h3 id="upload">Upload</h3>
    <div class="form-group">
        <?php echo $pResumeHtml; ?>
        <div id="resumeActions" <?php echo $pResumeButtonsStyle; ?>>
            <a href="<?php echo $pResumeLink; ?>" id="aResumeDownload" class="btn btn-primary btn-sm">
                Download
            </a>
            <button type="button" id="btnResumeDelete" class="btn btn-danger btn-sm">
                Delete Resume
            </button>
        </div>
        <div class="mb-3">
            <label class="form-label" for="profileResume" id="profileResumeLabel">
                Choose file (PDF)
            </label>
            <input name="profileResume" type="file" class="form-control" id="profileResume" accept=".pdf, application/pdf">
            
        </div>
    </div>
    <br />
</form>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
