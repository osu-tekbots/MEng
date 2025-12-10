<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\EvaluationsDao;
use DataAccess\UploadsDao;

$usersDao = new UsersDao($dbConn, $logger);
$evaluationsDao = new EvaluationsDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);

// 1. Determine Server-Side Filters (Department Only)
$filterDepartment = isset($_GET['department']) && $_GET['department'] != '' ? $_GET['department'] : null;

// 2. Fetch Reviewers
// We use the new logic you added to UsersDao for filtering
$reviewers = [];
if ($filterDepartment) {
    $reviewers = $usersDao->getReviewersByDepartment($filterDepartment);
} else {
    $reviewers = $usersDao->getAllReviewers();
}

// 3. Build Department Map (ID -> Name) for display
$department_flags = $usersDao->getAllDepartmentFlags();
$deptMap = [];
foreach ($department_flags as $dept) {
    $deptMap[$dept->getId()] = $dept->getFlagName();
}

require_once PUBLIC_FILES . '/lib/osu-identities-api.php';
include_once PUBLIC_FILES . '/modules/header.php';
?>

<style>
    /* Child Table Styles */
    .child-table {
        width: 100%;
        margin: 10px 0;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .child-table th { font-size: 0.85em; color: #6c757d; }
    .child-table td { vertical-align: middle; }
    
    table.dataTable tbody td.dt-control {
        text-align: center;
        cursor: pointer;
    }
</style>

<div class="container-fluid">
    <div class="container mt-5 mb-5">

        <div class="row mb-4">
            <div class="col">
                <h2>Reviewer Workload</h2>
                <p class="text-muted">View reviewers and their currently assigned evaluations.</p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Reviewer List</h5>
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
                                    echo '<option value="'. $dept->getId() .'" '.$selected.'>'. $dept->getFlagName() .'</option>';
                                }
                            ?>
                        </select>
                    </div>
                </div>

                <hr class="mt-4 mb-4">

                <table id="reviewersTable" class="table table-striped table-hover table-bordered w-100">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" style="width: 40px;"></th> <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Department</th>
                            <th scope="col" class="text-center">Assigned Evaluations</th>
                            <th scope="col">Last Login</th>
                        </tr>
                    </thead>
                    <tbody id="reviewersTableBody">
                        <?php 
                            foreach ($reviewers as $reviewer) {
                                $reviewerId = $reviewer->getId();
                                
                                // --- DATA FETCHING ---
                                // 1. Get Evaluations for this reviewer
                                $rawEvals = $evaluationsDao->getEvaluationsByReviewerUserId($reviewerId);
                                
                                $evaluationsPayload = [];
                                
                                if ($rawEvals && count($rawEvals) > 0) {
                                    foreach ($rawEvals as $eval) {
                                        // 2. Get Document Name (Flag) using UploadsDao
                                        // We have to lookup the upload first to find its type
                                        $uploadId = $eval->getFkUploadId();
                                        $docType = $uploadsDao->getDocumentType($uploadId);
                                        $docName = $docType ? $docType->getFlagName() : 'Unknown Document';

                                        // 3. Format Date
                                        $dateRaw = $eval->getDateCreated();
                                        $dateAssigned = '-';
                                        if ($dateRaw) {
                                             $dateAssigned = date("m/d/Y g:i A", strtotime($dateRaw));
                                        }

                                        $evaluationsPayload[] = [
                                            'id' => $eval->getId(),
                                            'docName' => $docName,
                                            'dateAssigned' => $dateAssigned
                                        ];
                                    }
                                }

                                $jsonEvaluations = htmlspecialchars(json_encode($evaluationsPayload), ENT_QUOTES, 'UTF-8');
                                
                                // --- ROW FORMATTING ---
                                
                                // Name
                                $lname = method_exists($reviewer, 'getLastName') ? $reviewer->getLastName() : '';
                                $fname = method_exists($reviewer, 'getFirstName') ? $reviewer->getFirstName() : '';
                                $nameDisplay = ($lname && $fname) ? "$lname, $fname" : $reviewer->getFullName();

                                // Department
                                $dId = method_exists($reviewer, 'getDepartmentId') ? $reviewer->getDepartmentId() : null;
                                $deptName = ($dId && isset($deptMap[$dId])) ? $deptMap[$dId] : '<span class="text-muted">-</span>';

                                // Last Login
                                $lastLoginRaw = method_exists($reviewer, 'getLastLogin') ? $reviewer->getLastLogin() : null;
                                $lastLoginDisplay = '<span class="text-muted small">Never</span>';
                                if ($lastLoginRaw instanceof DateTime) {
                                    $lastLoginDisplay = $lastLoginRaw->format("m/d/Y g:i A");
                                } elseif (is_string($lastLoginRaw) && !empty($lastLoginRaw)) {
                                    $lastLoginDisplay = date("m/d/Y g:i A", strtotime($lastLoginRaw));
                                }

                                // Count Badge
                                $evalCount = count($evaluationsPayload);
                                $badgeClass = $evalCount > 0 ? 'bg-primary' : 'bg-secondary';
                                $countDisplay = '<span class="badge ' . $badgeClass . '">' . $evalCount . '</span>';

                                echo '<tr data-evaluations="' . $jsonEvaluations . '">';
                                echo '<td class="dt-control text-center" style="cursor:pointer; color: #007bff;"><i class="fas fa-plus-circle"></i></td>';
                                echo '<td class="font-weight-bold">' . $nameDisplay . '</td>';
                                echo '<td>' . $reviewer->getEmail() . '</td>';
                                echo '<td>' . $deptName . '</td>';
                                echo '<td class="text-center">' . $countDisplay . '</td>';
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

<script>
    function filterDepartments() {
        const deptValue = document.getElementById("departments").value;
        let url = "?"; 
        if (deptValue) {
            url += "department=" + deptValue;
        }
        window.location.href = url;
    }

    /**
     * Generates the Child Row HTML for Evaluations
     */
    function format(rowData, tr) {
        var evals = $(tr).data('evaluations');
        
        if (!evals || evals.length === 0) {
            return '<div class="p-3 text-muted">No evaluations assigned.</div>';
        }

        var html = '<div class="p-3 bg-white border-left border-primary ml-3">';
        html += '<h6 class="text-primary mb-2">Assigned Evaluations</h6>';
        html += '<table class="table table-sm table-bordered mb-0 bg-white">';
        html += '<thead class="thead-light"><tr><th>Document Type</th><th>Date Assigned</th></tr></thead>';
        html += '<tbody>';
        
        evals.forEach(function(e) {
            html += '<tr>';
            html += '<td>' + e.docName + '</td>';
            html += '<td>' + e.dateAssigned + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        html += '</div>';
        return html;
    }

    // Initialize DataTable
    let table = new DataTable('#reviewersTable', {
        columns: [
            { className: 'dt-control', orderable: false, data: null, defaultContent: '' }, // 0
            { data: 'name' },           // 1
            { data: 'email' },          // 2
            { data: 'department' },     // 3
            { data: 'evaluations' },    // 4
            { data: 'last_login' }      // 5
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
            row.child(format(row.data(), tr)).show();
            tr.classList.add('shown');
            if(icon) { icon.classList.remove('fa-plus-circle'); icon.classList.add('fa-minus-circle'); }
        }
    });
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>