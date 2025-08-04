<?php
include_once '../../bootstrap.php';

use DataAccess\UsersDao;
$usersDao = new UsersDao($dbConn, $logger);

$js = array(
    array(
        'src' => '../assets/js/fileUpload.js',
        'defer' => 'true'
    )
);

include_once PUBLIC_FILES . '/modules/header.php';

$user = $usersDao->getUser($_SESSION['userID']);

// To-do: get document type from database (make a document type object)
$documentType = 1;

?>

<form id="formUploadDocument">

    <input type="hidden" name="userId" id="userId" value="<?php echo $user->getId(); ?>" />
    <input type="hidden" name="documentType" id="documentType" value="<?php echo $documentType; ?>" />

    <div class="btn-upload-submit">
        <button type="submit" class="btn btn-primary" id="btnUploadSubmit">
            <i class="fas fa-save"></i>&nbsp;&nbsp;Save Changes
        </button>
        <span class="loader" id="btnUploadLoader"></span>
    </div>

    <h3 id="upload">Upload</h3>
    <div class="form-group">
        <div class="mb-3">
            <label class="form-label" for="userUpload" id="userUploadLabel">
                Choose file (PDF)
            </label>
            <input name="userUpload" type="file" class="form-control" id="userUpload" accept=".pdf, application/pdf">
            
        </div>
    </div>
    <br />
</form>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
