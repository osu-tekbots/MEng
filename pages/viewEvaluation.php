<?php
include_once '../bootstrap.php';

use DataAccess\EvaluationsDao;
use DataAccess\UploadsDao;
use DataAccess\UsersDao;
use DataAccess\RubricsDao;

include_once PUBLIC_FILES . '/lib/authorize.php';
allowIf($_SESSION['userIsAdmin']);

$evaluationsDao = new EvaluationsDao($dbConn, $logger);
$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);
$rubricsDao = new RubricsDao($dbConn, $logger);


/* =========================================================================
 *  FETCH EVALUATIONS FOR THE DROPDOWN
 *
 *  By default, only submitted evaluations (arrangement = 3) are shown.
 *  To show all evaluations instead, comment out the status filter line
 *  and uncomment the "all evaluations" line below it.
 * ========================================================================= */

// Only submitted evaluations (arrangement 3 = Submitted)
$dropdownEvaluations = $evaluationsDao->getEvaluationsByStatusArrangement(3);
// All evaluations (uncomment below and comment the line above to show every evaluation)
// $dropdownEvaluations = $evaluationsDao->getEvaluationsByStatusArrangement(1) + $evaluationsDao->getEvaluationsByStatusArrangement(2) + $evaluationsDao->getEvaluationsByStatusArrangement(3);

if (!$dropdownEvaluations) $dropdownEvaluations = [];

// Build display labels for the dropdown: "Student — Rubric (Reviewer)"
$dropdownItems = [];
foreach ($dropdownEvaluations as $eval) {
    $student = $usersDao->getUser($eval->getFkStudentId());
    $reviewer = $usersDao->getUser($eval->getFkReviewerId());
    $rubric = $evaluationsDao->getRubricFromEvaluationId($eval->getId());

    $studentName = $student ? $student->getFullName() : 'Unknown Student';
    $reviewerName = $reviewer ? $reviewer->getFullName() : 'Unknown Reviewer';
    $rubricName = $rubric ? $rubric->getName() : 'Unknown Rubric';

    $dropdownItems[] = [
        'id' => $eval->getId(),
        'label' => $studentName . ' - ' . $rubricName . ' (' . $reviewerName . ')'
    ];
}


/* =========================================================================
 *  LOAD SELECTED EVALUATION (GET parameter)
 *
 *  An evaluation can be loaded via the dropdown or via a direct URL with
 *  ?evaluationId=<id> (e.g. from the View Reviews page). Direct links
 *  work for any evaluation status, not just submitted.
 * ========================================================================= */

$selectedEvaluation = null;
$selectedTemplate = null;
$selectedUpload = null;
$highestStatusFlag = null;
$studentName = '';
$reviewerName = '';
$rubricName = '';

if (isset($_GET['evaluationId']) && !empty($_GET['evaluationId'])) {
    $selectedEvaluation = $evaluationsDao->getEvaluationById($_GET['evaluationId']);

    if ($selectedEvaluation) {
        // Load rubric template with items
        $selectedTemplate = $evaluationsDao->getRubricFromEvaluationId($selectedEvaluation->getId());

        // Load upload info
        $selectedUpload = $uploadsDao->getUpload($selectedEvaluation->getFkUploadId());

        // Load status flag
        $highestStatusFlag = $evaluationsDao->getHighestStatusFlagByEvaluationId($selectedEvaluation->getId());

        // Load student/reviewer names for display
        $student = $usersDao->getUser($selectedEvaluation->getFkStudentId());
        $reviewer = $usersDao->getUser($selectedEvaluation->getFkReviewerId());
        $studentName = $student ? $student->getFullName() : 'Unknown Student';
        $reviewerName = $reviewer ? $reviewer->getFullName() : 'Unknown Reviewer';
        $rubricName = $selectedTemplate ? $selectedTemplate->getName() : 'Unknown Rubric';
    }
}

include_once PUBLIC_FILES . '/modules/header.php';
?>


<!-- =====================================================================
     CSS — read-only rubric-button styles (mirrors evaluateRubrics.php)
     ===================================================================== -->

<style>
.rubric-btn {
    background-color: #f8f9fa;
    color: #495057;
    border: 2px solid #e9ecef;
    font-size: 1.05rem;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    cursor: default;
    pointer-events: none;
}
/* Selected option highlighted green, matching evaluateRubrics.php */
.btn-check:checked + .rubric-btn {
    background-color: #198754 !important;
    color: white !important;
    border-color: #198754 !important;
    box-shadow: inset 0 3px 5px rgba(0,0,0,0.125);
}
/* Read-only comment display box */
.readonly-comment {
    background-color: #e9ecef;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 0.75rem;
    min-height: 60px;
    overflow-y: auto;
    color: #495057;
}
</style>


<!-- =====================================================================
     PAGE CONTENT (Should be pulled from evaluateRubrics helper functions todo to avoid redundancy)
     ===================================================================== -->

<div class="container mt-4">

    <h2>View Evaluation</h2>
    <p class="text-muted">View a submitted evaluation in read-only mode.</p>

    <!-- =================================================================
         Evaluation Dropdown Selector (mirrors createRubric.php pattern)
         ================================================================= -->

    <form method="GET" class="mb-4">
        <label for="evaluationId" class="form-label">Select Evaluation to View:</label>
        <select name="evaluationId" id="evaluationId" class="form-select" onchange="this.form.submit()">
            <option value="">-- Select an Evaluation --</option>
            <?php foreach ($dropdownItems as $item):
                $selected = (isset($_GET['evaluationId']) && $_GET['evaluationId'] == $item['id']) ? 'selected' : '';
            ?>
                <option value="<?= htmlspecialchars($item['id']) ?>" <?= $selected ?>>
                    <?= htmlspecialchars($item['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($selectedEvaluation && $selectedTemplate): ?>

        <!-- =============================================================
             Evaluation Info Header
             ============================================================= -->

        <?php
        // Status badge rendering (same logic as evaluateRubrics.php renderStatusBanner)
        if ($highestStatusFlag):
            $arr = $highestStatusFlag->getArrangement();
            $isSubmitted = $arr >= 3;
            $alertClass = $isSubmitted ? 'alert-success' : 'alert-info';
            $iconClass = $isSubmitted ? 'bi-check-circle-fill' : 'bi-info-circle-fill';
            $verb = $arr == 1 ? 'is currently' : ($arr == 2 ? 'was saved as a' : 'was');
        ?>
            <div class="alert <?= $alertClass ?> d-flex align-items-center mt-3 mb-2" role="alert">
                <i class="bi <?= $iconClass ?> me-2"></i>
                <div>
                    This evaluation <?= $verb ?>
                    <strong><?= htmlspecialchars($highestStatusFlag->getName()) ?></strong>
                    <?php if ($highestStatusFlag->getDateCreated() && $arr != 1): ?>
                        on <?= htmlspecialchars(date('F j, Y \a\t g:i A', strtotime($highestStatusFlag->getDateCreated()))) ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="mb-3 text-end">
            <button onclick="deleteEvaluation('<?= htmlspecialchars($selectedEvaluation->getId()) ?>')" class="btn btn-danger">
                <i class="bi bi-trash-fill me-1"></i> Delete Evaluation
            </button>
        </div>

        <!-- Evaluation metadata: reviewer, student, rubric -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Student:</strong> <?= htmlspecialchars($studentName) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Reviewer:</strong> <?= htmlspecialchars($reviewerName) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Rubric:</strong> <?= htmlspecialchars($rubricName) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- =============================================================
             Read-Only Rubric Items (mirrors evaluateRubrics.php layout, 
             but all inputs disabled / displayed as static content)
             ============================================================= -->

        <?php foreach ($selectedTemplate->items as $item):
            $options = $rubricsDao->getRubricItemOptionsByItemId($item->getId());
            $optionCount = $options ? count($options) : 0;

            // Look up the reviewer's saved answer for this rubric item
            $existingItem = $evaluationsDao->getEvaluationRubricItemByEvalAndRubricItem(
                $selectedEvaluation->getId(), $item->getId()
            );
            $currentOptionId = $existingItem && $existingItem->getRubricItemOption()
                ? $existingItem->getRubricItemOption()->getId() : null;
            $currentComment = $existingItem ? $existingItem->getComments() : '';
            $rawId = $item->getId();
        ?>

            <div class="row mb-5">
                <div class="card col-12 p-0 border-primary">

                    <!-- Item header -->
                    <div class="card-header bg-primary text-white mb-1">
                        <strong><?= htmlspecialchars($item->getName()) ?></strong>
                    </div>

                    <div class="card-body">

                        <!-- Item description -->
                        <div class="mb-4">
                            <?= $item->getDescription() ?>
                        </div>

                        <?php
                        /* ---------------------------------------------------------
                         *  Options layout — more than 2: full-width flex row
                         * --------------------------------------------------------- */
                        if ($optionCount > 2): ?>
                            <div class="d-flex w-100 gap-2 mb-4 flex-wrap">
                                <?php foreach ($options as $opt): ?>
                                    <div class="flex-fill" style="flex-basis: 0; min-width: 150px;">
                                        <input class="btn-check" type="radio"
                                               name="answers[<?= htmlspecialchars($rawId) ?>]"
                                               id="<?= htmlspecialchars($rawId . '_' . $opt->getId()) ?>"
                                               value="<?= htmlspecialchars($opt->getId()) ?>"
                                               <?= ($currentOptionId == $opt->getId() ? 'checked' : '') ?>
                                               disabled>
                                        <label class="btn rubric-btn w-100 h-100 d-flex align-items-center justify-content-center text-center p-3"
                                               for="<?= htmlspecialchars($rawId . '_' . $opt->getId()) ?>">
                                            <?= htmlspecialchars($opt->getTitle()) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <?php
                            /* ---------------------------------------------------------
                             *  Options layout — 1 or 2: narrower column with comment beside
                             * --------------------------------------------------------- */
                            if ($optionCount > 0 && $optionCount <= 2): ?>
                                <div class="col-md-5 d-flex flex-column gap-3 mb-3 mb-md-0">
                                    <?php foreach ($options as $opt): ?>
                                        <div class="flex-fill">
                                            <input class="btn-check" type="radio"
                                                   name="answers[<?= htmlspecialchars($rawId) ?>]"
                                                   id="<?= htmlspecialchars($rawId . '_' . $opt->getId()) ?>"
                                                   value="<?= htmlspecialchars($opt->getId()) ?>"
                                                   <?= ($currentOptionId == $opt->getId() ? 'checked' : '') ?>
                                                   disabled>
                                            <label class="btn rubric-btn w-100 h-100 d-flex align-items-center justify-content-center text-center p-3"
                                                   for="<?= htmlspecialchars($rawId . '_' . $opt->getId()) ?>">
                                                <?= htmlspecialchars($opt->getTitle()) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="col-md-7">
                                    <label class="mb-2">
                                        <strong>Comments:</strong>
                                    </label>
                                    <div class="readonly-comment"><?= !empty($currentComment) ? $currentComment : '<em class="text-muted">No comments provided.</em>' ?></div>
                                </div>
                            <?php else: ?>
                                <!-- Full-width comment box (used when there are 0 or >2 options) -->
                                <div class="col-12">
                                    <label class="mb-2">
                                        <strong>Comments:</strong>
                                    </label>
                                    <div class="readonly-comment"><?= !empty($currentComment) ? $currentComment : '<em class="text-muted">No comments provided.</em>' ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>

        <?php endforeach; ?>

    <?php elseif (isset($_GET['evaluationId']) && !empty($_GET['evaluationId'])): ?>
        <!-- Evaluation ID was provided but couldn't be loaded -->
        <div class="alert alert-danger d-flex align-items-center mt-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>Could not load evaluation. The evaluation ID may be invalid.</div>
        </div>
    <?php else: ?>
        <!-- No evaluation selected -->
        <div class="alert alert-warning d-flex align-items-center mt-3" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <div>Select an evaluation from the dropdown above, or navigate here from the <a href="./viewReviews.php">View Reviews</a> page.</div>
        </div>
    <?php endif; ?>

    <div style="height: 150px;"></div>
</div>

<script>
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
                    setTimeout(() => window.location.replace("viewReviews.php"), 1000);
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
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
