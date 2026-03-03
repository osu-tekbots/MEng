<?php
include_once '../bootstrap.php';
use DataAccess\EvaluationsDao;
use DataAccess\UploadsDao;
use DataAccess\UsersDao;

use Model\Evaluation;
use Model\EvaluationRubric;
use Model\EvaluationRubricItem;
use Model\RubricItem;
use Model\RubricItemOption;
use DataAccess\RubricsDao;

$js = array(
   "https://cdn.ckeditor.com/ckeditor5/31.1.0/classic/ckeditor.js"
);


$evaluationsDao = new EvaluationsDao($dbConn, $logger);
$usersDao = new UsersDao($dbConn, $logger); //Need first name, last name
$uploadsDao = new UploadsDao($dbConn, $logger);//Need upload name and upload file path
$rubricsDao = new RubricsDao($dbConn, $logger);
//Evaluations assigned to reviewer
$evaluations = $evaluationsDao->getEvaluationsByReviewerUserId($_SESSION['userID']);


// Handle form submission for saving rubric answers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prefer loading the selected evaluation rubric so we process every item
    $evaluationId = $_POST['evaluationId'] ?? '';
    $answers = $_POST['answers'] ?? [];
    //Answers needs to hold ids for options? Or we can just hold the rubric items whole
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

        $postedRubric = $evaluationsDao->getRubricFromEvaluationId($evaluationId);
        if ($postedRubric && !empty($postedRubric->items)) {
            foreach ($postedRubric->items as $item) {
                $rubricItemId = $item->getId();

                // Lookup posted values by raw id key. Use null if absent (eg. unchecked radio)
                $value = array_key_exists($rubricItemId, $answers) ? $answers[$rubricItemId] : null;
                $comment = array_key_exists($rubricItemId, $comments) ? $comments[$rubricItemId] : null;

                $evalItem = $evaluationsDao->getEvaluationRubricItemByEvalAndRubricItem($evaluationId, $rubricItemId);
                
                if ($evalItem) {
                    $evalItem->setComments($comment);
                    $optionModel = new RubricItemOption($value);
                    $evalItem->setRubricItemOption($optionModel);
                    if ($evaluationsDao->setEvaluationRubricItem($evalItem)) {
                        $updated++;
                    }
                } else {
                    $evalItem = new EvaluationRubricItem();
                    $evalItem-> setFkEvaluationId($evaluationId);
                    
                    $itemModel = new RubricItem($rubricItemId);
                    $evalItem->setRubricItem($itemModel);
                    
                    $optionModel = new RubricItemOption($value);
                    $evalItem->setRubricItemOption($optionModel);
                    $evalItem->setComments($comment);
                    
                    if ($evaluationsDao->insertEvaluationRubricItem($evalItem)) {
                        $updated++;
                    }
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

    $selectedTemplate = $evaluationsDao->getRubricFromEvaluationId($_GET['evaluationId']);
    if ($logger && $selectedTemplate) {

        $logger->info('Selected Evaluation ID: ' . $_GET['evaluationId']);
        $logger->info('Selected Template Name: ' . $selectedTemplate->getName());
        $logger->info('Selected Template Items: ' . count($selectedTemplate->items));
        $logger->info('Selected upload: ' . $selectedUpload -> getFilePath().$selectedUpload->getFileName());
        
        // Debugging items list
        if (isset($selectedTemplate->items) && !empty($selectedTemplate->items)) {
            $logger->info('Rubric Template has ' . count($selectedTemplate->items) . ' items.');
            // foreach($selectedTemplate->items as $idx => $ti) {
            //      $logger->info('   Item ' . $idx . ': ID=' . $ti->getId() . ', Name=' . $ti->getName());
            // }
        } else {
            $logger->error('Rubric Template items array is missing or empty.');
        }

    } else {
        $logger->error('Failed to load selectedTemplate for evaluation ID: ' . $_GET['evaluationId']);
    }
}

include_once PUBLIC_FILES . '/modules/header.php';
?>


<style>
.rubric-btn {
    background-color: #f8f9fa;
    color: #495057;
    border: 2px solid #e9ecef;
    transition: all 0.2s ease-in-out;
    font-size: 1.05rem;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    cursor: pointer;
}
.rubric-btn:hover {
    background-color: #4d99e6ff;
    border-color: #4d99e6ff;
}
.btn-check:checked + .rubric-btn {
    background-color: #198754 !important; /* Bootstrap Success Green */
    color: white !important;
    border-color: #198754 !important;
    box-shadow: inset 0 3px 5px rgba(0,0,0,0.125);
}
</style>

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
                        $evaluationRubric = $evaluationsDao -> getRubricFromEvaluationId($evaluation -> getId());

                        $evaluationName = '['.$student->getFirstName() . '_' . $student-> getLastName() . "][" . $evaluationRubric->getName() ."]";

                    ?>
                    <option value="<?php echo $evaluation->getId(); ?>" <?php if (isset($_GET['evaluationId']) && $evaluation->getId() == $_GET['evaluationId']) echo 'selected'; ?>>
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
                href="<?php echo htmlspecialchars('./uploads' . $selectedUpload->getFilePath() . $selectedUpload->getId() ); ?>" download="<?php echo $selectedUpload->getFileName();?>">
                    <?php echo htmlspecialchars($selectedUpload->getFileName()); ?>
                </a>

            </div>
            <br>
            <form method="POST" action="" id="rubricAnswersForm">
                <input type="hidden" name="templateId" value="<?php echo $selectedTemplate->getId(); ?>">
                <input type="hidden" name="evaluationId" value="<?php echo htmlspecialchars($_GET['evaluationId']); ?>">
                
                <!--Evaluation questions + answer box-->
                <?php $logger->info('Iterating through ' . count($selectedTemplate->items) . ' items for rendering.'); ?>
                <?php foreach ($selectedTemplate -> items as $item): 
                    $options = $rubricsDao->getRubricItemOptionsByItemId($item->getId());
                    if (!$options) {
                        $logger->error('No options found for item ID: ' . $item->getId());
                    } else {
                         $logger->info('Found ' . count($options) . ' options for item ID ' . $item->getId());
                    }
                    $existingItem = $evaluationsDao->getEvaluationRubricItemByEvalAndRubricItem($_GET['evaluationId'], $item->getId());
                    $currentOptionId = $existingItem && $existingItem->getRubricItemOption() ? $existingItem->getRubricItemOption()->getId() : null;
                    $currentComment = $existingItem ? $existingItem->getComments() : '';
                    $rawId = $item->getId();
                    $name = 'answers[' . $rawId . ']';
                ?>
                    <div class="row mb-5">
                        <div class="card col-12 p-0 border-primary">
                            <div class="card-header bg-primary text-white mb-1">
                                <strong><?= htmlspecialchars($item->getName()) ?></strong>
                            </div>

                            <div class="card-body">
                                <div class="mb-4">
                                    <?= $item->getDescription() ?>
                                </div>
                                
                                <?php 
                                $optionCount = $options ? count($options) : 0; 
                                if ($optionCount > 2): 
                                ?>
                                    <div class="d-flex w-100 gap-2 mb-4 flex-wrap">
                                        <?php foreach ($options as $opt): ?>
                                            <div class="flex-fill" style="flex-basis: 0; min-width: 150px;">
                                                <input class="btn-check" type="radio" name="<?= htmlspecialchars($name) ?>" id="<?= htmlspecialchars($rawId . '_' . $opt->getId()) ?>" value="<?= htmlspecialchars($opt->getId()) ?>" <?= ($currentOptionId == $opt->getId() ? 'checked' : '') ?> required>
                                                <label class="btn rubric-btn w-100 h-100 d-flex align-items-center justify-content-center text-center p-3" for="<?= htmlspecialchars($rawId . '_' . $opt->getId()) ?>">
                                                    <?= htmlspecialchars($opt->getTitle()) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="row">
                                    <?php if ($optionCount > 0 && $optionCount <= 2): ?>
                                        <div class="col-md-5 d-flex flex-column gap-3 mb-3 mb-md-0">
                                            <?php foreach ($options as $opt): ?>
                                                <div class="flex-fill">
                                                    <input class="btn-check" type="radio" name="<?= htmlspecialchars($name) ?>" id="<?= htmlspecialchars($rawId . '_' . $opt->getId()) ?>" value="<?= htmlspecialchars($opt->getId()) ?>" <?= ($currentOptionId == $opt->getId() ? 'checked' : '') ?> required>
                                                    <label class="btn rubric-btn w-100 h-100 d-flex align-items-center justify-content-center text-center p-3" for="<?= htmlspecialchars($rawId . '_' . $opt->getId()) ?>">
                                                        <?= htmlspecialchars($opt->getTitle()) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="col-md-7">
                                            <label class="mb-2" for="<?= htmlspecialchars('comments_' . $rawId) ?>">
                                                <strong>Add Comments <?= $item->getCommentRequired() ? '<span class="text-danger">*</span>' : '' ?>:</strong>
                                            </label>
                                            <textarea class="form-control item-comments" id="<?= htmlspecialchars('comments_' . $rawId) ?>" name="<?= htmlspecialchars('comments[' . $rawId . ']') ?>" <?= $item->getCommentRequired() ? 'required' : '' ?>><?= htmlspecialchars($currentComment) ?></textarea>
                                        </div>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <label class="mb-2" for="<?= htmlspecialchars('comments_' . $rawId) ?>">
                                                <strong>Add Comments <?= $item->getCommentRequired() ? '<span class="text-danger">*</span>' : '' ?>:</strong> 
                                            </label>
                                            <textarea class="form-control item-comments" id="<?= htmlspecialchars('comments_' . $rawId) ?>" name="<?= htmlspecialchars('comments[' . $rawId . ']') ?>" <?= $item->getCommentRequired() ? 'required' : '' ?>><?= htmlspecialchars($currentComment) ?></textarea>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
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

        // Prevent HTML5 validation from silently failing when it tries to focus an obscured textarea.
        textarea.removeAttribute('required');

        return ClassicEditor.create(textarea, {
            toolbar: ['bold', 'italic', 'bulletedList', 'numberedList', 'undo', 'redo'],
            placeholder: textarea.getAttribute('placeholder') || ''
        })
        .then(editor => {
            answerEditors.set(textarea, editor);
            editor.ui.view.editable.element.style.minHeight = '200px';
            editor.ui.view.editable.element.style.overflowY = 'auto';

            // Continuously sync data into the hidden textarea 
            editor.model.document.on('change:data', () => {
                textarea.value = editor.getData();
            });

            return editor;
        })
        .catch(err => console.error('CKEditor init error:', err));
    }

    function initializeAnswerEditors() {
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