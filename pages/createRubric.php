<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../bootstrap.php';
use DataAccess\RubricsDao;
use Model\Rubric;
use Model\RubricItem; 
use Model\RubricItemOption;

use DataAccess\EvaluationsDao;

$js = array(
    "https://cdn.ckeditor.com/ckeditor5/31.1.0/classic/ckeditor.js"
);

$message = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'saved') {
    $message = 'Rubric saved successfully!';
}

$rubricsDao = new RubricsDao($dbConn, $logger);
$evaluationsDao = new EvaluationsDao($dbConn, $logger);

// ==========================================================
// 1. POST PROCESSING (Create / Update / Copy / Delete)
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $rubricId = $_POST['rubricId'] ?? null;
    $rubricName = $_POST['rubricName'] ?? 'Untitled Rubric';
    
    if (in_array($action, ['create', 'update', 'copy'])) {
        $isNewRubric = ($action === 'create' || $action === 'copy');
        if ($action === 'copy') $rubricName .= ' (Copy)';
        
        // --- A. Save or Update the Core Rubric ---
        if ($isNewRubric) {
            $rubric = new Rubric();
            $rubric->setName($rubricName)
                   ->setLastUsed(date('Y-m-d H:i:s'))
                   ->setLastModified(date('Y-m-d H:i:s'));
            $rubricsDao->addNewRubric($rubric);
            $targetRubricId = $rubricsDao->getLastInsertedRubricId();
        } else {
            $targetRubricId = $rubricId;
            $rubric = clone $rubricsDao->getRubricById($targetRubricId);
            if ($rubric) {
                $rubric->setName($rubricName)->setLastModified(date('Y-m-d H:i:s'));
                $rubricsDao->updateRubric($rubric);
            }
        }

        $submittedItemIds = $_POST['itemIds'] ?? [];

        // --- B. Delete Removed Items (If Updating) ---
        if ($action === 'update' && $targetRubricId) {
            $existingItems = $rubricsDao->getRubricItems($targetRubricId) ?: [];
            foreach ($existingItems as $exItem) {
                if (!in_array($exItem->getId(), $submittedItemIds)) {
                    // Delete associated options first to prevent foreign key errors
                    $opts = $rubricsDao->getRubricItemOptionsByItemId($exItem->getId()) ?: [];
                    foreach ($opts as $o) $rubricsDao->deleteRubricItemOption($o->getId());
                    // Delete the item
                    $rubricsDao->deleteRubricItem($exItem->getId());
                }
            }
        }

        // --- C. Process Items and their Options ---
        foreach ($submittedItemIds as $iid) {
            $itemName = $_POST['itemName'][$iid] ?? '';
            if (trim($itemName) === '') continue; // Skip empty item names
            
            $itemDesc = $_POST['itemDesc'][$iid] ?? '';
            $itemReq = ($_POST['itemCommentRequired'][$iid] ?? 'false') === 'true' ? 1 : 0;
            
            // It's a "new" item if we're creating/copying, OR if the JS generated a temporary ID
            $isNewItem = $isNewRubric || strpos($iid, 'new_') === 0;

            if ($isNewItem) {
                $rubricsDao->createRubricItem($targetRubricId, $itemName, $itemDesc, $itemReq);
                $actualItemId = $rubricsDao->getLastInsertedRubricItemId(); 
            } else {
                $actualItemId = $iid;
                $itemToUpdate = clone $rubricsDao->getRubricItemById($actualItemId);
                if ($itemToUpdate) {
                    $itemToUpdate->setName($itemName)
                                 ->setDescription($itemDesc)
                                 ->setCommentRequired($itemReq);
                    $rubricsDao->updateRubricItem($itemToUpdate);
                }
                
                // Delete missing options for this item
                $submittedOptionIds = $_POST['optionIds'][$iid] ?? [];
                $existingOpts = $rubricsDao->getRubricItemOptionsByItemId($actualItemId) ?: [];
                foreach ($existingOpts as $exOpt) {
                    if (!in_array($exOpt->getId(), $submittedOptionIds)) {
                        $rubricsDao->deleteRubricItemOption($exOpt->getId());
                    }
                }
            }

            // --- D. Process Options for this Item ---
            
            // 1. Existing options submitted (If we are updating)
            if (!$isNewRubric && !$isNewItem && isset($_POST['optionIds'][$iid])) {
                foreach ($_POST['optionIds'][$iid] as $oid) {
                    $optTitle = $_POST['optionTitle'][$oid] ?? '';
                    $optVal = $_POST['optionValue'][$oid] ?? '';
                    $optToUpdate = clone $rubricsDao->getRubricItemOptionById($oid);
                    if ($optToUpdate) {
                        $optToUpdate->setTitle($optTitle)->setValue($optVal);
                        $rubricsDao->updateRubricItemOption($optToUpdate);
                    }
                }
            } elseif ($isNewRubric && isset($_POST['optionIds'][$iid])) {
                // If copying, treat previously existing options as brand new
                foreach ($_POST['optionIds'][$iid] as $oid) {
                    $optTitle = $_POST['optionTitle'][$oid] ?? '';
                    $optVal = $_POST['optionValue'][$oid] ?? '';
                    if (trim($optTitle) !== '') {
                        $rubricsDao->createRubricItemOption($actualItemId, $optVal, $optTitle);
                    }
                }
            }

            // 2. Brand new options added dynamically via Javascript
            if (isset($_POST['optionTitleNew'][$iid])) {
                foreach ($_POST['optionTitleNew'][$iid] as $index => $optTitle) {
                    $optVal = $_POST['optionValueNew'][$iid][$index] ?? '';
                    if (trim($optTitle) !== '') {
                        $rubricsDao->createRubricItemOption($actualItemId, $optVal, $optTitle);
                    }
                }
            }
        }

        // Redirect to edit mode of the new/updated rubric to prevent POST resubmissions on refresh
        header("Location: ?rubricId=" . $targetRubricId . "&msg=saved");
        exit;
    }
}

// ==========================================================
// 2. FETCH DATA FOR DISPLAY
// ==========================================================

$rubrics = $rubricsDao->getAllRubrics();
$selectedRubric = null;
$rubricId = $_REQUEST['rubricId'] ?? null;
$isLocked = false;

if ($rubricId != null){
    $selectedRubric = $rubricsDao->getRubricById($rubricId);
    if ($logger && $selectedRubric) {
        $logger->info('Selected rubric ID: ' . $selectedRubric->getId());
    }
    $isLocked = $evaluationsDao->isRubricInUse($rubricId);
} 
    
$rubricItemsHTML = Array();
if ($selectedRubric){
    $rubricItems = $selectedRubric->getItems();
    if ($rubricItems) {
        foreach ($rubricItems as $item){
            $itemId = $item->getId();
            
            // Main Item Fields
            $disabledAttr = $isLocked ? 'disabled' : '';
            $rubricItemsHTML[$itemId] = '
            <div class="row mt-4 rubric-item-row align-items-start">
                <input type="hidden" name="itemIds[]" value="'.$itemId.'">
                <div class="col-md-8">
                    <input
                        name="itemName['.$itemId.']"
                        class="form-control mb-2 rubric-name-editor"
                        placeholder="Item Name"
                        value="'.htmlspecialchars($item->getName()).'"
                        '.$disabledAttr.'
                    >';
            if ($isLocked) {
                // When a rubric is locked, all of its standard form inputs (textareas, dropdowns) 
                // are disabled or removed to prevent editing historical data.
                // However, the "Copy as New" functionality requires ALL original data to be submitted.
                // This block generates hidden inputs to silently pass that historical data through the POST request.
                $commentReqStr = $item->getCommentRequired() ? 'true' : 'false';
                $rubricItemsHTML[$itemId] .= '
                    <input type="hidden" name="itemName['.$itemId.']" value="'.htmlspecialchars($item->getName()).'">
                    <textarea name="itemDesc['.$itemId.']" style="display:none;">'.htmlspecialchars($item->getDescription()).'</textarea>
                    <input type="hidden" name="itemCommentRequired['.$itemId.']" value="'.$commentReqStr.'">
                    
                    <!-- Read-only visual display of the description -->
                    <div class="form-control" style="min-height: 100px; background-color: #e9ecef; overflow-y: auto;">
                        '.$item->getDescription().'
                    </div>';
            } else {
                $rubricItemsHTML[$itemId] .= '
                    <textarea 
                        name="itemDesc['.$itemId.']" 
                        class="form-control" 
                        placeholder="Description" 
                        rows="4">'.htmlspecialchars($item->getDescription()).'</textarea>';
            }

            // Options container
            $options = $item->getItemOptions() ?: [];
            $optionsLabel = $isLocked ? 'Selected Options:' : 'Possible Options:';
            $rubricItemsHTML[$itemId] .= '
                    <div class="mt-4">
                        <strong>'.$optionsLabel.'</strong><br>
                        <div class="row options-container mt-2">';
            
            
            // Existing options for this item
            if ($options) {
                foreach ($options as $o){
                    $oid = $o->getId();
                    if ($isLocked) {
                        // For locked rubrics, standard option inputs are omitted to prevent tampering.
                        // We use hidden inputs to ensure the "Copy as New" function can still 
                        // submit the original option data (ID, Title, Value) to the server.
                        $rubricItemsHTML[$itemId] .= '
                                <div class="col-md-2 mb-3">
                                    <!-- Hidden inputs preserving locked option data -->
                                    <input type="hidden" name="optionIds['.$itemId.'][]" value="'.$oid.'">
                                    <input type="hidden" name="optionTitle['.$oid.']" value="'.htmlspecialchars($o->getTitle()).'">
                                    <input type="hidden" name="optionValue['.$oid.']" value="'.htmlspecialchars($o->getValue()).'">
                                    
                                    <!-- Read-only visual display of the option -->
                                    <div class="p-3 text-center" style="background-color:#f8f9fa; border:1px solid lightgrey; border-radius:6px; min-height:60px; display:flex; align-items:center; justify-content:center;">
                                        <strong>'.htmlspecialchars($o->getTitle()).'</strong>
                                    </div>
                                </div>';
                    } else {
                        $rubricItemsHTML[$itemId] .= '
                                <div class="col-md-2 option-card" style="border-color:grey;border-style:solid;padding:5px;margin:2px;border-radius:4px;">
                                    <input type="hidden" name="optionIds['.$itemId.'][]" value="'.$oid.'">
                                    <input name="optionTitle['.$oid.']" class="form-control mb-2 rubric-name-editor" value="'.htmlspecialchars($o->getTitle()).'">
                                    <input name="optionValue['.$oid.']" class="form-control mb-2 rubric-name-editor" value="'.htmlspecialchars($o->getValue()).'" type="number">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-option" aria-label="Delete">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>';
                    }
                }
            }

			if (!$isLocked) {
                $rubricItemsHTML[$itemId] .= '
                            <div class="col-md-3">
                                <button type="button" class="btn btn-warning btn-sm btn-add-option" data-itemid="'.$itemId.'" aria-label="Add Option">
                                    <i class="bi bi-plus"></i> Add Option
                                </button>
                            </div>';
            }

            // Close blocks and add controls
            $rubricItemsHTML[$itemId] .= '
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <select name="itemCommentRequired['.$itemId.']" class="form-select" required '.$disabledAttr.'>
                        <option value="true" '.(($item->getCommentRequired()) ? 'selected' : '') .'>Comments Required</option>
                        <option value="false" '.((!$item->getCommentRequired()) ? 'selected' : '') .'>Comments Optional</option>
                    </select>';
            if (!$isLocked) {
                $rubricItemsHTML[$itemId] .= '<button type="button" class="btn btn-danger btn-remove-item"><i class="bi bi-trash"></i></button>';
            }
            $rubricItemsHTML[$itemId] .= '
                </div>
            </div>';
        }
    }
}
    
$rubricSelectorHTML = '
    <form method="GET" class="mb-4">
        <label for="rubricId" class="form-label">Select Rubric to Edit or Copy:</label>
        <select name="rubricId" id="rubricId" class="form-select" onchange="this.form.submit()">
            <option value="">-- Create New Rubric --</option>';
            foreach ($rubrics as $r){
                $selected = ($rubricId == $r->getId()) ? 'selected' : '';
                $rubricSelectorHTML .= '<option value="'.$r->getId().'" '.$selected.'>'. htmlspecialchars($r->getName()).'</option>';
            }
$rubricSelectorHTML .= '</select>
    </form>
';

include_once PUBLIC_FILES . '/modules/header.php';
?>

<div class="container mt-4">
    <h2>Rubric Management</h2>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php echo $rubricSelectorHTML; ?>

    <form method="POST" class="mb-5" id="rubricForm">
        <input type="hidden" name="action" value="<?php echo $selectedRubric ? 'update' : 'create'; ?>">
        <?php if ($selectedRubric): ?>
            <input type="hidden" name="rubricId" value="<?php echo $selectedRubric->getId(); ?>">
        <?php endif; ?>
        
        <div class="mb-3">
            <label for="rubricName" class="form-label">Rubric Name</label>
            <input type="text" class="form-control" id="rubricName" maxlength="255" name="rubricName" required value="<?php echo $selectedRubric ? htmlspecialchars($selectedRubric->getName()) : ''; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>>
            <?php if ($isLocked && $selectedRubric): ?>
                <!-- 
                    Because the visible Rubric Name input is set to disabled when locked, 
                    it won\'t be submitted in the POST request. This hidden input ensures 
                    "Copy as New" still receives the original rubric name to copy from.
                -->
                <input type="hidden" name="rubricName" value="<?php echo htmlspecialchars($selectedRubric->getName()); ?>">
            <?php endif; ?>
        </div>
        
        <div id="itemsContainer" class="mt-3 mb-4">
            <h5>Rubric Items</h5>
            <?php 
                foreach ($rubricItemsHTML as $item) echo $item;
            ?>
        </div>

        <template id="templateRow">
            <div class="row mt-4 rubric-item-row align-items-start">
                <input type="hidden" name="itemIds[]" value="__ITEM_ID__">
                <div class="col-md-8">
                    <input name="itemName[__ITEM_ID__]" class="form-control mb-2 rubric-name-editor" placeholder="Item Name" value="">
                    <textarea name="itemDesc[__ITEM_ID__]" class="form-control" placeholder="Description" rows="4"></textarea>
                    
                    <div class="mt-3">
                        <strong>Possible Options:</strong><br>
                        <div class="row options-container">
                            <div class="col-md-3">
                                <button type="button" class="btn btn-warning btn-sm btn-add-option" data-itemid="__ITEM_ID__" aria-label="Add Option">
                                    <i class="bi bi-plus"></i> Add Option
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 d-flex gap-2">
                    <select name="itemCommentRequired[__ITEM_ID__]" class="form-select" required>
                        <option value="true">Comments Required</option>
                        <option value="false" selected>Comments Optional</option>
                    </select>
                    <button type="button" class="btn btn-danger btn-remove-item"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        </template>

        </template>

        <?php if ($isLocked): ?>
            <div class="alert alert-warning mb-3">
                <i class="bi bi-exclamation-triangle-fill"></i> This rubric is currently in use for an evaluation. Forms and inputs have been locked to prevent modifying historical data.
            </div>
            <div>
                <button type="submit" name="action" value="copy" class="btn btn-info">Copy as New</button>
            </div>
        <?php else: ?>
            <button type="button" class="btn btn-secondary mb-3" id="addItemBtn">Add Item</button>
            <div>
                <button type="submit" class="btn btn-primary">Save Rubric</button>
                <?php if ($selectedRubric): ?>
                    <button type="submit" name="action" value="copy" class="btn btn-info ms-2">Copy as New</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </form>
</div>

<script>
    let editorInstances = new Map(); 
    let newItemCounter = 1; // Used to generate unique IDs for brand new items before they hit the DB

    function createEditorForTextarea(textarea) {
        if (editorInstances.has(textarea)) return Promise.resolve(editorInstances.get(textarea));

        let isLocked = textarea.classList.contains('locked-editor');

        return ClassicEditor.create(textarea, {
            toolbar: isLocked ? [] : ['bold', 'italic', 'bulletedList', 'numberedList', 'undo', 'redo'],
            placeholder: textarea.getAttribute('placeholder') || ''
        })
        .then(editor => {
            if (isLocked) {
                editor.enableReadOnlyMode("lockedRubric");
            }
            editorInstances.set(textarea, editor);
            textarea.classList.add('ckeditor-enabled');
            return editor;
        })
        .catch(err => {
            console.error('CKEditor init error:', err);
            throw err;
        });
    }

    function initializeCKEditors() {
        document.querySelectorAll("textarea").forEach(textarea => {
            if (!document.documentElement.contains(textarea)) return;
            createEditorForTextarea(textarea).catch(()=>{});
        });
    }

    // Handle adding an entirely new item row
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const templateHTML = document.getElementById("templateRow").innerHTML;
        const tempId = 'new_' + newItemCounter++;
        
        // Inject our temporary ID so the backend can group this item's fields and options
        const finalHTML = templateHTML.replace(/__ITEM_ID__/g, tempId);

        const wrapper = document.createElement('div');
        wrapper.innerHTML = finalHTML;
        const newNode = wrapper.firstElementChild;

        document.getElementById("itemsContainer").appendChild(newNode);

        const textarea = newNode.querySelector("textarea");
        if (textarea) createEditorForTextarea(textarea).catch(()=>{});
    });

    // Make sure CKEditor content is pushed to textareas on form submit
    document.getElementById('rubricForm').addEventListener('submit', e => {
        editorInstances.forEach((editor, textarea) => {
            textarea.value = editor.getData().trim(); 
        });
    });

    // Event Delegation for dynamically added elements
    document.addEventListener('click', function(e) {
        
        // Remove Item
        const btnRemoveItem = e.target.closest('.btn-remove-item');
        if (btnRemoveItem) {
            const row = btnRemoveItem.closest('.rubric-item-row');
            if (row) {
                const textarea = row.querySelector("textarea");
                if (textarea && editorInstances.has(textarea)) {
                    editorInstances.get(textarea).destroy().catch(err => console.warn(err));
                    editorInstances.delete(textarea);
                }
                row.remove();
            }
            return;
        }

        // Remove Option
        const btnRemoveOption = e.target.closest('.btn-remove-option');
        if (btnRemoveOption) {
            btnRemoveOption.closest('.option-card').remove();
            return;
        }
        
        // Add Option
        const btnAddOption = e.target.closest('.btn-add-option');
        if (btnAddOption) {
            const itemId = btnAddOption.dataset.itemid;
            const optionsContainer = btnAddOption.closest('.options-container');
            
            const newOptionHTML = `
                <div class="col-md-2 option-card" style="border-color:grey;border-style:solid;padding:5px;margin:2px;border-radius:4px;">
                    <input name="optionTitleNew[${itemId}][]" class="form-control mb-2 rubric-name-editor" placeholder="Title">
                    <input name="optionValueNew[${itemId}][]" class="form-control mb-2 rubric-name-editor" placeholder="Value" type="number">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-option" aria-label="Delete">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>`;
                
            optionsContainer.insertAdjacentHTML('beforeend', newOptionHTML);
        }
    });

    window.addEventListener("DOMContentLoaded", initializeCKEditors);
</script>

<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>