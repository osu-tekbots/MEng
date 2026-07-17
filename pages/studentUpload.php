<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\UploadsDao;

$selectedDocumentType = isset($_GET['documentType']) && !empty($_GET['documentType']) ? $_GET['documentType'] : 1;

$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);



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
                                        echo ">" . $documentType->getName() . "</option>";
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

    /**
     * Handles the Document Upload form submission (new upload or update).
     */
    function onUploadDocumentFormSubmit(event) {
        if (event) event.preventDefault();

        let form = new FormData(document.getElementById('formUploadDocument'));
        let bodyDocumentUpload = new FormData();

        let previousUpload = false;
        let newUpload = false;

        for (const [key, value] of form.entries()) {
            if (key == 'userUpload' && value.size > 0) {
                bodyDocumentUpload.append(key, value);
                newUpload = true;
            } else if (key == 'previousUploadId') {
                bodyDocumentUpload.append(key, value);
                previousUpload = true;
            } else {
                bodyDocumentUpload.append(key, value);
            }
        }

        const documentType = document.getElementById("documentType");
        const userId = document.getElementById("userId");
        let acceptedTypes = document.getElementById('userUpload').getAttribute('accept').trim().split(",");

        bodyDocumentUpload.append('userId', userId.value);
        bodyDocumentUpload.append('documentType', documentType.value);
        bodyDocumentUpload.append('acceptedTypes', acceptedTypes);

        if (previousUpload) {
            bodyDocumentUpload.append('action', 'updateDocument');
            api.post('/uploads.php', bodyDocumentUpload, true)
                .then(res => {
                    snackbar('Successfully updated', 'success');
                    $('#btnUploadLoader').hide();
                    setTimeout(function () { location.reload(); }, 1000);
                })
                .catch(err => {
                    snackbar(err.message, 'error');
                    $('#btnUploadLoader').hide();
                });
        } else if (newUpload) {
            bodyDocumentUpload.append('action', 'uploadDocument');
            api.post('/uploads.php', bodyDocumentUpload, true)
                .then(res => {
                    snackbar('Successfully uploaded', 'success');
                    $('#btnUploadLoader').hide();
                    setTimeout(function () { location.reload(); }, 1000);
                })
                .catch(err => {
                    snackbar(err.message, 'error');
                    $('#btnUploadLoader').hide();
                });
        }

        $('#btnUploadSubmit').attr('disabled', true);
        $('#btnUploadLoader').show();
        return false;
    }
    $('#formUploadDocument').on('submit', onUploadDocumentFormSubmit);

    /**
     * Handles deleting an uploaded document.
     */
    function onUploadDelete(event) {
        if (event) event.preventDefault();

        let bodyDocumentUpload = new FormData();
        const documentType = document.getElementById("documentType");
        const userId = document.getElementById("userId");
        const previousUploadId = document.getElementById("previousUploadId");

        bodyDocumentUpload.append('userId', userId.value);
        bodyDocumentUpload.append('documentType', documentType.value);
        bodyDocumentUpload.append('previousUploadId', previousUploadId.value);

        bodyDocumentUpload.append('action', 'deleteDocument');
        api.post('/uploads.php', bodyDocumentUpload, true)
            .then(res => {
                snackbar('Successfully deleted', 'success');
                $('#btnUploadLoader').hide();
                setTimeout(function () { location.reload(); }, 1000);
            })
            .catch(err => {
                snackbar(err.message, 'error');
                $('#btnUploadLoader').hide();
            });
    }
    $('#aUploadDelete').on('click', onUploadDelete);

    /**
     * Handles downloading an uploaded document.
     */
    function onUploadDownload(event) {
        if (event) event.preventDefault();

        let bodyDocumentUpload = new FormData();
        const uploadId = document.getElementById("previousUploadId");

        bodyDocumentUpload.append('uploadId', uploadId.value);
        bodyDocumentUpload.append('action', 'downloadDocument');

        api.post('/uploads.php', bodyDocumentUpload, true)
            .then(data => {
                let payload;
                try {
                    payload = typeof data.message === 'string' ? JSON.parse(data.message) : data;
                } catch (e) {
                    payload = data;
                }

                if (!payload.fileData) {
                    alert("Server returned no file data");
                    return;
                }

                const cleanBase64 = payload.fileData.replace(/\s/g, '');
                const binaryString = window.atob(cleanBase64);
                const len = binaryString.length;
                const bytes = new Uint8Array(len);
                for (let i = 0; i < len; i++) {
                    bytes[i] = binaryString.charCodeAt(i);
                }

                const blob = new Blob([bytes], { type: 'application/octet-stream' });
                const url = window.URL.createObjectURL(blob);

                const a = document.createElement('a');
                a.href = url;
                a.download = payload.filename || "error.txt";
                a.style.display = 'block';
                a.style.position = 'absolute';
                a.style.left = '-9999px';
                document.body.appendChild(a);

                const clickEvent = new MouseEvent('click', {
                    view: window,
                    bubbles: true,
                    cancelable: true
                });
                a.dispatchEvent(clickEvent);

                setTimeout(() => {
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }, 5000);
            })
            .catch(err => {
                console.error("Download Critical Failure:", err);
                alert("Download failed. Check console for details.");
            });
    }
    $('#aUploadDownload').on('click', onUploadDownload);
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>