<?php
//Main PHP setup code
include_once '../bootstrap.php';

use DataAccess\EvaluationsDao;
use DataAccess\UploadsDao;
use DataAccess\UsersDao;

use Model\Evaluation;
use Model\EvaluationRubric;
use Model\EvaluationRubricItem;


$css = array(
	'https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css',
	'https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css',
	"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
);

$js = array(
	'https://code.jquery.com/jquery-3.5.1.min.js',
	'https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js',
	'https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.js',
	"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
);

$evaluationsDao = new EvaluationsDao($dbConn, $logger);//Need entire evaluations object to extract necessary info for the rest
$usersDao = new UsersDao($dbConn, $logger); //Need first name, last name
$uploadsDao = new UploadsDao($dbConn, $logger);//Need upload name and upload file path
?>

<?php
//Table Generation code


//Table structure:
//  (studentName + evaluationRubricTemplateName) as a link to evaluateRubrics evaluationId = id, link to upload (found using uploadID in evaluation object),
// reviewDueDate (leave blank for now), date created (part of evaluation object),
// status (not started, in progress, completed) (need to create function to get status of evaluation rubric),
//
//Getting student name
$tableHTML = '';
$tableHTML.="
			<table class='table ' id='reviewerAssignmentsTable' style='width: 100%; max-width: 100%; table-layout:fixed;'>
					<thead>
						<tr>
							<th>Evaluation</th>
							<th>Upload Link</th>
                            <th>Student</th>
                            <th>Date Created</th>
                            <th>Status</th>
						</tr>
					</thead>
					<tbody>";


$evaluations = $evaluationsDao->getEvaluationsByReviewerUserId($_SESSION['userID']);
foreach($evaluations as $evaluation) {
    $student = $usersDao->getUser($evaluation->getFkStudentId());
    $upload = $uploadsDao->getUpload($evaluation->getFkUploadId());
    $evaluationRubric = $evaluationsDao -> getEvaluationRubricFromEvaluationId($evaluation -> getId());

    $tableHTML.= getTableRow($evaluation, $student, $upload, $evaluationRubric);
    
}
$tableHTML.= "</tbody>
		</table>
    ";  

include_once PUBLIC_FILES . '/modules/header.php';
?>

<?php 
//PHP Helper Functions
function getTableRow($evaluation, $student, $upload, $evaluationRubric) {
    $studentName = $student->getFirstName() . ' ' . $student-> getLastName();
    $evaluationLink = "<a href='evaluateRubrics.php?evaluationId=" . $evaluation->getId() . "'>".(
        ($evaluationRubric != false) ? $evaluationRubric -> getName() : "Not set")."</a>";
    $uploadLink = "<a href='./uploads" . $upload->getFilePath() .$upload->getFileName()."'>" . $upload->getFileName() . "</a>";
    $dateCreated = $evaluation->getDateCreated();

    $flag = $evaluation -> getHighestStatusFlag();
    $status = ($flag != null ? $flag -> getName() : "No Status"); 
    return "
        <tr>
            <td>$evaluationLink</td>
            <td>$uploadLink</td>
            <td>$studentName</td>
            <td>$dateCreated</td>
            <td>$status</td>
        </tr>
    ";
}
?>

<!-- Page HTML -->
<div>
    <div class="container mt-4">
        <h2>Assigned Reviews</h2>
        <?php echo $tableHTML; ?>
    </div>
</div>


<script> 
$("#reviewerAssignmentsTable").DataTable({
    //'dom': 'Bft',
    'scrollX': false,
    'paging':   false,
    'ordering': true,
    'info':     false,
    'order': [[ 3, "desc" ]],
    //Default order is by date created descending
    "columns": [
			null, //null means column can be ordered; default = true, null = no change
			{ "orderable": false },
			{ "orderable": false },
            null, 
            null
	    ]
});
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
