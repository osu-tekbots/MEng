<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\UploadsDao;
use DataAccess\EvaluationsDao; // [1] Add Namespace

// 1. Setup DAOs
$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);
$evaluationsDao = new EvaluationsDao($dbConn, $logger); // [2] Initialize DAO

// 2. Document Type (hardcoded to thesis)
$selectedDocumentType = 1;

$uploadAcceptedTypesString = "PDF, DOCX, ZIP";
//Using mime_content_type("Filename") types list; more robust than extension checking
$uploadAcceptedTypes = ["application/pdf", 
                        "application/vnd.openxmlformats-officedocument.wordprocessingml.document", 
                        "application/zip", "application/x-zip-compressed"];



include_once PUBLIC_FILES . '/modules/header.php';

// 4. Fetch User Data
$userSelect = isset($_GET['userId']) && !empty($_GET['userId']) ? $_GET['userId'] : false;
if (!$userSelect) {
    $user = $usersDao->getUser($_SESSION['userID']);
    $userFlags = $usersDao->getUserFlags($_SESSION['userID']); 
    $hasPermissions = true;
} else {
    $user = $usersDao->getUser($userSelect);
    $userFlags = $usersDao->getUserFlags($userSelect); 
    if (($_SESSION['userID'] == $userSelect) || ($_SESSION['userIsAdmin'])) {
        $hasPermissions = true;
    } else {
        $hasPermissions = false;
    }
}

if (!$hasPermissions || !$user) {
    echo '<div class="container-fluid">
            <div class="container mt-5 mb-5">
                
                <div class="row mb-4">
                    <div class="col">
                        <h2>My Profile</h2>
                        <p class="text-muted">Manage your personal information, document submissions, and view your system permissions.</p>
                    </div>
                </div>
            </div>
        </div>';
    die();
}

// 5. Fetch Upload Data (FILTERED)


/*
// A. Gather the User's Program IDs
$userDeptIds = [];
if ($userFlags) {
    foreach ($userFlags as $flag) {
        if ($flag->getType() === 'Program') {
            $userDeptIds[] = $flag->getId();
        }
    }
}
*/

$documentTypes = $uploadsDao->getAllDocumentTypes();

// C. Fetch Previous Uploads
// We use $user->getId() (the profile being viewed) instead of $_SESSION['userID']
$previousUpload = $uploadsDao->getUserUploadByFlag($user->getId(), $selectedDocumentType);

// [3] Check for existing evaluations associated with this upload
$uploadLocked = false;
if ($previousUpload) {
    // Get all evaluations for this student
    $studentEvaluations = $evaluationsDao->getEvaluationsByStudentUserId($user->getId());
    
    // Check if any evaluation links to the current upload ID
    if ($studentEvaluations) {
        foreach ($studentEvaluations as $eval) {
            if ($eval->getFkUploadId() == $previousUpload->getId()) {
                $uploadLocked = true;
                break;
            }
        }
    }
}


// 6. Process Flags/Roles
$allPrograms = $usersDao->getAllDepartmentFlags();

// Robustly build the array of IDs checking for validity
$userFlagIds = [];
if ($userFlags && is_array($userFlags)) {
    foreach ($userFlags as $flag) {
        // Cast to string to ensure strictly safe comparison later
        $userFlagIds[] = (string)$flag->getId();
    }
}
?>

<div class="container-fluid">
    <div class="container mt-5 mb-5">
        
        <div class="row mb-4">
            <div class="col">
                <h2>My Profile</h2>
                <p class="text-muted">Manage your personal information and document submissions.</p>
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
                        <form id="formEditProfile" onsubmit="return onEditProfileFormSubmit(event)">
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
                            
                            <h6 class="text-muted small text-uppercase font-weight-bold mb-2 mt-2">Program</h6>
                            <div class="mb-3">
                                <?php 
                                    // Logic to determine the currently selected program (if any)
                                    // We check if the user has a flag that matches a program ID
                                    $currentDeptId = '';
                                    if ($userFlagIds) {
                                        foreach ($allPrograms as $prog) {
                                            if (in_array($prog->getId(), $userFlagIds)) {
                                                $currentDeptId = $prog->getId();
                                                break; // Enforce single selection by taking the first match
                                            }
                                        }
                                    }
                                ?>

                                <select class="form-control" 
                                        id="programSelect" 
                                        data-user-id="<?php echo $user->getId(); ?>"
                                        data-current-prog="<?php echo $currentDeptId; ?>"
                                        onchange="updateProgram(this)"
                                        <?php echo $btnDisabled ?? ''; ?>>
                                    
                                    <option value="">Program Not Selected</option>
                                    <?php foreach ($allPrograms as $prog): ?>
                                        <option value="<?php echo $prog->getId(); ?>" 
                                            <?php echo ($currentDeptId == $prog->getId()) ? 'selected' : ''; ?>>
                                            <?php echo $prog->getName(); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Select your program.</small>
                            </div>

                            <hr class="mt-4 mb-4">
                            
                            <button type="submit" id="btnEditProfileSubmit" class="btn btn-primary">
                                Save Changes
                            </button>
                        </form>
                    </div>
                </div>

                <form id="formUploadDocument" onsubmit="return onUploadDocumentFormSubmit(event)">
                    <input type="hidden" name="userId" id="userId" value="<?php echo $user->getId(); ?>" />

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Document Submission</h5>
                        </div>
                        
                        <div class="card-body">
                            
                            <input type="hidden" id="documentType" value="<?php echo $selectedDocumentType; ?>">

                            <?php if ($previousUpload): ?>
                                <div class="alert alert-secondary mb-4">
                                    <input type="hidden" name="previousUploadId" id="previousUploadId" value="<?php echo $previousUpload->getId(); ?>" />
                                    <div class="row align-items-center">
									<div><p>Congratulations, you have uploaded a file. Please confirm that all other information on this page is correct and try downloading your file to be sure it looks correct. If you need to upload a new version of the file you can do that below. If everything looks correct, you have completed your task of uploading your graduate document to this website.</p></div>
                                        <div class="col-md-6 mb-2 mb-md-0">
                                            <h6 class="mb-1"><i class="fas fa-file-pdf text-danger mr-2"></i> File Previously Uploaded</h6>
                                            <small class="text-muted">Manage your existing submission for this document type.</small>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo htmlspecialchars('./uploads' . $previousUpload->getFilePath() . $previousUpload->getId()); ?>" 
                                            download="<?php echo htmlspecialchars($previousUpload->getFileName()); ?>" 
                                            class="col-sm-6">
                                            <?php echo $previousUpload->getFileName();?>
                                            </a>
                                            
                                            <?php // [4] Conditional rendering for Delete Button ?>
                                            <?php if ($uploadLocked): ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="File is currently under evaluation">
                                                    <i class="fas fa-lock"></i> Locked
                                                </button>
                                            <?php else: ?>
                                                <a id="aUploadDelete" class="btn btn-sm btn-outline-danger" onclick="onUploadDelete(event)">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php // [5] Conditional rendering for New File Input ?>
                            <?php if ($uploadLocked): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle mr-2"></i> 
                                    <strong>Submission Locked:</strong> An evaluation has already been generated for this document. You cannot upload a new version or delete the existing file while an evaluation is active.
                                </div>
                            <?php else: ?>
                                <div class="form-group row">
                                    <label for="userUpload" class="col-sm-3 col-form-label text-muted small text-uppercase font-weight-bold" id="userUploadLabel">
                                        New File
                                    </label>
                                    <div class="col-sm-9">
                                        <div class="custom-file">
                                            <input onchange="displayUpload()" name="userUpload" type="file" class="form-control pt-1" id="userUpload" accept="<?php echo implode(",", $uploadAcceptedTypes); ?>" style="height: auto;">
                                            <small class="form-text text-muted">Please ensure your file is in <?php echo $uploadAcceptedTypesString; ?> format.</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>

                        <div class="card-footer bg-white text-right">
                            <?php if (!$uploadLocked): ?>
                                <div class="btn-upload-submit d-inline-block">
                                    <span class="loader mr-2" id="btnUploadLoader" style="display:none;"></span>
                                    <button style="visibility:hidden;" type="submit" class="
									btn btn-primary" id="btnUploadSubmit">
                                        <i class="fas fa-save mr-1"></i> Upload Document
                                    </button>
                                </div>
                            <?php endif; ?>
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

                        <hr>

                        <h6 class="text-muted small text-uppercase font-weight-bold mb-2">User Roles</h6>
                        <div>
                            <?php 
                                $hasRoles = false;
                                if ($userFlags && is_array($userFlags)) {
                                    foreach ($userFlags as $flag) {
                                        if ($flag->getType() == 'Role') {
                                            $hasRoles = true;
                                            echo '<span class="badge bg-secondary mb-1">' . $flag->getName() . '</span> ';
                                        }
                                    }
                                }
                                if (!$hasRoles) {
                                    echo '<span class="text-muted small font-italic">No Roles</span>';
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Displays the Upload Button only after a file upload is selected.
 */
function displayUpload() {
    document.getElementById("btnUploadSubmit").style.visibility = "visible";
}

/**
 * Handles the logic to swap programs.
 * Removes the previously selected program flag and adds the new one.
 */
async function updateProgram(selectElem) {
    const userId = selectElem.getAttribute('data-user-id');
    const oldDeptId = selectElem.getAttribute('data-current-prog');
    const newDeptId = selectElem.value;
    const endpoint = '/users';

    selectElem.disabled = true;

    try {
        if (oldDeptId && oldDeptId !== "") {
            await api.post(endpoint, {
                action: 'toggleUserFlag',
                operation: 'remove',
                userId: userId,
                flagId: oldDeptId
            });
        }

        if (newDeptId && newDeptId !== "") {
            await api.post(endpoint, {
                action: 'toggleUserFlag',
                operation: 'add',
                userId: userId,
                flagId: newDeptId
            });
        }

        selectElem.setAttribute('data-current-prog', newDeptId);
    } catch (err) {
        console.error("Failed to update program", err);
        alert("An error occurred while saving the program selection. Please refresh the page.");
    } finally {
        selectElem.disabled = false;
    }
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
    changesDetected = false;
    $('#btnUploadLoader').show();
    return false;
}


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


/**
 * Handles the Profile Edit form submission.
 */
function onEditProfileFormSubmit(event) {
    if (event) event.preventDefault();

    let body = {
        action: 'updateUserProfile',
        userId: document.getElementById('userId').value,
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        email: document.getElementById('email').value
    };

    $('#btnEditProfileSubmit').attr('disabled', true);

    api.post('/users.php', body)
        .then(res => {
            snackbar('Profile updated successfully', 'success');
            setTimeout(function () { location.reload(); }, 1000);
        })
        .catch(err => {
            snackbar(err.message, 'error');
            $('#btnEditProfileSubmit').attr('disabled', false);
        });
}

</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>