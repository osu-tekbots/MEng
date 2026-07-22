<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\EvaluationsDao;
use DataAccess\UploadsDao;


$usersDao = new UsersDao($dbConn, $logger);
$evaluationsDao = new EvaluationsDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);

// 1. Determine Server-Side Filters (Program Only)
$filterProgram = isset($_GET['program']) && $_GET['program'] != '' ? $_GET['program'] : null;

// 2. Fetch Reviewers
$reviewers = [];
if ($filterProgram) {
    $reviewers = $usersDao->getReviewersByDepartment($filterProgram);
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

            // B. Fetch Student Program (CHANGED from Document Type)
            $studentProgram = 'None Assigned';
            if ($student) {
                // Get all flags for the student
                $studentFlags = $usersDao->getUserFlags($student->getId());
                if ($studentFlags) {
                    foreach ($studentFlags as $flag) {
                        // Check if the flag is a Program type
                        if ($flag->getType() === 'Program') {
                            $studentProgram = $flag->getName();
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
            
            // Use the function to get the highest status flag directly
            $statusFlag = $evaluationsDao->getHighestStatusFlagByEvaluationId($eval->getId());
            if ($statusFlag) {
                $status = $statusFlag->getName();
            }

            // Use the status flag's date_created for the completion date
            $dateCompleted = '';
            if ($statusFlag) {
                $rawDate = $statusFlag->getDateCreated();
                if ($rawDate) {
                    $dateCompleted = date("m/d/Y g:i A", strtotime($rawDate));
                }
            }

            // F. Get Rubric Name
            $rubricName = 'Unknown Rubric';
            $rubric = $evaluationsDao->getRubricFromEvaluationId($eval->getId());
            if ($rubric) {
                $rubricName = $rubric->getName();
            }

            // E. Add to list
            $flatEvaluations[] = [
                'id' => $eval->getId(),
                'student_name' => $studentName,
                'student_program' => $studentProgram, // Updated key
                'reviewer_name' => $reviewerName,
                'status' => $status,
                'date_completed' => $dateCompleted,
                'rubric_name' => $rubricName
            ];
        }
    }
}

// 4. Build Program Map for Dropdown
$program_flags = $usersDao->getAllDepartmentFlags();

// 5. Fetch rubrics used in evaluations (for bulk export dropdown)
$rubricsUsedInEvals = $evaluationsDao->getRubricsUsedInEvaluations();

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
                    <div class="col-md-4">
                        <label class="text-muted small text-uppercase font-weight-bold">Filter by Reviewer Program</label>
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
                    <div class="col-md-8">
                        <label class="text-muted small text-uppercase font-weight-bold">Bulk Export</label>
                        <div class="d-flex align-items-end">
                            <select id="bulkRubric" class="form-control mr-2">
                                <option value="">Select Rubric</option>
                                <?php foreach ($rubricsUsedInEvals as $r): ?>
                                    <option value="<?= htmlspecialchars($r['id']) ?>"><?= htmlspecialchars($r['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select id="bulkTimeRange" class="form-control mr-2">
                                <option value="all">All Time</option>
                                <option value="1week">1 Week</option>
                                <option value="1month">1 Month</option>
                                <option value="3months">3 Months</option>
                                <option value="6months">6 Months</option>
                                <option value="1year">1 Year</option>
                            </select>
                            <button id="bulkExportBtn" class="btn btn-primary text-nowrap">Export All</button>
                        </div>
                    </div>
                </div>

                <hr class="mt-4 mb-4">

                <table id="evaluationsTable" class="table table-striped table-hover table-bordered w-100">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Student Name</th>
                            <th scope="col">Student Program</th> <th scope="col">Reviewer Name</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date Updated</th>
                            <th scope="col">Rubric Name</th>
                            <th scope="col">Export Data</th>
                            <th scope="col">View</th>
                            <th scope="col">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            foreach ($flatEvaluations as $row) {
                                // Status Badge Logic
                                $badgeClass = '';
                                $badgeStyle = '';
                                
                                $statusName = trim($row['status']);
                                if ($statusName === 'Complete' || $statusName === 'Submitted') {
                                    $badgeStyle = 'background-color: #05c488ff; color: white;';
                                } else if ($statusName === 'Draft') {
                                    $badgeClass = 'bg-warning text-dark';
                                } else {
                                    // Default (Pending)
                                    $badgeStyle = 'background-color: #e5e7eb; color: #374151;'; // Lighter gray
                                }
                                
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['student_name']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['student_program']) . '</td>'; // Updated Data
                                echo '<td>' . htmlspecialchars($row['reviewer_name']) . '</td>';
                                echo '<td class="text-center"><span class="badge ' . $badgeClass . ' p-2" style="' . $badgeStyle . '">' . $row['status'] . '</span></td>';
                                echo '<td>' . $row['date_completed'] . '</td>';
                                echo '<td>' . htmlspecialchars($row['rubric_name']) . '</td>';
                                echo '<td> <button data-id = "' . $row['id'] . '"  class = "btn btn-success export-btn"> Export </button> </td>';
                                echo '<td> <a href="evaluateRubrics.php?evaluationId=' . urlencode($row['id']) . '&viewMode=1" class="btn btn-primary btn-sm">View</a> </td>';
                                echo '<td> <button onclick="deleteEvaluation(\'' . htmlspecialchars($row['id']) . '\')" class="btn btn-danger btn-sm"><i class="bi bi-trash-fill"></i></button> </td>';
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

    // --- Bulk Export (independent from individual export) ---
    document.getElementById('bulkExportBtn').addEventListener('click', async () => {
        const rubricId = document.getElementById('bulkRubric').value;
        const timeRange = document.getElementById('bulkTimeRange').value;

        if (!rubricId) {
            alert('Please select a rubric.');
            return;
        }

        const btn = document.getElementById('bulkExportBtn');
        btn.disabled = true;
        btn.textContent = 'Exporting...';

        try {
            // 1. Fetch bulk data from API
            const res = await api.post('/evaluations.php', {
                action: 'getBulkExportData',
                rubricId: rubricId,
                timeRange: timeRange
            });

            const data = res.message;

            if (!data || data.length === 0) {
                alert('No evaluations found for the selected rubric and time range.');
                return;
            }

            // 2. Get rubric name for filename
            const rubricName = document.getElementById('bulkRubric').selectedOptions[0].text;
            const filename = rubricName.replace(/[^a-zA-Z0-9]/g, '_') + '_bulk_export.xlsx';

            // 3. Send to download.php with type=bulk
            const response = await fetch('./downloaders/download.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: filename,
                    data: data,
                    type: 'bulk'
                })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            // 4. Download the file
            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(downloadUrl);

        } catch (err) {
            console.error('Bulk export error:', err);
            alert('An error occurred during bulk export. Please try again.');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Export All';
        }
    });
    
    function filterPrograms() {
        const progValue = document.getElementById("programs").value;
        let url = "?"; 
        if (progValue) {
            url += "program=" + progValue;
        }
        window.location.href = window.location.pathname + url;
    }

    // Handle evaluation deletion
    function deleteEvaluation(evalId) {
        console.log("Delete button clicked for evaluation ID:", evalId);
        
        if (confirm("Are you sure you want to delete this evaluation? This action cannot be undone.")) {
            console.log("Deletion confirmed by user. Sending POST request to api/evaluations.php...");
            
            let body = {
                action: 'deleteEvaluation',
                evaluationId: evalId
            };

            api.post('/evaluations.php', body)
                .then(res => {
                    console.log("Delete request succeeded. Response:", res);
                    if (typeof snackbar === 'function') snackbar(res.message, 'success');
                    else alert(res.message || "Evaluation deleted successfully.");
                    setTimeout(() => window.location.reload(), 1000);
                })
                .catch(err => {
                    console.error("Delete request failed. Error:", err);
                    if (typeof snackbar === 'function') snackbar(err.message, 'error');
                    else alert(err.message || "Failed to delete evaluation.");
                });
        } else {
            console.log("Deletion cancelled by user.");
        }
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