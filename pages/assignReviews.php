<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\UploadsDao;
use DataAccess\RubricsDao;
use DataAccess\DocumentTypesDao;

$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);
$rubricsDao = new RubricsDao($dbConn, $logger);
$documentTypesDao = new DocumentTypesDao($dbConn, $logger);

// 1. Determine Server-Side Filters (Program Only)
$filterProgram = isset($_GET['program']) && $_GET['program'] != '' ? $_GET['program'] : null;

// 2. Fetch Students based on Program Filter
$students = [];
if ($filterProgram) {
    $students = $usersDao->getStudentsByDepartment($filterProgram);
} else {
    $students = $usersDao->getAllStudents();
}

// 3. Build a User Lookup Map (ID -> display name)
$userMap = [];
foreach ($students as $student) {
    $lname = method_exists($student, 'getLastName') ? $student->getLastName() : '';
    $fname = method_exists($student, 'getFirstName') ? $student->getFirstName() : '';
    if ($lname && $fname) {
        $userMap[$student->getId()] = $lname . ', ' . $fname;
    } else {
        $userMap[$student->getId()] = $student->getFullName();
    }
}

// 4. Fetch Uploads Data
$allUploads = $uploadsDao->getAllUploads();
$unassignedUploads = $uploadsDao->getAllUnassignedUploads();

// Create a lookup array for unassigned IDs
$unassignedIds = [];
foreach ($unassignedUploads as $upl) {
    $unassignedIds[$upl->getId()] = true;
}

// 5. Build Program Map (ID -> Name) for display
$program_flags = $usersDao->getAllDepartmentFlags();
$progMap = [];
foreach ($program_flags as $prog) {
    $progMap[$prog->getId()] = $prog->getName();
}

require_once PUBLIC_FILES . '/lib/osu-identities-api.php';
include_once PUBLIC_FILES . '/modules/header.php';
?>

<style>
    /* Fixed Bottom Bar Dropup Styles */
    #assignmentActionBar .multi-select-options {
        top: auto !important;
        bottom: 100% !important;
        margin-bottom: 10px;
        margin-top: 0 !important;
        max-height: 50vh !important;
        overflow-y: auto !important;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.15);
    }
    
    /* Visually distinguish assigned rows */
    .assigned-upload td {
        color: #6c757d;
        background-color: #f1f1f1;
    }

    /* Select button styles */
    .btn-select-upload {
        min-width: 80px;
    }
    .btn-select-upload.selected {
        background-color: #28a745;
        border-color: #28a745;
        color: #fff;
    }

    /* --- Rubric Dropdown Styling --- */
    #rubrics {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        box-shadow: none !important;
        box-sizing: border-box !important;
        width: 100% !important;
        height: 45px !important;
        min-height: 45px !important;
        margin: 0 !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 5px !important;
        font-size: 16px !important;
        color: #212529 !important;
        font-family: inherit !important;
        line-height: 1.5 !important;
        padding: 7px 30px 7px 12px !important;
        cursor: pointer !important;
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23949ba3' viewBox='0 0 16 16'%3E%3Cpath d='M8 13.1l-8-8 2.1-2.2 5.9 5.9 5.9-5.9 2.1 2.2z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 15px center !important;
        background-size: 12px 12px !important;
    }
    #rubrics:focus {
        outline: none !important;
        border-color: #c1c9d0 !important;
    }
    #rubrics:invalid {
        color: #65727e !important;
    }
    #rubrics option {
        color: #212529;
    }

    /* Ensure snackbar displays above the fixed bottom bar on this page */
    #snackbar {
        z-index: 1100 !important;
    }
</style>

<div class="container-fluid">
    <div class="container mt-5 mb-5">

        <div class="row mb-4">
            <div class="col">
                <h2>Assign Reviews</h2>
                <p class="text-muted">Select an upload below to assign for review.</p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Uploads</h5>
            </div>
            <div class="card-body">
                
                <div class="form-row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase font-weight-bold">Filter by Program</label>
                        <select id="programs" name="programs" class="form-control" onchange="filterPrograms()">
                            <option value="">All Programs</option>
                            <?php 
                                foreach ($program_flags as $prog) {
                                    $selected = ($filterProgram == $prog->getId()) ? 'selected' : '';
                                    echo '<option value="'. $prog->getId() .'" '.$selected.'>'. $prog->getName() .'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase font-weight-bold">Filter by Status</label>
                        <select id="filterStatus" class="form-control" onchange="updateUploadVisibility()">
                            <option value="unassigned" selected>Unassigned Uploads Only</option>
                            <option value="all">All Uploads (Includes Assigned)</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-4 mb-4">

                <table id="uploadsTable" class="table table-striped table-hover table-bordered w-100">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Document Type</th>
                            <th scope="col">Date Uploaded</th>
                            <th scope="col">Student Name</th>
                            <th scope="col" class="text-center" style="width: 100px;">Select</th>
                        </tr>
                    </thead>
                    <tbody id="uploadsTableBody">
                        <?php 
                            foreach ($allUploads as $upload) {
                                $uploadId = $upload->getId();
                                $userId = $upload->getFkUserId();
                                $isAssigned = !isset($unassignedIds[$uploadId]);

                                // Skip uploads whose owner is not in the filtered student list
                                if (!isset($userMap[$userId])) continue;

                                // Document Type
                                $docType = $uploadsDao->getDocumentType($uploadId);
                                $docTypeName = $docType ? $docType->getName() : 'Unknown Type';

                                // Date Uploaded
                                $dateRaw = $upload->getDateUploaded();
                                $dateDisplay = '-';
                                if ($dateRaw instanceof DateTime) {
                                    $dateDisplay = $dateRaw->format("m/d/Y g:i A");
                                } elseif (is_string($dateRaw) && !empty($dateRaw)) {
                                    $dateDisplay = date("m/d/Y g:i A", strtotime($dateRaw));
                                }

                                // Student Name
                                $studentName = $userMap[$userId];

                                // Row classes for assigned styling / filtering
                                $rowClass = $isAssigned ? 'assigned-upload d-none' : '';
                                $statusBadge = $isAssigned ? ' <span class="badge bg-primary ml-2">Assigned</span>' : '';

                                echo '<tr class="' . $rowClass . '">';
                                echo '<td>' . $docTypeName . $statusBadge . '</td>';
                                echo '<td>' . $dateDisplay . '</td>';
                                echo '<td class="font-weight-bold">' . $studentName . '</td>';
                                echo '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary btn-select-upload" data-upload-id="' . $uploadId . '">Select</button></td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div style="height: 150px;"></div>
    </div>
</div>

<div id="assignmentActionBar" class="fixed-bottom bg-white border-top shadow-lg py-3 d-none" style="z-index: 1030;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-2">
                <h5 class="mb-0 text-primary">Assigning</h5>
                <span id="selectionCount" class="text-muted small font-weight-bold">1 Upload Selected</span>
            </div>
            <div class="col-lg-4 position-relative">
                <label for="reviewers" class="text-muted small text-uppercase font-weight-bold mb-1">Reviewers</label>
                <select id="reviewers" name="reviewers" data-placeholder="Select Reviewers..." multiple data-multi-select class="form-control form-control-sm">
                    <?php 
                        $reviewers = $usersDao->getAllReviewers();
                        foreach ($reviewers as $reviewer) {
                            echo '<option value="'. $reviewer->getId() .'">'. $reviewer->getFullName() .'</option>';
                        }
                    ?>
                </select>
            </div>
            <div class="col-lg-3">
                <label for="rubrics" class="text-muted small text-uppercase font-weight-bold mb-1">Rubric</label>
                <select id="rubrics" name="rubrics" class="form-control" required>
                    <option value="" selected disabled>Select Rubric...</option>
                    <?php 
                        $rubrics = $rubricsDao->getAllRubrics();
                        foreach ($rubrics as $rubric) {
                            echo '<option value="'. $rubric->getId() .'">'. $rubric->getName() .'</option>';
                        }
                    ?>
                </select>
            </div>
            <div class="col-lg-3 text-right">
                 <button id="btnAssign" class="btn btn-primary btn-block mt-3 mt-lg-0">Create Evaluations</button>
            </div>
        </div>
    </div>
</div>

<script>
    function filterPrograms() {
        const progValue = document.getElementById("programs").value;
        let url = "?";
        if (progValue) {
            url += "program=" + progValue;
        }
        window.location.href = window.location.pathname + url;
    }

    function updateUploadVisibility() {
        const showAll = document.getElementById('filterStatus').value === 'all';
        const assignedRows = document.querySelectorAll('.assigned-upload');
        assignedRows.forEach(row => {
            if (showAll) row.classList.remove('d-none');
            else row.classList.add('d-none');
        });
    }

    // Initialize DataTable
    let table = new DataTable('#uploadsTable', {
        columns: [
            { data: 'docType' },      // 0 - Document Type
            { data: 'dateUploaded' },  // 1 - Date Uploaded
            { data: 'studentName' },   // 2 - Student Name
            { orderable: false, searchable: false } // 3 - Select
        ],
        order: [[2, 'asc']]
    });

    if(typeof MultiSelect !== 'undefined') {
        document.querySelectorAll('[data-multi-select]').forEach(select => {
            new MultiSelect(select);
        });
    }

    function updateActionBarVisibility() {
        const selected = document.querySelector('.btn-select-upload.selected');
        const actionBar = document.getElementById('assignmentActionBar');

        if (selected) {
            actionBar.classList.remove('d-none');
        } else {
            actionBar.classList.add('d-none');
        }
    }

    document.getElementById('uploadsTableBody').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-select-upload');
        if (!btn) return;

        // Deselect any previously selected button
        const prev = document.querySelector('.btn-select-upload.selected');
        if (prev && prev !== btn) {
            prev.classList.remove('selected', 'btn-success');
            prev.classList.add('btn-outline-primary');
            prev.textContent = 'Select';
        }

        // Toggle current button
        if (btn.classList.contains('selected')) {
            btn.classList.remove('selected', 'btn-success');
            btn.classList.add('btn-outline-primary');
            btn.textContent = 'Select';
        } else {
            btn.classList.add('selected', 'btn-success');
            btn.classList.remove('btn-outline-primary');
            btn.textContent = 'Selected';
        }

        updateActionBarVisibility();
    });

    document.getElementById('btnAssign').addEventListener('click', () => {
        const btn = document.getElementById('btnAssign');
        
        const selectedBtn = document.querySelector('.btn-select-upload.selected');
        const selectedUploads = selectedBtn ? [selectedBtn.dataset.uploadId] : [];
        
        let reviewerInputs = document.querySelectorAll('input[name="reviewers[]"]');
        if (reviewerInputs.length === 0) {
            reviewerInputs = document.querySelectorAll('input[name="reviewers"]');
        }
        const selectedReviewers = Array.from(reviewerInputs).map(input => input.value);

        const rubricSelect = document.getElementById('rubrics');
        const selectedRubric = rubricSelect ? rubricSelect.value : null;

        if (selectedUploads.length === 0) {
            if (typeof snackbar === 'function') snackbar('Please select an upload.', 'error');
            else alert('Please select an upload.');
            return;
        }
        if (selectedReviewers.length === 0) {
            if (typeof snackbar === 'function') snackbar('Please select at least one reviewer.', 'error');
            else alert('Please select at least one reviewer.');
            return;
        }
        if (!selectedRubric) {
            if (typeof snackbar === 'function') snackbar('Please select a rubric.', 'error');
            else alert('Please select a rubric.');
            return;
        }

        const body = {
            action: 'assignEvaluations',
            uploadIds: selectedUploads,
            reviewerIds: selectedReviewers,
            rubricId: selectedRubric
        };

        btn.disabled = true;
        btn.innerText = 'Processing...';

        api.post('/evaluations.php', body)
            .then(res => {
                if (typeof snackbar === 'function') snackbar(res.message, 'success');
                else alert(res.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                if (typeof snackbar === 'function') snackbar(err.message, 'error');
                else alert('Error: ' + err.message);
                
                btn.disabled = false;
                btn.innerText = 'Create Evaluations';
            });
    });
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>