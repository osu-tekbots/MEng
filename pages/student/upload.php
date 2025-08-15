<?php
include_once '../../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\UploadsDao;
use DataAccess\DocumentTypesDao;

$selectedDocumentType = isset($_GET['documentType']) && !empty($_GET['documentType']) ? $_GET['documentType'] : 1;

$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);
$documentTypesDao = new DocumentTypesDao($dbConn, $logger);

$js = array(
    array(
        'src' => 'assets/js/fileUpload.js',
        'defer' => 'true'
    )
);

include_once PUBLIC_FILES . '/modules/header.php';

$user = $usersDao->getUser($_SESSION['userID']);
$documentTypes = $documentTypesDao->getAllDocumentTypes();

$previousUpload = $uploadsDao->getUserUploadByType($_SESSION['userID'], $selectedDocumentType);

?>

<form id="formUploadDocument">

    <input type="hidden" name="userId" id="userId" value="<?php echo $user->getId(); ?>" />

    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <h3 id="upload">Upload</h3>
            </div>
        </div>
        <div class="form-group">
            <div class="mb-3">
                <div class="row">
                    <div class="col">
                        <label class="form-label" for="userUpload" id="userUploadLabel">
                            Choose file (PDF)
                        </label>
                    </div>
                    <div class="col-3">
                        <select class="form-select" id="documentType" onChange="onDocumentTypeChange()">
                            <?php 
                                foreach ($documentTypes as $documentType) {
                                    echo "<option value=" . $documentType->getId() . " ";
                                    if ($documentType->getId() == $selectedDocumentType) {
                                        echo "selected";
                                    }
                                    echo ">" . $documentType->getTypeName() . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                </br>
                <?php
                    if ($previousUpload) {
                        echo '<div class="row">';
                        echo '<input type="hidden" name="previousUploadId" id="previousUploadId" value="' . $previousUpload->getId() . '" />';
                        echo '<div class="col-2"><h4>Previous Upload: </h4></div>';
                        echo '<div class="col-2"><a id="aUploadDownload" class="btn btn-primary w-100">Download</a></div>';
                        echo '<div class="col-1"><a id="aUploadStatus" class="btn btn-success w-100">Status</a></div>';
                        echo '<div class="col-1"><a id="aUploadDelete" class="btn btn-danger w-100">Delete</a></div>';
                        echo '</div>';
                        echo '</br>';
                    }
                ?>
                <div class="row">
                    <div class="col">
                        <input name="userUpload" type="file" class="form-control" id="userUpload" accept=".pdf, application/pdf">
                    </div>
                </div>
            </div>
        </div>      

        <div class="row">
            <div class="col">
                <div class="btn-upload-submit">
                    <button type="submit" class="btn btn-primary" id="btnUploadSubmit">
                        <i class="fas fa-save"></i>&nbsp;&nbsp;Save Changes
                    </button>
                    <span class="loader" id="btnUploadLoader"></span>
                </div>
            </div>
        </div>
    </div>

</form>

<script>
    function onDocumentTypeChange() {
        const documentType = document.getElementById("documentType");
        window.location.replace("student/upload.php?documentType=" + documentType.value);
    }
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
