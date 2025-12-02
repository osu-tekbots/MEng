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
<div class="container-fluid">
    <div class="container mt-4">
        <div class="row">
            <div class="col">
                <h2>Uploads</h2>
            </div>
            <div class="col">
                <select id="departments" name="departments" class="form-control">
                    <option value="" selected disabled>All Departments</option>
                    <?php 
                        foreach ($department_flags as $department_flag) {
                            echo '<option value="'. $department_flag->getId() .'">'. $department_flag->getFlagName() .'</option>';
                        }
                    ?>
                </select>
            </div>
            <div class="col">
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
        <br>
        <br>
        <div class="row">
            <div class="col">
                <table id="uploadsTable" class="table table-striped table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                        <th scope="col">Student Name</th>
                        <th scope="col">Department/Major</th>
                        <th scope="col">Date Uploaded</th>
                        <th scope="col">Select</th>
                        </tr>
                    </thead>
                    <tbody id="uploadsTableBody">
                        <?php 
                            foreach ($uploads as $upload) {
                                echo '<tr>';
                                $uploader = $usersDao->getUser($upload->getFkUserId());
                                echo '<td>' . $uploader->getFullName() . '</td>';
                                $documentTypeFlag = $uploadsDao->getDocumentType($upload->getId());
                                echo '<td>' . $documentTypeFlag->getFlagName() . '</td>';
                                echo '<td>' . $upload->getDateUploaded() . '</td>';
                                echo '<td><input class="form-check-input upload-checkbox" type="checkbox" value="'.$upload->getId().'"></td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <label for="reviewers">Assign Reviewers (Select one or more)</label>
                <select id="reviewers" name="reviewers" data-placeholder="Select Reviewers" multiple data-multi-select class="form-control">
                    <?php 
                        $reviewers = $usersDao->getAllReviewers();
                        foreach ($reviewers as $reviewer) {
                            echo '<option value="'. $reviewer->getId() .'">'. $reviewer->getFullName() .'</option>';
                        }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="rubrics">Assign Rubric (Select one)</label>
                <select id="rubrics" name="rubrics" class="form-control">
                    <option value="" selected disabled>Select a Rubric...</option>
                    <?php 
                        $rubrics = $rubricsDao->getAllRubricTemplates();
                        foreach ($rubrics as $rubric) {
                            echo '<option value="'. $rubric->getId() .'">'. $rubric->getName() .'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>

        <div class="row mt-4 mb-5">
            <div class="col">
                <button id="btnAssign" class="btn btn-primary btn-lg">Create Evaluations</button>
            </div>
        </div>
    </div>
</div>

<script>
    function onAssignmentChange() {
        const assigned = document.getElementById("assigned");
        window.location.replace("assignReviewers.php?onlyUnassigned=" + assigned.value);
    }

    new DataTable('#uploadsTable');

    if(typeof MultiSelect !== 'undefined') {
        document.querySelectorAll('[data-multi-select]').forEach(select => {
            new MultiSelect(select);
        });
    }

    document.getElementById('btnAssign').addEventListener('click', () => {
        const btn = document.getElementById('btnAssign');
        const selectedUploads = Array.from(document.querySelectorAll('.upload-checkbox:checked'))
            .map(cb => cb.value);
        const hiddenInputs = document.querySelectorAll('input[name="reviewers[]"]');
        const selectedReviewers = Array.from(hiddenInputs).map(input => input.value);
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