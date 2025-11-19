<?php
include_once '../bootstrap.php';
use DataAccess\EvaluationsDao;
use Model\Evaluation;
use Model\EvaluationRubric;
use Model\EvaluationRubricItem;

$js = array(
   "https://cdn.ckeditor.com/ckeditor5/31.1.0/classic/ckeditor.js"
);


$evaluationsDao = new EvaluationsDao($dbConn, $logger);
//$allowedTypes = ['number', 'boolean', 'text'];

//Not really being used right now
$evaluations = $evaluationsDao->getEvaluationsByReviewerUserId($_SESSION['userID']);
$selectedEvaluation = null;
//

$evaluationRubricTemplates = $evaluationsDao -> getEvaluationRubricsByReviewerUserId($_SESSION['userID']);
$logger -> info('Fetched ' . count($evaluationRubricTemplates) . ' evaluation rubric templates for user ID ' . $_SESSION['userID']);
$selectedTemplate = null;

$logger -> info('User ID ' . $_SESSION['userID'] . ' has ' . count($evaluations) . ' evaluations.');
if (isset($_GET['evaluationId'])) {
    //Redundant code 
    //$selectedEvaluation = $evaluationsDao->getEvaluationById($_GET['evaluationId']);
    $selectedTemplate = $evaluationsDao->getEvaluationRubricFromEvaluationId($_GET['evaluationId']);

    if ($logger && $selectedTemplate) {
        //$logger->info('Selected Evaluation ID: ' . $selectedEvaluation->getId());
        $logger->info('Selected Evaluation Rubric ID: ' . $selectedTemplate->getId());
    }
}

include_once PUBLIC_FILES . '/modules/header.php';
?>

<?php
function renderAnswerInput($item) {
    $type = $item->getAnswerType();
    $id   = htmlspecialchars($item->getId());
    $name = "answers[$id]";

    switch ($type) {

        /* ---------------------------------------------------------
           TEXT INPUT
        --------------------------------------------------------- */
        case "text": ?>
            <textarea
                class="form-control item-answer"
                id="<?= $id ?>_answer"
                name="<?= $name ?>"
                rows="3"
            ></textarea>
        <?php break;

        /* ---------------------------------------------------------
           BOOLEAN (TRUE / FALSE)
        --------------------------------------------------------- */
        case "boolean": ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input"
                       type="radio"
                       name="<?= $name ?>"
                       id="<?= $id ?>_answer"
                       value="true">
                <label class="form-check-label" for="<?= $id ?>_true">True</label>
            </div>

            <div class="form-check form-check-inline">
                <input class="form-check-input"
                       type="radio"
                       name="<?= $name ?>"
                       id="<?= $id ?>_answer"
                       value="false">
                <label class="form-check-label" for="<?= $id ?>_false">False</label>
            </div>
        <?php break;

        /* ---------------------------------------------------------
           NUMBER — LIKERT SCALE (1–10)
        --------------------------------------------------------- */
        case "number": ?>
            <div class="likert-scale d-flex gap-2 flex-wrap">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <div class="form-check">
                        <input  class="form-check-input"
                                type="radio"
                                name="<?= $name ?>"
                                id="<?= $id ?>_answer"
                                value="<?= $i ?>">
                        <label class="form-check-label" for="<?= $id ?>_<?= $i ?>">
                            <?= $i ?>
                        </label>
                    </div>
                <?php endfor; ?>
            </div>
        <?php break;

        /* ---------------------------------------------------------
           DEFAULT → TEXTAREA
        --------------------------------------------------------- */
        default: ?>
            <textarea
                class="form-control item-answer"
                id="<?= $id ?>_answer"
                name="<?= $name ?>"
                rows="3"
            ></textarea>
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
                <?php foreach ($evaluationRubricTemplates as $eval): ?>
                    <option value="<?php echo $eval->getFkEvaluationId(); ?>" <?php if ($selectedTemplate && $eval->getFkEvaluationId() == $selectedTemplate->getFkEvaluationId()) echo 'selected'; ?>><?php echo htmlspecialchars($eval->getName()); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($selectedTemplate): ?>
        <h3>Evaluating rubric: <?php echo htmlspecialchars($selectedTemplate->getName()); ?></h3>
            <form method="POST" action="submitRubricAnswers.php">
                <input type="hidden" name="templateId" value="<?php echo $selectedTemplate->getId(); ?>">
                
                <!--Evaluation questions + answer box-->

                <?php foreach ($selectedTemplate -> items as $item): ?>
                    <!-- Question:: -->

                    <div class="row mb-5">
                        <!-- Left column: Name and Description (12/12) -->
                        <div class="card col-md-12">
                            <!-- Item Name -->
                            <div class="card-header mb-1 ">
                                <?php echo $item->getName(); ?>
                                <div> 
                                    <span> <strong>Answer Type: </strong> 
                                        <?php echo $item->getAnswerType(); ?>
                                    </span>
                                </div>    
                            </div>

                            <!-- Item Description -->
                            <div class="card-body">
                                <?php echo $item->getDescription(); ?>
                            </div>
                        </div>
                    
                    <!-- Answer:: -->

                        <!-- Full-width answer textarea below -->
                        <div class="col-12 mt-2">
                            <?php renderAnswerInput($item); ?>
                            <!-- Displays the correct answer type for the item -->
                        </div>

                    <!-- Comment:: -->
                        <div class="col-12 mt-2" <?php echo(($item -> getAnswerType() == "text")?("hidden"):(""));?>> 
                            <!-- Full-width comment textarea only displays when the answer isnt already text-->
                            <label> 
                                Add Comments: 

                                <textarea 
                                    class="form-control item-comments" 
                                    id="<?php echo htmlspecialchars($item->getId() . 'comments'); ?>" 
                                    name="comments[<?php echo htmlspecialchars($item->getId()); ?>]" 
                                    >
                                </textarea>
                            </label>
                        </div>
                    </div>

                <?php endforeach; ?>
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
</script>