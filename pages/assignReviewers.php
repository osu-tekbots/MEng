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

// 1. Fetch Uploads based on filter
$uploads = [];
if (isset($_GET['onlyUnassigned']) && $_GET['onlyUnassigned'] == 'all') {
    $uploads = $uploadsDao->getAllUploads();
} else {
    // Default to unassigned only
    $uploads = $uploadsDao->getAllUnassignedUploads();
}

// 2. Group Uploads by User ID
// Structure: [ userId => [ upload1, upload2... ] ]
$uploadsGroupedByUser = [];
foreach ($uploads as $upload) {
    $uid = $upload->getFkUserId();
    if (!isset($uploadsGroupedByUser[$uid])) {
        $uploadsGroupedByUser[$uid] = [];
    }
    $uploadsGroupedByUser[$uid][] = $upload;
}

$department_flags = $usersDao->getAllDepartmentFlags();

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
    
    /* Styles for the expanded child row */
    .child-table {
        width: 100%;
        margin: 10px 0;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .child-table th { font-size: 0.85em; color: #6c757d; }
    .child-table td { vertical-align: middle; }
</style>

<div class="container-fluid">
    <div class="container mt-5 mb-5">

        <div class="row mb-4">
            <div class="col">
                <h2>Upload Management</h2>
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
                        <select id="departments" name="departments" class="form-control">
                            <option value="" selected disabled>All Departments</option>
                            <?php 
                                foreach ($department_flags as $department_flag) {
                                    echo '<option value="'. $department_flag->getId() .'">'. $department_flag->getFlagName() .'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase font-weight-bold">Filter by Status</label>
                        <select id="assigned" name="assigned" class="form-control" onChange="onAssignmentChange()">
                            <?php 
                                if (isset($_GET['onlyUnassigned']) && $_GET['onlyUnassigned'] == 'all') {
                                    echo '<option value="all" selected>All Uploads</option>';
                                    echo '<option value="unassigned">Unassigned Uploads Only</option>';
                                } else {
                                    echo '<option value="all">All Uploads</option>';
                                    echo '<option value="unassigned" selected>Unassigned Uploads Only</option>';
                                }
                            ?>
                        </select>
                    </div>
                </div>

                <hr class="mt-4 mb-4">

                <table id="usersTable" class="table table-striped table-hover table-bordered w-100">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" style="width: 50px;"></th> <th scope="col" class="col-5">Student Name</th>
                            <th scope="col" class="col-5">Email</th>
                            <th scope="col" class="text-center col-2">Upload Count</th>
                        </tr>
                    </thead>
                    <tbody id="uploadsTableBody">
                        <?php 
                            // Loop through the grouped users
                            foreach ($uploadsGroupedByUser as $userId => $userUploads) {
                                $user = $usersDao->getUser($userId);
                                if(!$user) continue; // Skip if user not found

                                // Prepare upload data for JS
                                $uploadsPayload = [];
                                foreach($userUploads as $up) {
                                    $docType = $uploadsDao->getDocumentType($up->getId());
                                    $uploadsPayload[] = [
                                        'id' => $up->getId(),
                                        'name' => $docType ? $docType->getFlagName() : 'Unknown Type',
                                        'date' => $up->getDateUploaded()
                                    ];
                                }
                                $jsonUploads = htmlspecialchars(json_encode($uploadsPayload), ENT_QUOTES, 'UTF-8');

                                echo '<tr data-uploads="' . $jsonUploads . '">';
                                echo '<td class="dt-control text-center" style="cursor:pointer; color: #007bff;"><i class="fas fa-plus-circle"></i></td>';
                                echo '<td>' . $user->getFullName() . '</td>';
                                echo '<td>' . $user->getEmail() . '</td>';
                                echo '<td class="text-center"><span class="badge bg-secondary">' . count($userUploads) . '</span></td>';
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
                <select id="rubrics" name="rubrics" class="form-control">
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
    function onAssignmentChange() {
        const assigned = document.getElementById("assigned");
        window.location.replace("assignReviewers.php?onlyUnassigned=" + assigned.value);
    }

    /**
     * Generates the Child Table HTML
     * rowData: DataTables row object
     * tr: The raw DOM <tr> element
     */
    function format(rowData, tr) {
        // Retrieve the JSON data we stored in the data-uploads attribute
        var uploadsData = $(tr).data('uploads');
        
        if (!uploadsData || uploadsData.length === 0) {
            return '<div class="p-3">No uploads found.</div>';
        }

        var html = '<div class="p-3 bg-white border-left border-primary ml-3">';
        html += '<h6 class="text-primary mb-2">Available Uploads</h6>';
        html += '<table class="table table-sm table-bordered mb-0 bg-white">';
        html += '<thead class="thead-light"><tr><th>Document Type</th><th>Date Uploaded</th><th class="text-center" style="width: 100px;">Select</th></tr></thead>';
        html += '<tbody>';
        
        uploadsData.forEach(function(upload) {
            html += '<tr>';
            html += '<td>' + upload.name + '</td>';
            html += '<td>' + upload.date + '</td>';
            html += '<td class="text-center"><input class="form-check-input upload-checkbox position-static" type="checkbox" value="' + upload.id + '"></td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    // Initialize DataTable
    let table = new DataTable('#usersTable', {
        columns: [
            { className: 'dt-control', orderable: false, data: null, defaultContent: '' },
            { data: 'name' }, // Corresponds to student name column
            { data: 'email' }, // Corresponds to email column
            { data: 'count' }  // Corresponds to count column
        ],
        order: [[1, 'asc']]
    });
    
    // Toggle Child Row
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
            // Pass the raw TR to get the data-attribute
            row.child(format(row.data(), tr)).show();
            tr.classList.add('shown');
            if(icon) { icon.classList.remove('fa-plus-circle'); icon.classList.add('fa-minus-circle'); }
            
            // Re-bind change events for new checkboxes that just appeared
            // (Note: The delegated listener on uploadsTableBody handles this, but good to be aware)
        }
    });

    if(typeof MultiSelect !== 'undefined') {
        document.querySelectorAll('[data-multi-select]').forEach(select => {
            new MultiSelect(select);
        });
    }

    // --- SELECTION & ACTION BAR LOGIC ---

    function updateActionBarVisibility() {
        // Need to query selector inside the specific table to catch dynamic rows
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

    // Event Delegation: Listens on the table body for clicks on checkboxes
    // This works even for checkboxes created dynamically in the child rows
    document.getElementById('uploadsTableBody').addEventListener('change', function(e) {
        if (e.target.classList.contains('upload-checkbox')) {
            updateActionBarVisibility();
        }
    });

    // --- ASSIGNMENT SUBMISSION LOGIC ---

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