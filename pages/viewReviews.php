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

            // B. Fetch Student Department (CHANGED from Document Type)
            $studentDepartment = 'None Assigned';
            if ($student) {
                // Get all flags for the student
                $studentFlags = $usersDao->getUserFlags($student->getId());
                if ($studentFlags) {
                    foreach ($studentFlags as $flag) {
                        // Check if the flag is a Department type
                        if ($flag->getType() === 'Department') {
                            $studentDepartment = $flag->getName();
                            // Requirement: Only show one if multiple exist
                            break; 
                        }
                    }
                }
            }

            // C. Fetch Reviewer Display Name
            $reviewerName = $reviewer->getFullName();
            if (empty(trim($reviewerName)) || $reviewer->getLastLogin() == null) {
                $reviewerName = $reviewer->getEmail();
            }

            // D. Determine Status & Completion Date
            $status = 'temp';
            
            // Use the new function to get the highest status assignment
            $statusAssignment = $evaluationsDao->getHighestStatusAssignmentByEvaluationId($eval->getId());
            if ($statusAssignment) {
                // Fetch the flag name using the ID from the assignment
                $flag = $evaluationsDao->getEvaluationFlag($statusAssignment->getFkEvaluationFlagId());
                if ($flag) {
                    $status = $flag->getName();
                }
            }

            // We still check for a rubric to determine the completion date (if applicable)
            $rubric = $evaluationsDao->getEvaluationRubricFromEvaluationId($eval->getId());
            $dateCompleted = '';

            if ($rubric) {
                $rawDate = $rubric->getDateCreated();
                if ($rawDate) {
                    $dateCompleted = date("m/d/Y g:i A", strtotime($rawDate));
                }
            }

            // E. Add to list
            $flatEvaluations[] = [
                'id' => $eval->getId(),
                'student_name' => $studentName,
                'student_department' => $studentDepartment, // Updated key
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
                            <th scope="col">Student Department</th> <th scope="col">Reviewer Name</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date Completed</th>
                            <th scope="col">Export Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            foreach ($flatEvaluations as $row) {
                                // Status Badge Logic - Updated to check for "Complete" (DB value) vs "Completed"
                                $badgeClass = ($row['status'] == 'Complete') ? 'bg-success' : 'bg-warning';
                                
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['student_name']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['student_department']) . '</td>'; // Updated Data
                                echo '<td>' . htmlspecialchars($row['reviewer_name']) . '</td>';
                                echo '<td class="text-center"><span class="badge ' . $badgeClass . ' p-2">' . $row['status'] . '</span></td>';
                                echo '<td>' . $row['date_completed'] . '</td>';
                                echo '<td> <button data-id = "' . $row['id'] . '"  class = "btn btn-success export-btn"> Export </button> </td>';
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
    async function getEvaluationData(evaluationId) {
        try {
            const body = { 
                evaluationId: evaluationId,
                action: 'getEvaluationData'
            };

            const res = await api.post('/evaluations.php', body);
            return res.message;   // ← this now reaches the caller
        } catch (err) {
            console.log('Error fetching evaluation data:', err);
            throw err;
        }
        
    }
    async function getEvaluationInfo(evaluationId) {
        try {
            const body = { 
                evaluationId: evaluationId,
                action: 'getEvaluationInfo'
            };

            const res = await api.post('/evaluations.php', body);
            return res.message;   // ← this now reaches the caller
        } catch (err) {
            console.log('Error fetching evaluation data:', err);
            throw err;
        }
    }
    document.querySelectorAll('.export-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            
            const excelData = await getEvaluationData(e.target.getAttribute('data-id'));
            const evaluationInfo = await getEvaluationInfo(e.target.getAttribute('data-id'));


            const filename = evaluationInfo["studentOnid"] + '_' + evaluationInfo["rubricName"] + '.xlsx';
            //NOTE; dont know why this works but it does... might want to change in the future
            const response = await fetch('./downloaders/download.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: filename, 
                    data: excelData
                })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            // 3. Convert the response to a Blob (Binary Large Object)
            const blob = await response.blob();

            // 4. Create a temporary 'blob' URL
            const downloadUrl = window.URL.createObjectURL(blob);

            // 5. Create a hidden <a> tag and programmatically click it
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = filename; // The filename for the browser
            document.body.appendChild(link);
            link.click();

            // 6. Cleanup: remove the link and revoke the URL
            link.remove();
            window.URL.revokeObjectURL(downloadUrl);
        });
    });
    
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