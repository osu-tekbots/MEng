<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\UploadsDao;

$selectedDocumentType = isset($_GET['documentType']) && !empty($_GET['documentType']) ? $_GET['documentType'] : 1;

$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);

$js = array(
    array(
        'src' => 'assets/js/fileUpload.js',
        'defer' => 'true'
    )
);

include_once PUBLIC_FILES . '/modules/header.php';

$user = $usersDao->getUser($_SESSION['userID']);
$documentTypes = $uploadsDao->getAllDocumentTypes();

$previousUpload = $uploadsDao->getUserUploadByFlag($_SESSION['userID'], $selectedDocumentType);

?>

<div class="container-fluid">
    <div class="container mt-5 mb-5">

        <div class="row mb-4">
            <div class="col">
                <h2>Student Upload</h2>
                <p class="text-muted">Manage your document submissions and view statuses.</p>
            </div>
        </div>

        <form id="formUploadDocument">
            <input type="hidden" name="userId" id="userId" value="<?php echo $user->getId(); ?>" />

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Document Submission</h5>
                </div>
                
                <div class="card-body">
                    
                    <div class="form-group row align-items-center">
                        <label for="documentType" class="col-sm-3 col-form-label text-muted small text-uppercase font-weight-bold">Document Type</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="documentType" onChange="onDocumentTypeChange()">
                                <?php 
                                    foreach ($documentTypes as $documentType) {
                                        echo "<option value=" . $documentType->getId() . " ";
                                        if ($documentType->getId() == $selectedDocumentType) {
                                            echo "selected";
                                        }
                                        echo ">" . $documentType->getFlagName() . "</option>";
                                    }
                                ?>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <?php if ($previousUpload): ?>
                        <div class="alert alert-secondary mb-4">
                            <input type="hidden" name="previousUploadId" id="previousUploadId" value="<?php echo $previousUpload->getId(); ?>" />
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <h6 class="mb-1"><i class="fas fa-file-pdf text-danger mr-2"></i> File Previously Uploaded</h6>
                                    <small class="text-muted">Manage your existing submission for this document type.</small>
                                </div>
                                <div class="col-md-6 text-right">
                                    <div class="btn-group" role="group">
                                        <a id="aUploadDownload" class="btn btn-sm btn-outline-primary">Download</a>
                                        <a id="aUploadStatus" class="btn btn-sm btn-outline-info">Status</a>
                                        <a id="aUploadDelete" class="btn btn-sm btn-outline-danger">Delete</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group row">
                        <label for="userUpload" class="col-sm-3 col-form-label text-muted small text-uppercase font-weight-bold" id="userUploadLabel">
                            New File (PDF)
                        </label>
                        <div class="col-sm-9">
                            <div class="custom-file">
                                <input name="userUpload" type="file" class="form-control pt-1" id="userUpload" accept=".pdf, application/pdf" style="height: auto;">
                                <small class="form-text text-muted">Please ensure your file is in PDF format.</small>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-white text-right">
                    <div class="btn-upload-submit d-inline-block">
                        <span class="loader mr-2" id="btnUploadLoader" style="display:none;"></span>
                        <button type="submit" class="btn btn-primary" id="btnUploadSubmit">
                            <i class="fas fa-save mr-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
    function onDocumentTypeChange() {
        const documentType = document.getElementById("documentType");
        window.location.replace("studentUpload.php?documentType=" + documentType.value);
    }
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>