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

// 1. Determine Server-Side Filters (Department Only)
$filterDepartment = isset($_GET['department']) && $_GET['department'] != '' ? $_GET['department'] : null;

// 2. Fetch Students based on Department Filter
$students = [];
if ($filterDepartment) {
    $students = $usersDao->getStudentsByDepartment($filterDepartment);
} else {
    $students = $usersDao->getAllStudents();
}

// 3. Fetch Uploads Data
$allUploads = $uploadsDao->getAllUploads();
$unassignedUploads = $uploadsDao->getAllUnassignedUploads();

// Create a lookup array for unassigned IDs
$unassignedIds = [];
foreach ($unassignedUploads as $upl) {
    $unassignedIds[$upl->getId()] = true;
}

// 4. Group All Uploads by User ID
$uploadsGroupedByUser = [];
foreach ($allUploads as $upload) {
    $uid = $upload->getFkUserId();
    if (!isset($uploadsGroupedByUser[$uid])) {
        $uploadsGroupedByUser[$uid] = [];
    }
    $uploadsGroupedByUser[$uid][] = $upload;
}

// 5. Build Department Map (ID -> Name) for display
$department_flags = $usersDao->getAllDepartmentFlags();
$deptMap = [];
foreach ($department_flags as $dept) {
    $deptMap[$dept->getId()] = $dept->getName();
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
    
    /* Child Table Styles */
    .child-table {
        width: 100%;
        margin: 10px 0;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .child-table th { font-size: 0.85em; color: #6c757d; }
    .child-table td { vertical-align: middle; }
    
    /* Visually distinguish assigned rows */
    .assigned-upload td {
        color: #6c757d;
        background-color: #f1f1f1;
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
</style>

<div class="container-fluid">
    <div class="container mt-5 mb-5">

        <div class="row mb-4">
            <div class="col">
                <h2>Assign Reviews</h2>
                <p class="text-muted">Select students below to view and assign their uploads.</p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Student List</h5>
            </div>
            <div class="card-body">
                
                <div class="form-row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase font-weight-bold">Filter by Department</label>
                        <select id="departments" name="departments" class="form-control" onchange="filterDepartments()">
                            <option value="">All Departments</option>
                            <?php 
                                foreach ($department_flags as $dept) {
                                    $selected = ($filterDepartment == $dept->getId()) ? 'selected' : '';
                                    echo '<option value="'. $dept->getId() .'" '.$selected.'>'. $dept->getName() .'</option>';
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

                <table id="usersTable" class="table table-striped table-hover table-bordered w-100">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" style="width: 40px;"></th> <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Department</th>
                            <th scope="col" class="text-center">Files</th>
                            <th scope="col">Last Login</th>
                        </tr>
                    </thead>
                    <tbody id="uploadsTableBody">
                        <?php 
                            foreach ($students as $student) {
                                $studentId = $student->getId();
                                
                                // Get Uploads
                                $userUploads = isset($uploadsGroupedByUser[$studentId]) ? $uploadsGroupedByUser[$studentId] : [];

                                // Prepare Uploads JSON for child row
                                $uploadsPayload = [];
                                foreach($userUploads as $up) {
                                    $docType = $uploadsDao->getDocumentType($up->getId());
                                    $isAssigned = !isset($unassignedIds[$up->getId()]); 
                                    
                                    // FORMAT UPLOAD DATE (Standardize Format)
                                    $dateRaw = $up->getDateUploaded();
                                    $dateDisplay = '';
                                    if ($dateRaw instanceof DateTime) {
                                        $dateDisplay = $dateRaw->format("m/d/Y g:i A");
                                    } elseif (is_string($dateRaw) && !empty($dateRaw)) {
                                        $dateDisplay = date("m/d/Y g:i A", strtotime($dateRaw));
                                    } else {
                                        $dateDisplay = '-';
                                    }

                                    $uploadsPayload[] = [
                                        'id' => $up->getId(),
                                        'name' => $docType ? $docType->getName() : 'Unknown Type',
                                        'date' => $dateDisplay, // Uses the formatted date string
                                        'isAssigned' => $isAssigned
                                    ];
                                }
                                $jsonUploads = htmlspecialchars(json_encode($uploadsPayload), ENT_QUOTES, 'UTF-8');
                                
                                // Format Data
                                
                                // 1. Name: Lastname, Firstname
                                $lname = method_exists($student, 'getLastName') ? $student->getLastName() : '';
                                $fname = method_exists($student, 'getFirstName') ? $student->getFirstName() : '';
                                if ($lname && $fname) {
                                    $nameDisplay = $lname . ', ' . $fname;
                                } else {
                                    $nameDisplay = $student->getFullName(); 
                                }

                                // 2. Department Name
                                $dId = method_exists($student, 'getDepartmentId') ? $student->getDepartmentId() : null;
                                $deptName = ($dId && isset($deptMap[$dId])) ? $deptMap[$dId] : '<span class="text-muted">-</span>';

                                // 3. Last Login (Handling DateTime object)
                                $lastLoginRaw = method_exists($student, 'getLastLogin') ? $student->getLastLogin() : null;
                                $lastLoginDisplay = '<span class="text-muted small">Never</span>';

                                if ($lastLoginRaw instanceof DateTime) {
                                    $lastLoginDisplay = $lastLoginRaw->format("m/d/Y g:i A");
                                } elseif (is_string($lastLoginRaw) && !empty($lastLoginRaw)) {
                                    $lastLoginDisplay = date("m/d/Y g:i A", strtotime($lastLoginRaw));
                                }

                                // 4. Files Badge
                                $fileCount = count($userUploads);
                                $filesDisplay = $fileCount > 0 
                                    ? '<span class="badge bg-primary">' . $fileCount . '</span>' 
                                    : '<span class="text-muted small">None</span>';

                                echo '<tr data-uploads="' . $jsonUploads . '">';
                                echo '<td class="dt-control text-center" style="cursor:pointer; color: #007bff;"><i class="fas fa-plus-circle"></i></td>';
                                echo '<td class="font-weight-bold">' . $nameDisplay . '</td>';
                                echo '<td>' . $student->getEmail() . '</td>';
                                echo '<td>' . $deptName . '</td>';
                                echo '<td class="text-center">' . $filesDisplay . '</td>';
                                echo '<td>' . $lastLoginDisplay . '</td>';
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
                <span id="selectionCount" class="text-muted small font-weight-bold">0 Selected</span>
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
                        $rubrics = $rubricsDao->getAllRubricTemplates();
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
    function filterDepartments() {
        const deptValue = document.getElementById("departments").value;
        let url = "assignReviews.php?";
        if (deptValue) {
            url += "department=" + deptValue;
        }
        window.location.href = url;
    }

    function updateUploadVisibility() {
        const showAll = document.getElementById('filterStatus').value === 'all';
        const assignedRows = document.querySelectorAll('.assigned-upload');
        assignedRows.forEach(row => {
            if (showAll) row.classList.remove('d-none');
            else row.classList.add('d-none');
        });
    }

    function format(rowData, tr) {
        var uploadsData = $(tr).data('uploads');
        var showAll = document.getElementById('filterStatus').value === 'all';
        
        if (!uploadsData || uploadsData.length === 0) {
            return '<div class="p-3 text-muted">No uploads found.</div>';
        }

        var html = '<div class="p-3 bg-white border-left border-primary ml-3">';
        html += '<h6 class="text-primary mb-2">Available Uploads</h6>';
        html += '<table class="table table-sm table-bordered mb-0 bg-white">';
        html += '<thead class="thead-light"><tr><th>Document Type</th><th>Date Uploaded</th><th class="text-center" style="width: 100px;">Select</th></tr></thead>';
        html += '<tbody>';
        
        uploadsData.forEach(function(upload) {
            let rowClass = upload.isAssigned ? 'assigned-upload' : '';
            let hiddenClass = (upload.isAssigned && !showAll) ? 'd-none' : '';
            let statusBadge = upload.isAssigned ? ' <span class="badge bg-primary ml-2">Assigned</span>' : '';

            html += '<tr class="' + rowClass + ' ' + hiddenClass + '">';
            html += '<td>' + upload.name + statusBadge + '</td>';
            html += '<td>' + upload.date + '</td>';
            html += '<td class="text-center"><input class="form-check-input upload-checkbox position-static" type="checkbox" value="' + upload.id + '"></td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        html += '<div class="text-muted small mt-2 font-italic status-note">Toggle "Filter by Status" to see previously assigned uploads.</div>';
        html += '</div>';
        return html;
    }

    // Initialize DataTable matching columns exactly
    let table = new DataTable('#usersTable', {
        columns: [
            { className: 'dt-control', orderable: false, data: null, defaultContent: '' }, // 0
            { data: 'name' },        // 1
            { data: 'email' },       // 2
            { data: 'department' },  // 3
            { data: 'files' },       // 4
            { data: 'last_login' }   // 5
        ],
        order: [[1, 'asc']]
    });
    
    table.on('click', 'tbody td.dt-control', function (e) {
        let tr = e.target.closest('tr');
        let row = table.row(tr);
        let icon = tr.querySelector('i');
    
        if (row.child.isShown()) {
            row.child.hide();
            tr.classList.remove('shown');
            if(icon) { icon.classList.remove('fa-minus-circle'); icon.classList.add('fa-plus-circle'); }
        }
        else {
            row.child(format(row.data(), tr)).show();
            tr.classList.add('shown');
            if(icon) { icon.classList.remove('fa-plus-circle'); icon.classList.add('fa-minus-circle'); }
        }
    });

    if(typeof MultiSelect !== 'undefined') {
        document.querySelectorAll('[data-multi-select]').forEach(select => {
            new MultiSelect(select);
        });
    }

    function updateActionBarVisibility() {
        const selectedCheckboxes = document.querySelectorAll('.upload-checkbox:checked');
        const actionBar = document.getElementById('assignmentActionBar');
        const countLabel = document.getElementById('selectionCount');
        const count = selectedCheckboxes.length;

        if (count > 0) {
            actionBar.classList.remove('d-none');
            countLabel.innerText = count + ' Item' + (count !== 1 ? 's' : '') + ' Selected';
        } else {
            actionBar.classList.add('d-none');
        }
    }

    document.getElementById('uploadsTableBody').addEventListener('change', function(e) {
        if (e.target.classList.contains('upload-checkbox')) {
            updateActionBarVisibility();
        }
    });

    document.getElementById('btnAssign').addEventListener('click', () => {
        const btn = document.getElementById('btnAssign');
        
        const selectedUploads = Array.from(document.querySelectorAll('.upload-checkbox:checked'))
            .map(cb => cb.value);
        
        let reviewerInputs = document.querySelectorAll('input[name="reviewers[]"]');
        if (reviewerInputs.length === 0) {
            reviewerInputs = document.querySelectorAll('input[name="reviewers"]');
        }
        const selectedReviewers = Array.from(reviewerInputs).map(input => input.value);

        const rubricSelect = document.getElementById('rubrics');
        const selectedRubric = rubricSelect ? rubricSelect.value : null;

        if (selectedUploads.length === 0) {
            if (typeof snackbar === 'function') snackbar('Please select at least one upload.', 'error');
            else alert('Please select at least one upload.');
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