<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\UploadsDao;
use DataAccess\EvaluationsDao; // [1] Add Namespace

// 1. Setup DAOs
$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);
$evaluationsDao = new EvaluationsDao($dbConn, $logger); // [2] Initialize DAO

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

// A. Gather the User's Department IDs
$userDeptIds = [];
if ($userFlags) {
    foreach ($userFlags as $flag) {
        if ($flag->getType() === 'Department') {
            $userDeptIds[] = $flag->getId();
        }
    }
}

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
$allRoles = $usersDao->getAllRoleFlags();
$allDepartments = $usersDao->getAllDepartmentFlags();

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
                        <form id="formEditProfile">
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
                            
                            <button type="submit" id="btnEditProfileSubmit" class="btn btn-primary">
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
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo htmlspecialchars('./uploads' . $previousUpload->getFilePath() . $previousUpload->getId()); ?>" 
                                            download="<?php echo htmlspecialchars($previousUpload->getFileName()); ?>" 
                                            class="btn btn-sm btn-outline-primary">
                                            Download
                                            </a>
                                            
                                            <?php // [4] Conditional rendering for Delete Button ?>
                                            <?php if ($uploadLocked): ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="File is currently under evaluation">
                                                    <i class="fas fa-lock"></i> Locked
                                                </button>
                                            <?php else: ?>
                                                <a id="aUploadDelete" class="btn btn-sm btn-outline-danger">Delete</a>
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
                                        New File (PDF)
                                    </label>
                                    <div class="col-sm-9">
                                        <div class="custom-file">
                                            <input name="userUpload" type="file" class="form-control pt-1" id="userUpload" accept=".pdf, application/pdf" style="height: auto;">
                                            <small class="form-text text-muted">Please ensure your file is in PDF format.</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>

                        <div class="card-footer bg-white text-right">
                            <?php if (!$uploadLocked): ?>
                                <div class="btn-upload-submit d-inline-block">
                                    <span class="loader mr-2" id="btnUploadLoader" style="display:none;"></span>
                                    <button type="submit" class="btn btn-primary" id="btnUploadSubmit">
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
                        
                        <h6 class="text-muted small text-uppercase font-weight-bold mb-2">Department</h6>
                        <div class="mb-3">
                            <?php 
                                // Logic to determine the currently selected department (if any)
                                // We check if the user has a flag that matches a department ID
                                $currentDeptId = '';
                                if ($userFlagIds) {
                                    foreach ($allDepartments as $dept) {
                                        if (in_array($dept->getId(), $userFlagIds)) {
                                            $currentDeptId = $dept->getId();
                                            break; // Enforce single selection by taking the first match
                                        }
                                    }
                                }
                            ?>

                            <select class="form-control" 
                                    id="departmentSelect" 
                                    data-user-id="<?php echo $user->getId(); ?>"
                                    data-current-dept="<?php echo $currentDeptId; ?>"
                                    onchange="updateDepartment(this)"
                                    <?php echo $btnDisabled ?? ''; ?>>
                                
                                <option value="">Select Department...</option>
                                <?php foreach ($allDepartments as $dept): ?>
                                    <option value="<?php echo $dept->getId(); ?>" 
                                        <?php echo ($currentDeptId == $dept->getId()) ? 'selected' : ''; ?>>
                                        <?php echo $dept->getName(); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Select a single department for this user.</small>
                        </div>

                        <hr>

                        <h6 class="text-muted small text-uppercase font-weight-bold mb-2">User Roles</h6>
                        <div>
                            <?php foreach ($allRoles as $role): ?>
                                <?php 
                                    $hasFlag = in_array($role->getId(), $userFlagIds);
                                    $btnStyle = $hasFlag ? 'btn-secondary' : 'btn-outline-secondary';
                                    $action = $hasFlag ? 'remove' : 'add'; // Still needed for the generic buttons
                                ?>
                                <button type="button" 
                                        class="btn btn-sm <?php echo $btnStyle; ?> mb-1 btn-flag-toggle"
                                        data-flag-id="<?php echo $role->getId(); ?>"
                                        data-user-id="<?php echo $user->getId(); ?>"
                                        data-action="<?php echo $action; ?>" 
                                        data-type="secondary"
                                        <?php echo $btnDisabled ?? ''; ?>>
                                    <?php echo $role->getName(); ?>
                                </button>
                            <?php endforeach; ?>
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
        const documentType = document.getElementById("documentType").value;
        const userId = document.getElementById("userId").value; 

        window.location.replace("profile.php?documentType=" + documentType + "&userId=" + userId);
    }

    /**
     * Handles the logic to swap departments.
     * Removes the previously selected department flag and adds the new one.
     */
    async function updateDepartment(selectElem) {
        const userId = selectElem.getAttribute('data-user-id');
        const oldDeptId = selectElem.getAttribute('data-current-dept');
        const newDeptId = selectElem.value;
        
        // UPDATED: Point to the root '/users' endpoint which the UserActionHandler listens to
        const endpoint = '/users'; 

        // Disable select while processing to prevent rapid clicks
        selectElem.disabled = true;

        try {
            // 1. Remove the old department (if one was previously set)
            if (oldDeptId && oldDeptId !== "") {
                await api.post(endpoint, { 
                    action: 'toggleUserFlag', // MATCHES CASE IN handleRequest()
                    operation: 'remove',      // MATCHES PARAM IN handleToggleFlag()
                    userId: userId, 
                    flagId: oldDeptId 
                });
            }

            // 2. Add the new department (if the user didn't just select the placeholder)
            if (newDeptId && newDeptId !== "") {
                await api.post(endpoint, { 
                    action: 'toggleUserFlag', // MATCHES CASE IN handleRequest()
                    operation: 'add',         // MATCHES PARAM IN handleToggleFlag()
                    userId: userId, 
                    flagId: newDeptId 
                });
            }

            // 3. Update the tracker so the next change knows what to remove
            selectElem.setAttribute('data-current-dept', newDeptId);
            
            // Optional: Provide visual feedback
            // alert('Department updated');

        } catch (err) {
            console.error("Failed to update department", err);
            alert("An error occurred while saving the department selection. Please refresh the page.");
        } finally {
            selectElem.disabled = false;
        }
    }
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>