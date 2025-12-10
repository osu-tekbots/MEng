<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\UploadsDao;

// 1. Setup DAOs
$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);

// 2. Handle Document Type Logic (from studentUpload.php)
$selectedDocumentType = isset($_GET['documentType']) && !empty($_GET['documentType']) ? $_GET['documentType'] : 1;

// 3. Include Upload JS
$js = array(
    array(
        'src' => 'assets/js/fileUpload.js',
        'defer' => 'true'
    )
);

include_once PUBLIC_FILES . '/modules/header.php';

// 4. Fetch User Data
$user = $usersDao->getUser($_SESSION['userID']);
$userFlags = $usersDao->getUserFlags($_SESSION['userID']); 

// 5. Fetch Upload Data
$documentTypes = $uploadsDao->getAllDocumentTypes();
$previousUpload = $uploadsDao->getUserUploadByFlag($_SESSION['userID'], $selectedDocumentType);

// 6. Process Flags/Roles
$roles = [];
$departments = [];

if ($userFlags) {
    foreach ($userFlags as $flag) {
        if ($flag->getFlagType() === 'Role') {
            $roles[] = $flag->getFlagName();
        } elseif ($flag->getFlagType() === 'Department') {
            $departments[] = $flag->getFlagName();
        }
    }
}
?>

<div class="container-fluid">
    <div class="container mt-5 mb-5">
        
        <div class="row mb-4">
            <div class="col">
                <h2>My Profile</h2>
                <p class="text-muted">Manage your personal information, document submissions, and view your system permissions.</p>
            </div>
        </div>

        <?php if (isset($message) && $message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="profile.php" method="POST">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="firstName">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" 
                                           value="<?php echo $user->getFirstName(); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" 
                                           value="<?php echo $user->getLastName(); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $user->getEmail(); ?>" required>
                            </div>
                            
                            <hr class="mt-4 mb-4">
                            
                            <button type="submit" name="updateProfile" class="btn btn-primary">
                                Save Changes
                            </button>
                        </form>
                    </div>
                </div>

                <form id="formUploadDocument">
                    <input type="hidden" name="userId" id="userId" value="<?php echo $user->getId(); ?>" />

                    <div class="card shadow-sm mb-4">
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
                                    <i class="fas fa-save mr-1"></i> Upload Document
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>

            <div class="col-lg-4">
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">System Identifiers</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="text-muted small text-uppercase font-weight-bold">ONID</label>
                            <input type="text" class="form-control-plaintext font-weight-bold" 
                                   value="<?php echo $user->getOnid(); ?>" readonly>
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-muted small text-uppercase font-weight-bold">OSU ID</label>
                            <input type="text" class="form-control-plaintext font-weight-bold" 
                                   value="<?php echo $user->getOsuId(); ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Permissions & Access</h5>
                    </div>
                    <div class="card-body">
                        
                        <h6 class="text-muted small text-uppercase font-weight-bold mb-2">Departments</h6>
                        <div class="mb-3">
                            <?php if (count($departments) > 0): ?>
                                <?php foreach ($departments as $dept): ?>
                                    <span class="badge badge-info p-2 mr-1"><?php echo $dept; ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted font-italic">No departments assigned.</span>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <h6 class="text-muted small text-uppercase font-weight-bold mb-2">User Roles</h6>
                        <div>
                            <?php if (count($roles) > 0): ?>
                                <?php foreach ($roles as $role): ?>
                                    <span class="badge badge-secondary p-2 mr-1"><?php echo $role; ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted font-italic">No specific roles assigned.</span>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    /**
     * Handles document type selection change.
     * Reloads profile.php with the selected type in the query string.
     */
    function onDocumentTypeChange() {
        const documentType = document.getElementById("documentType");
        // Updated redirect URL to profile.php
        window.location.replace("profile.php?documentType=" + documentType.value);
    }
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>