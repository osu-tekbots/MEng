<?php
include_once '../bootstrap.php';
use DataAccess\EvaluationsDao;
use DataAccess\UploadsDao;
use DataAccess\UsersDao;

use Model\Evaluation;
use Model\EvaluationRubric;
use Model\EvaluationRubricItem;

$js = array(
   "https://cdn.ckeditor.com/ckeditor5/31.1.0/classic/ckeditor.js"
);


$evaluationsDao = new EvaluationsDao($dbConn, $logger);
$usersDao = new UsersDao($dbConn, $logger); //Need first name, last name
$uploadsDao = new UploadsDao($dbConn, $logger);//Need upload name and upload file path

//Evaluations assigned to reviewer
$evaluations = $evaluationsDao->getEvaluationsByReviewerUserId($_SESSION['userID']);


// Handle form submission for saving rubric answers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prefer loading the selected evaluation rubric so we process every item
    $evaluationId = $_POST['evaluationId'] ?? '';
    $answers = $_POST['answers'] ?? [];
    $comments = $_POST['comments'] ?? [];

    $logger -> info(json_encode($_POST));
    $updated = 0;

    if (!empty($evaluationId)) {

        //Deal with setting the flag, should be pending when first created
        if(isset($_POST['action'])) {
            if($_POST['action'] === 'submit'){
                $evaluationsDao -> setStatusFlagForEvaluation($evaluationId, 3);
                //Magic number 3 corresponds to "Submitted" status flag
            } else if($_POST['action'] === 'save'){
                $evaluationsDao -> setStatusFlagForEvaluation($evaluationId, 2);
                //Magic number 2 corresponds to "Draft" status flag
            }
        }

        $postedTemplate = $evaluationsDao->getEvaluationRubricFromEvaluationId($evaluationId);
        if ($postedTemplate && !empty($postedTemplate->items)) {
            foreach ($postedTemplate->items as $item) {
                $itemId = $item->getId();

                // Lookup posted values by raw id key. Use null if absent (eg. unchecked radio)
                $value = array_key_exists($itemId, $answers) ? $answers[$itemId] : null;
                $comment = array_key_exists($itemId, $comments) ? $comments[$itemId] : null;

                $evalItem = new EvaluationRubricItem($itemId);
                $evalItem->setValue($value)
                         ->setComments($comment);

                if ($evaluationsDao->setEvaluationRubricItem($evalItem)) {
                    $updated++;
                }
            }
        } else {
            // Fallback: process any posted answers (legacy)
            foreach ($answers as $itemId => $value) {
                $evalItem = new EvaluationRubricItem($itemId);
                $evalItem->setValue($value)
                         ->setComments($comments[$itemId] ?? null);

                if ($evaluationsDao->setEvaluationRubricItem($evalItem)) {
                    $updated++;
                }
            }
        }
    }

    // Redirect back to the same evaluation to refresh values
    header('Location: ?evaluationId=' . urlencode($evaluationId));
    exit;
}

$logger -> info('User ID ' . $_SESSION['userID'] . ' has ' . count($evaluations) . ' evaluations.');

$selectedTemplate = null;
$selectedUpload = '';
if (isset($_GET['evaluationId'])) {
    
    $selectedUpload = $uploadsDao -> getUpload($evaluationsDao -> getEvaluationById($_GET['evaluationId']) -> getFkUploadId());

    $selectedTemplate = $evaluationsDao->getEvaluationRubricFromEvaluationId($_GET['evaluationId']);

    if ($logger && $selectedTemplate) {
        //$logger->info('Selected Evaluation ID: ' . $selectedEvaluation->getId());
        $logger->info('Selected Evaluation ID: ' . $_GET['evaluationId']);
        $logger->info('Selected upload: ' . $selectedUpload -> getFilePath().$selectedUpload->getFileName());

    }
}

include_once PUBLIC_FILES . '/modules/header.php';
?>

<?php
function renderAnswerInput($item) {
    $type = $item->getAnswerType();
    $rawId = $item->getId();
    $id   = htmlspecialchars($rawId);
    $name = 'answers[' . $rawId . ']';
    switch ($type) {

        /* ---------------------------------------------------------
           TEXT INPUT
        --------------------------------------------------------- */
        case "text": ?>
            <textarea
                class="form-control item-answer"
                id="<?= $id ?>_answer"
                name="<?= htmlspecialchars($name) ?>"
                rows="3"
            ><?= htmlspecialchars($item->getValue() ?? '') ?></textarea>
        <?php break;

        /* ---------------------------------------------------------
           BOOLEAN (TRUE / FALSE)
        --------------------------------------------------------- */
        case "boolean": ?>
            <?php $val = $item->getValue(); ?>
            <div class="form-check form-check-inline">
                  <input class="form-check-input"
                      type="radio"
                      name="<?= htmlspecialchars($name) ?>"
                       id="<?= $id ?>_true"
                       value="true" <?= ($val === 'true' ? 'checked' : '') ?>>
                <label class="form-check-label" for="<?= $id ?>_true">True</label>
            </div>

            <div class="form-check form-check-inline">
                  <input class="form-check-input"
                      type="radio"
                      name="<?= htmlspecialchars($name) ?>"
                       id="<?= $id ?>_false"
                       value="false" <?= ($val === 'false' ? 'checked' : '') ?>>
                <label class="form-check-label" for="<?= $id ?>_false">False</label>
            </div>
        <?php break;

        /* ---------------------------------------------------------
           NUMBER, 1-5 qualitative scale
        --------------------------------------------------------- */
        case "number": ?>
            <?php $val = $item->getValue(); 
            $options = [
                0 => "N/A",
                1 => "Strongly disagree",
                2 => "Disagree",
                3 => "Neutral",
                4 => "Agree",
                5 => "Strongly agree"
            ];?>
            <div class="likert-scale d-flex gap-2 flex-wrap">
                <?php foreach ($options as $value => $label): ?>
                    <div class="form-check">
                        <input  class="form-check-input"
                            type="radio"
                            name="<?= htmlspecialchars($name) ?>"
                            id="<?= $id ?>_<?= $value ?>"
                            value="<?= $value ?>" 
                            <?= ($val == $value ? 'checked' : '') ?>>

                        <label class="form-check-label" for="<?= $id ?>_<?= $value ?>">
                            <?= htmlspecialchars($label) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php break;

        /* ---------------------------------------------------------
           DEFAULT → TEXTAREA
        --------------------------------------------------------- */
        default: ?>
            <textarea
                class="form-control item-answer"
                id="<?= $id ?>_answer"
                name="<?= htmlspecialchars($name) ?>"
                rows="3"
            ><?= htmlspecialchars($item->getValue() ?? '') ?></textarea>
        <?php
    }
}
?>


<div class="container mt-4">
    <h2>Review Document</h2>
    <!-- Selecting Evaluation-->
    <div class="row">
        <form method="GET" class="mb-4">

            <label for="evaluationId" class="form-label">Select Evaluation to answer:</label>
            <select name="evaluationId" id="evaluationId" class="form-select" onchange="this.form.submit()">
                <option value="">Select Evaluation</option>
                <!-- Evaluations are actually evaluation rubric items but are being stored in the query param by their fk ids (not their own ids) so its still per evaluation, not rubric -->
                <?php foreach ($evaluations as $evaluation): ?>
                    <?php 
                        $student = $usersDao->getUser($evaluation->getFkStudentId());
                        $upload = $uploadsDao->getUpload($evaluation->getFkUploadId());
                        $evaluationRubric = $evaluationsDao -> getEvaluationRubricFromEvaluationId($evaluation -> getId());

                        $evaluationName = '['.$student->getFirstName() . '_' . $student-> getLastName() . "][" . $evaluationRubric->getName() ."][".$upload -> getFileName()."]";

                    ?>
                    <option value="<?php echo $evaluation->getId(); ?>" <?php if ($selectedTemplate && $evaluation->getId() == $selectedTemplate->getFkEvaluationId()) echo 'selected'; ?>>
                        <?php echo $evaluationName?> </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($selectedTemplate): ?>

            <div class="d-flex align-items-center justify-content-between">
                <h3 class="mb-0">
                    Evaluating rubric: <?php echo htmlspecialchars($selectedTemplate->getName()); ?>
                </h3>

                <a class="fs-5 fw-semibold text-decoration-none"
                href="<?php echo htmlspecialchars('./uploads' . $selectedUpload->getFilePath() . $selectedUpload->getFileName()); ?>">
                    <?php echo htmlspecialchars($selectedUpload->getFileName()); ?>
                </a>

            </div>
            <br>
            <form method="POST" action="" id="rubricAnswersForm">
                <input type="hidden" name="templateId" value="<?php echo $selectedTemplate->getId(); ?>">
                <input type="hidden" name="evaluationId" value="<?php echo htmlspecialchars($selectedTemplate->getFkEvaluationId()); ?>">
                
                <!--Evaluation questions + answer box-->

                <?php foreach ($selectedTemplate -> items as $item): ?>
                    <!-- Question:: -->

                    <div class="row mb-5">
                        <!-- Left column: Name and Description (12/12) -->
                        <div class="card col-12">
                            <div class="card-header mb-1 d-flex align-items-center">
                                <div class="me-3">
                                    <strong><?= htmlspecialchars($item->getName()) ?></strong>
                                </div>

                                <?php if (strtolower($item->getAnswerType()) !== 'text'): ?>
                                    <div class="ms-auto flex-shrink-0">
                                        <?= renderAnswerInput($item) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <?= $item->getDescription() ?>
                            </div>
                        </div>
                    
                    <!-- Answer:: -->

                        <!-- Renders answer inline with question if answer is text, then
                         Full width answer textarea -->
                        <div class= "col-12 mt-2">
                            <?php if (strtolower($item->getAnswerType()) === 'text'): ?>
                                <?= renderAnswerInput($item) ?>
                            <?php endif; ?>
                        </div>

                    <!-- Comment:: -->
                        <div class="col-12 mt-2" <?php echo(($item -> getAnswerType() == "text")?("hidden"):(""));?>> 
                            <!-- Full-width comment textarea only displays when the answer isnt already text-->
                            <label for="<?php echo htmlspecialchars($item->getId() . 'comments'); ?>"> 
                                Add Comments: 
                            </label>
                            <textarea 
                                class="form-control item-comments" 
                                id="<?php echo htmlspecialchars($item->getId() . 'comments'); ?>" 
                                name="<?= htmlspecialchars('comments[' . $item->getId() . ']') ?>" 
                                ><?= htmlspecialchars($item->getComments() ?? '') ?></textarea>
                            
                        </div>
                    </div>

                <?php endforeach; ?>
                <div class="mb-3">
                    <button type="submit" name = "action" value = "save" class="btn btn-primary">Save Responses</button>
                    <button type="submit" name = "action" value = "submit" class="btn btn-primary">Submit Responses</button>
                </div>
            </form>
    <?php endif; ?>
                



</div>

<script>
    // Track editor instances
    let answerEditors = new Map();

    function createEditorForAnswer(textarea) {
        if (answerEditors.has(textarea)) return Promise.resolve(answerEditors.get(textarea));

        return ClassicEditor.create(textarea, {
            toolbar: ['bold', 'italic', 'bulletedList', 'numberedList', 'undo', 'redo'],
            placeholder: textarea.getAttribute('placeholder') || ''
        })
        .then(editor => {
            answerEditors.set(textarea, editor);
            editor.ui.view.editable.element.style.minHeight = '200px';
            editor.ui.view.editable.element.style.overflowY = 'auto';
            return editor;
        })
        .catch(err => console.error('CKEditor init error:', err));
    }

    function initializeAnswerEditors() {
        document.querySelectorAll('textarea.item-answer').forEach(textarea => {
            createEditorForAnswer(textarea);
        });
        document.querySelectorAll('textarea.item-comments').forEach(textarea => {
            createEditorForAnswer(textarea);
        });
        
    }

    // Run on page load
    window.addEventListener('DOMContentLoaded', initializeAnswerEditors);

    // Sync CKEditor data back into textareas before submitting the form
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('rubricAnswersForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            // For each editor instance, copy data back to its textarea so PHP receives the content
            answerEditors.forEach((editor, textarea) => {
                try {
                    textarea.value = editor.getData();
                } catch (err) {
                    console.warn('Error syncing editor to textarea', err);
                }
            });
        });
    });
</script>