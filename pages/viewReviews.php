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

// 2. Fetch Reviewers (We use this to find evaluations since there is no getAllEvaluations)
$reviewers = [];
if ($filterDepartment) {
    $reviewers = $usersDao->getReviewersByDepartment($filterDepartment);
} else {
    $reviewers = $usersDao->getAllReviewers();
}

// 3. Build the Flat List of Evaluations
$flatEvaluations = [];

foreach ($reviewers as $reviewer) {
    // Fetch evaluations assigned to this reviewer
    $reviewerEvals = $evaluationsDao->getEvaluationsByReviewerUserId($reviewer->getId());
    
    if ($reviewerEvals) {
        foreach ($reviewerEvals as $eval) {
            
            // A. Fetch Student Info
            $student = $usersDao->getUser($eval->getFkStudentId());
            $studentName = $student ? $student->getFullName() : 'Unknown Student';

            // B. Fetch Document Type
            $uploadId = $eval->getFkUploadId();
            $docTypeObj = $uploadsDao->getDocumentType($uploadId);
            $docType = $docTypeObj ? $docTypeObj->getName() : 'Unknown Document';

            // C. Fetch Reviewer Display Name
            // Logic: Use Name, but if they never logged in (no last login?), show email? 
            // Simplified: We usually just show "Name (Email)" to be safe, or just Name.
            // Based on prompt: "Reviewer Name (email if reviewer never logged in)"
            $reviewerName = $reviewer->getFullName();
            if (empty(trim($reviewerName)) || $reviewer->getLastLogin() == null) {
                $reviewerName = $reviewer->getEmail();
            }

            // D. Determine Status & Completion Date
            // We check if a rubric exists for this evaluation.
            $rubric = $evaluationsDao->getEvaluationRubricFromEvaluationId($eval->getId());
            
            $status = 'Pending';
            $dateCompleted = '';

            if ($rubric) {
                $status = 'Completed'; // Assuming existence of rubric means it was submitted
                $rawDate = $rubric->getDateCreated();
                if ($rawDate) {
                    $dateCompleted = date("m/d/Y g:i A", strtotime($rawDate));
                }
            }

            // E. Add to list
            $flatEvaluations[] = [
                'id' => $eval->getId(),
                'student_name' => $studentName,
                'document_type' => $docType,
                'reviewer_name' => $reviewerName,
                'status' => $status,
                'date_completed' => $dateCompleted
            ];
        }
    }
}

// 4. Build Department Map for Dropdown
$department_flags = $usersDao->getAllDepartmentFlags();

require_once PUBLIC_FILES . '/lib/osu-identities-api.php';
include_once PUBLIC_FILES . '/modules/header.php';
?>

<div class="container-fluid">
    <div class="container mt-5 mb-5">

        <div class="row mb-4">
            <div class="col">
                <h2>View Evaluations</h2>
                <p class="text-muted">View the status of all assigned evaluations.</p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Evaluations List</h5>
            </div>
            <div class="card-body">
                
                <div class="form-row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase font-weight-bold">Filter by Reviewer Department</label>
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
                </div>

                <hr class="mt-4 mb-4">

                <table id="evaluationsTable" class="table table-striped table-hover table-bordered w-100">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Student Name</th>
                            <th scope="col">Document Type</th>
                            <th scope="col">Reviewer Name</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            foreach ($flatEvaluations as $row) {
                                // Status Badge Logic
                                $badgeClass = ($row['status'] == 'Completed') ? 'bg-success' : 'bg-warning';
                                
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['student_name']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['document_type']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['reviewer_name']) . '</td>';
                                echo '<td class="text-center"><span class="badge ' . $badgeClass . ' p-2">' . $row['status'] . '</span></td>';
                                echo '<td>' . $row['date_completed'] . '</td>';
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

    // Initialize DataTable
    $(document).ready(function() {
        $('#evaluationsTable').DataTable({
            order: [[0, 'asc']], // Order by Student Name by default
            language: {
                emptyTable: "No evaluations found matching the criteria."
            }
        });
    });
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>