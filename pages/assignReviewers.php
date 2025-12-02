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

$uploads = $uploadsDao->getAllUnassignedUploads();
if (isset($_GET['onlyUnassigned'])) {
    if ($_GET['onlyUnassigned'] == 'unassigned') {
        $uploads = $uploadsDao->getAllUnassignedUploads();
    } else {
        $uploads = $uploadsDao->getAllUploads();
    }
}

$department_flags = $usersDao->getAllDepartmentFlags();

require_once PUBLIC_FILES . '/lib/osu-identities-api.php';

include_once PUBLIC_FILES . '/modules/header.php';
?>

<style>
    /* Target the options list specifically inside the bottom Action Bar.
       We use !important to override any inline styles the JS library might apply.
    */
    #assignmentActionBar .multi-select-options {
        top: auto !important;        /* Stop it from calculating distance from top */
        bottom: 100% !important;     /* Anchor it to the bottom of the input (pushes it up) */
        margin-bottom: 10px;         /* Add a small gap between input and list */
        margin-top: 0 !important;    /* Remove default top margins */
        max-height: 50vh !important; /* Limit height so it doesn't fly off the top of the screen */
        overflow-y: auto !important; /* Ensure scrollbar appears if list is long */
        box-shadow: 0 -4px 12px rgba(0,0,0,0.15); /* Shadow directed upwards */
    }
</style>

<div class="container-fluid">
    <div class="container mt-5 mb-5">

        <div class="row mb-4">
            <div class="col">
                <h2>Upload Management</h2>
                <p class="text-muted">Review student submissions and assign evaluators.</p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Student Uploads</h5>
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
                                if (isset($_GET['onlyUnassigned'])) {
                                    if ($_GET['onlyUnassigned'] == 'unassigned') {
                                        echo '<option value="all">All Uploads</option>';
                                        echo '<option value="unassigned" selected>Unassigned Uploads Only</option>';
                                    } else {
                                        echo '<option value="all" selected>All Uploads</option>';
                                        echo '<option value="unassigned">Unassigned Uploads Only</option>';
                                    }
                                } else {
                                    echo '<option value="all">All Uploads</option>';
                                    echo '<option value="unassigned" selected>Unassigned Uploads Only</option>';
                                }
                            ?>
                        </select>
                    </div>
                </div>

                <hr class="mt-4 mb-4">

                <table id="uploadsTable" class="table table-striped table-hover table-bordered w-100">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Student Name</th>
                            <th scope="col">Department/Major</th>
                            <th scope="col">Date Uploaded</th>
                            <th scope="col" class="text-center">Select</th>
                        </tr>
                    </thead>
                    <tbody id="uploadsTableBody">
                        <?php 
                            foreach ($uploads as $upload) {
                                echo '<tr>';
                                echo '<td></td>';
                                $uploader = $usersDao->getUser($upload->getFkUserId());
                                echo '<td>' . $uploader->getFullName() . '</td>';
                                $documentTypeFlag = $uploadsDao->getDocumentType($upload->getId());
                                echo '<td>' . $documentTypeFlag->getFlagName() . '</td>';
                                echo '<td>' . $upload->getDateUploaded() . '</td>';
                                echo '<td class="text-center"><input class="form-check-input upload-checkbox" type="checkbox" value="'.$upload->getId().'"></td>';
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

    function format(d) {
        return (
            '<dl>' +
            '<dt>Full name:</dt><dd>' + d.name + '</dd>' +
            '<dt>Extra info:</dt><dd>Additional details...</dd>' +
            '</dl>'
        );
    }

    // Initialize DataTable
    let table = new DataTable('#uploadsTable', {
        columns: [
            { className: 'dt-control', orderable: false, data: null, defaultContent: '' },
            { data: 'name' },
            { data: 'position' },
            { data: 'office' },
            { data: 'salary' }
        ],
        order: [[1, 'asc']]
    });
    
    table.on('click', 'tbody td.dt-control', function (e) {
        let tr = e.target.closest('tr');
        let row = table.row(tr);
        if (row.child.isShown()) { row.child.hide(); }
        else { row.child(format(row.data())).show(); }
    });

    // Initialize MultiSelect
    if(typeof MultiSelect !== 'undefined') {
        document.querySelectorAll('[data-multi-select]').forEach(select => {
            new MultiSelect(select);
        });
    }

    // --- SELECTION & ACTION BAR LOGIC ---

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

    // --- ASSIGNMENT SUBMISSION LOGIC ---

    document.getElementById('btnAssign').addEventListener('click', () => {
        const btn = document.getElementById('btnAssign');
        
        // 1. Get Selected Upload IDs
        const selectedUploads = Array.from(document.querySelectorAll('.upload-checkbox:checked'))
            .map(cb => cb.value);
        
        // 2. Get Selected Reviewer IDs
        // FIX: We target the hidden inputs generated by the library instead of the original select ID
        // The library typically generates inputs with name="reviewers[]"
        let reviewerInputs = document.querySelectorAll('input[name="reviewers[]"]');
        
        // Fallback: If the library didn't add brackets, try the exact name
        if (reviewerInputs.length === 0) {
            reviewerInputs = document.querySelectorAll('input[name="reviewers"]');
        }

        const selectedReviewers = Array.from(reviewerInputs).map(input => input.value);

        // 3. Get Selected Rubric ID
        const rubricSelect = document.getElementById('rubrics');
        const selectedRubric = rubricSelect ? rubricSelect.value : null;

        // --- Validation ---
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