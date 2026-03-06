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


/* =========================================================================
 *  External JS dependencies
 * ========================================================================= */

$js = array(
   "https://cdn.ckeditor.com/ckeditor5/31.1.0/classic/ckeditor.js"
);


/* =========================================================================
 *  DAO initialisation & data loading
 * ========================================================================= */

$evaluationsDao = new EvaluationsDao($dbConn, $logger);
$usersDao = new UsersDao($dbConn, $logger); //Need first name, last name
$uploadsDao = new UploadsDao($dbConn, $logger);//Need upload name and upload file path
$rubricsDao = new RubricsDao($dbConn, $logger);

// Evaluations assigned to the current reviewer
$evaluations = $evaluationsDao->getEvaluationsByReviewerUserId($_SESSION['userID']);


/* =========================================================================
 *  HELPER FUNCTIONS — rendering individual evaluation items & options
 * ========================================================================= */

/**
 * Render a single option button (radio + label) for a rubric item.
 *
 * @param object $opt             The RubricItemOption model
 * @param string $inputName       The HTML input `name` attribute (e.g. "answers[<id>]")
 * @param string $rawId           The rubric-item ID (used to build unique element IDs)
 * @param string|null $currentOptionId  The currently-selected option ID (if any)
 */
function renderOptionButton($opt, $inputName, $rawId, $currentOptionId) { ?>
    <input class="btn-check" type="radio"
           name="<?= htmlspecialchars($inputName) ?>"
           id="<?= htmlspecialchars($rawId . '_' . $opt->getId()) ?>"
           value="<?= htmlspecialchars($opt->getId()) ?>"
           <?= ($currentOptionId == $opt->getId() ? 'checked' : '') ?>
           required>
    <label class="btn rubric-btn w-100 h-100 d-flex align-items-center justify-content-center text-center p-3"
           for="<?= htmlspecialchars($rawId . '_' . $opt->getId()) ?>">
        <?= htmlspecialchars($opt->getTitle()) ?>
    </label>
<?php }


/**
 * Render a complete evaluation item card: header, description, option
 * buttons (laid out differently depending on count), and comment textarea.
 *
 * @param object       $item            The rubric item model
 * @param array|null   $options         Array of RubricItemOption models
 * @param string|null  $currentOptionId Currently-selected option ID
 * @param string       $currentComment  Existing comment text
 * @param string       $rawId           The rubric-item ID
 * @param string       $name            HTML input name for the answers radio group
 */
function renderEvaluationItem($item, $options, $currentOptionId, $currentComment, $rawId, $name) {
    $optionCount = $options ? count($options) : 0;
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
                 *  Options layout — more than 2 options:
                 *  render as a full-width flex row, comments span full width below
                 * --------------------------------------------------------- */
                if ($optionCount > 2): ?>
                    <div class="d-flex w-100 gap-2 mb-4 flex-wrap">
                        <?php foreach ($options as $opt): ?>
                            <div class="flex-fill" style="flex-basis: 0; min-width: 150px;">
                                <?php renderOptionButton($opt, $name, $rawId, $currentOptionId); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <?php
                    /* ---------------------------------------------------------
                     *  Options layout — 1 or 2 options:
                     *  render in a narrower column with the comment box beside them
                     * --------------------------------------------------------- */
                    if ($optionCount > 0 && $optionCount <= 2): ?>
                        <div class="col-md-5 d-flex flex-column gap-3 mb-3 mb-md-0">
                            <?php foreach ($options as $opt): ?>
                                <div class="flex-fill">
                                    <?php renderOptionButton($opt, $name, $rawId, $currentOptionId); ?>
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
                        <!-- Full-width comment box (used when there are 0 or >2 options) -->
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
<?php }


/* =========================================================================
 *  FORM SUBMISSION HANDLER (POST)
 *
 *  When the reviewer clicks "Save" or "Submit", this block:
 *    1. Reads the posted evaluation ID, answers (radio selections), and
 *       free-text comments from the form.
 *    2. Sets the evaluation status flag:
 *         - "save"   → status 2 (Draft)
 *         - "submit" → status 3 (Submitted)
 *    3. Loads the rubric template attached to the evaluation so we can
 *       iterate over every rubric item (even ones the reviewer may not
 *       have answered — those will store NULL).
 *    4. For each rubric item:
 *         a. Looks up any existing EvaluationRubricItem row for this
 *            evaluation + rubric-item pair.
 *         b. If a row already exists → updates its option selection and
 *            comments, then persists via setEvaluationRubricItem().
 *         c. If no row exists → creates a new EvaluationRubricItem,
 *            populates it, and persists via insertEvaluationRubricItem().
 *    5. Redirects (PRG pattern) back to the same evaluation page so the
 *       reviewer sees fresh data and a browser-refresh won't re-post.
 * ========================================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Pull submitted data from the form
    $evaluationId = $_POST['evaluationId'] ?? '';
    $answers      = $_POST['answers']      ?? [];   // keyed by rubric-item ID → selected option ID
    $comments     = $_POST['comments']     ?? [];   // keyed by rubric-item ID → comment text

    $logger->info(json_encode($_POST));
    $updated = 0; // counter for successfully saved items

    if (!empty($evaluationId)) {

        /* --- Set evaluation status flag based on button clicked --- */
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'submit') {
                // Mark evaluation as "Submitted" (status flag 3)
                $evaluationsDao->setStatusFlagForEvaluation($evaluationId, 3);
            } else if ($_POST['action'] === 'save') {
                // Mark evaluation as "Draft" (status flag 2)
                $evaluationsDao->setStatusFlagForEvaluation($evaluationId, 2);
            }
        }

        /* --- Load the rubric template so we iterate every item --- */
        $postedRubric = $evaluationsDao->getRubricFromEvaluationId($evaluationId);

        if ($postedRubric && !empty($postedRubric->items)) {
            foreach ($postedRubric->items as $item) {
                $rubricItemId = $item->getId();

                // Get the posted answer/comment for this item; null if not present
                $value   = array_key_exists($rubricItemId, $answers)  ? $answers[$rubricItemId]  : null;
                $comment = array_key_exists($rubricItemId, $comments) ? $comments[$rubricItemId] : null;

                // Check whether a saved row already exists for this evaluation + rubric item
                $evalItem = $evaluationsDao->getEvaluationRubricItemByEvalAndRubricItem($evaluationId, $rubricItemId);

                if ($evalItem) {
                    /* -- UPDATE existing row -- */
                    $evalItem->setComments($comment);
                    $optionModel = new RubricItemOption($value);
                    $evalItem->setRubricItemOption($optionModel);

                    if ($evaluationsDao->setEvaluationRubricItem($evalItem)) {
                        $updated++;
                    }
                } else {
                    /* -- INSERT new row -- */
                    $evalItem = new EvaluationRubricItem();
                    $evalItem->setFkEvaluationId($evaluationId);

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

    // PRG redirect — prevents duplicate submissions on browser refresh
    header('Location: ?evaluationId=' . urlencode($evaluationId));
    exit;
}


/* =========================================================================
 *  LOAD SELECTED EVALUATION (GET parameter)
 * ========================================================================= */

$logger->info('User ID ' . $_SESSION['userID'] . ' has ' . count($evaluations) . ' evaluations.');

$selectedTemplate = null;
$selectedUpload = '';

if (isset($_GET['evaluationId'])) {

    $selectedUpload = $uploadsDao->getUpload(
        $evaluationsDao->getEvaluationById($_GET['evaluationId'])->getFkUploadId()
    );

    $selectedTemplate = $evaluationsDao->getRubricFromEvaluationId($_GET['evaluationId']);

    if ($logger && $selectedTemplate) {
        $logger->info('Selected Evaluation ID: ' . $_GET['evaluationId']);
        $logger->info('Selected Template Name: ' . $selectedTemplate->getName());
        $logger->info('Selected Template Items: ' . count($selectedTemplate->items));
        $logger->info('Selected upload: ' . $selectedUpload->getFilePath() . $selectedUpload->getFileName());

        // Debug: confirm item count
        if (isset($selectedTemplate->items) && !empty($selectedTemplate->items)) {
            $logger->info('Rubric Template has ' . count($selectedTemplate->items) . ' items.');
        } else {
            $logger->error('Rubric Template items array is missing or empty.');
        }
    } else {
        $logger->error('Failed to load selectedTemplate for evaluation ID: ' . $_GET['evaluationId']);
    }
}

include_once PUBLIC_FILES . '/modules/header.php';
?>


<!-- =====================================================================
     CSS — custom rubric-button styles
     ===================================================================== -->

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


<!-- =====================================================================
     PAGE CONTENT
     ===================================================================== -->

<div class="container mt-4">
    <h2>Review Document</h2>


    <!-- =================================================================
         Evaluation selector dropdown
         ================================================================= -->

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
                        $evaluationRubric = $evaluationsDao->getRubricFromEvaluationId($evaluation->getId());

                        $evaluationName = '['.$student->getFirstName() . '_' . $student->getLastName() . "][" . $evaluationRubric->getName() ."]";
                    ?>
                    <option value="<?php echo $evaluation->getId(); ?>" <?php if (isset($_GET['evaluationId']) && $evaluation->getId() == $_GET['evaluationId']) echo 'selected'; ?>>
                        <?php echo $evaluationName?> </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>


    <!-- =================================================================
         Rubric evaluation form (shown when an evaluation is selected)
         ================================================================= -->

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
                <?php foreach ($selectedTemplate->items as $item): 
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
                    <?php renderEvaluationItem($item, $options, $currentOptionId, $currentComment, $rawId, $name); ?>
                <?php endforeach; ?>

                <div class="mb-3">
                    <button type="submit" name = "action" value = "save" class="btn btn-primary">Save Responses</button>
                    <button type="submit" name = "action" value = "submit" class="btn btn-primary">Submit Responses</button>
                </div>
            </form>
    <?php endif; ?>
                


</div>


<!-- =====================================================================
     JavaScript — CKEditor initialisation & form-sync
     ===================================================================== -->

<script>
    // Map of textarea elements → CKEditor instances
    let answerEditors = new Map();

    /**
     * Create a CKEditor instance for the given textarea (if one doesn't
     * already exist). Returns a Promise that resolves with the editor.
     */
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

    /** Initialise CKEditor on every comment textarea. */
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