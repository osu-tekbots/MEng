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

<div class="container mt-4">
    <h2>Review Document</h2>

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
                
                <?php foreach ($selectedTemplate -> items as $item): ?>
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

                        <!-- Full-width answer textarea below -->
                        <div class="col-12 mt-2">
                            <textarea 
                                class="form-control item-answer" 
                                id="<?php echo htmlspecialchars($item->getId() . 'answer'); ?>" 
                                name="answers[<?php echo htmlspecialchars($item->getId()); ?>]" 
                                rows="3">
                            </textarea>
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
        
    }

    // Run on page load
    window.addEventListener('DOMContentLoaded', initializeAnswerEditors);
</script>