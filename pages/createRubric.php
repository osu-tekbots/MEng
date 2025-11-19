<?php
include_once '../bootstrap.php';
use DataAccess\RubricsDao;
use Model\RubricTemplate;
use Model\RubricItemTemplate; // Fix: Use correct class name for rubric template items

$js = array(
   "https://cdn.ckeditor.com/ckeditor5/31.1.0/classic/ckeditor.js"
);

$rubricsDao = new RubricsDao($dbConn, $logger);

// Handle form submissions (add/edit/delete/copy)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    //change copy to include -copy in name
    if ($action === 'create' || $action === 'copy') {
        $template = new RubricTemplate();

        $name = $_POST['rubricName'] ?? '';
        if($action === 'copy' && !empty($name)) {
            $name .= '-copy';
        }

        $template->setName($name)
                 ->setLastUsed(date('Y-m-d H:i:s'))
                 ->setLastModified(date('Y-m-d H:i:s'));
        $rubricsDao->addNewRubricTemplate($template);
        // Get the last inserted template id via DAO
        $templateId = $rubricsDao->getLastInsertedRubricTemplateId();
        if ($templateId) { 
            $template->setId($templateId);
        }
        $allowedTypes = ['number', 'boolean', 'text'];
        if (!empty($_POST['itemName']) && $templateId !== null) {
            foreach ($_POST['itemName'] as $i => $name) {
                if(trim($name) === '') {
                    continue; // Skip empty names
                }
                $desc = $_POST['itemDesc'][$i] ?? '';
                $type = $_POST['itemType'][$i] ?? '';
                if (!in_array($type, $allowedTypes)) {
                    $type = $allowedTypes[0]; // fallback to ''
                }
                $rubricsDao->createRubricTemplateItem($templateId, $name, $desc, $type);
            }
        }
        $message = 'Rubric template created!';
        //Redirects to new rubric (either copy or entirely new)
        echo "<script>window.location.href = '?templateId={$templateId}';</script>";
        exit;
    } elseif ($action === 'update') {
        $templateId = $_POST['templateId'];
        $template = $rubricsDao->getRubricTemplateById($templateId);
        $template->setName($_POST['rubricName'] ?? '')
                 ->setLastModified(date('Y-m-d H:i:s'));
        $rubricsDao->updateRubricTemplate($template);
        // Update items
        $existingItems = $rubricsDao->getRubricTemplateItems($templateId);
        $existingIds = array_map(function($item){ return $item->getId(); }, $existingItems);
        $submittedIds = $_POST['itemId'] ?? [];
        // Delete removed items
        foreach ($existingIds as $eid) {
            if (!in_array($eid, $submittedIds)) {
                $rubricsDao->deleteRubricTemplateItem($eid);
            }
        }
        // Add/update items
        if (!empty($_POST['itemName'])) {
            foreach ($_POST['itemName'] as $i => $name) {
                $desc = $_POST['itemDesc'][$i] ?? '';
                $type = $_POST['itemType'][$i] ?? '';
                $id = $_POST['itemId'][$i] ?? null;
                if ($id && in_array($id, $existingIds)) {
                    // Update
                    $item = new RubricItemTemplate($id);
                    $item->setFkRubricTemplateId($templateId)
                         ->setName($name)
                         ->setDescription($desc)
                         ->setAnswerType($type);
                    $rubricsDao->updateRubricTemplateItem($item);
                } else {
                    // New
                    $rubricsDao->createRubricTemplateItem($templateId, $name, $desc, $type);
                }
            }
        }
        $message = 'Rubric template updated!';
    }
}

$allowedTypes = ['number', 'boolean', 'text'];
$templates = $rubricsDao->getAllRubricTemplates();
/*
if ($logger) {
    $logger->info('Templates count: ' . (is_array($templates) ? count($templates) : 'not array'));
    foreach ($templates as $i => $tpl) {
        $logger->info("Template [$i] ID: " . $tpl->getId() . ", Name: " . $tpl->getName() . ", Items: " . (isset($tpl->items) && is_array($tpl->items) ? count($tpl->items) : 'no items property'));
        if (isset($tpl->items) && is_array($tpl->items)) {
            foreach ($tpl->items as $j => $item) {
                $logger->info("  Item [$j]: " . $item->getName() . " (" . $item->getAnswerType() . ")");
            }
        }
    }
}
*/

$selectedTemplate = null;
if (isset($_GET['templateId'])) {
    $selectedTemplate = $rubricsDao->getRubricTemplateById($_GET['templateId']);
    if ($logger && $selectedTemplate) {
        $logger->info('Selected Template ID: ' . $selectedTemplate->getId());
    }
}
include_once PUBLIC_FILES . '/modules/header.php';

// Function to build rubric item HTML
function buildTemplateItemRow($allowedTypes, $item = null)
{
    global $logger;

    if($item !== null) {
        //|| $item -> getName() === ''
        if($item -> getName() === null ) {
            $logger -> info('buildTemplateItemRow called with item that has no name.');
            return;
        }
    }
    ob_start();
    $logger->info('Building template item for: ' . ($item ? $item->getName() : 'new item. '. 'With required=' . ($required ? 'true' : 'false')));
    
    ?>

    <div class="row mt-4 rubric-item-row align-items-start">

        <?php if ($item !== null) { ?>
            <input type="hidden"
                name="itemId[]"
                value="<?= htmlspecialchars($item->getId()) ?>">
        <?php } ?>

        <!-- LEFT: Name + Description (2/3 width) -->
        <div class="col-md-8">

            <input
                name="itemName[]"
                class="form-control mb-2 rubric-name-editor"
                placeholder="Item Name"
                value = "<?php echo($item ? ($item->getName()) : '')?>"
            >

            <textarea 
                name="itemDesc[]" 
                class="form-control" 
                placeholder="Description" 
                rows="4"><?php echo($item ? htmlspecialchars($item->getDescription()) : '')?></textarea>

        </div>

        <!-- RIGHT: Type dropdown + Remove button (1/3 width) -->
        <div class="col-md-4 d-flex gap-2">

            <select name="itemType[]" class="form-select" required>
                <?php foreach ($allowedTypes as $type): ?>
                    <option value="<?= htmlspecialchars($type) ?>"
                            <?= ($item && $item->getAnswerType() == $type) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="button" class="btn btn-danger btn-remove-item">&times;</button>
        </div>

    </div>

    <?php
    
    return ob_get_clean();
}

?>


<div class="container mt-4">
    <h2>Rubric Template Management</h2>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="GET" class="mb-4">
        <label for="templateId" class="form-label">Select Rubric Template to Edit or Copy:</label>
        <select name="templateId" id="templateId" class="form-select" onchange="this.form.submit()">
            <option value="">-- Create New Rubric Template --</option>
            <?php foreach ($templates as $tpl): ?>
                <option value="<?php echo $tpl->getId(); ?>" <?php if ($selectedTemplate && $tpl->getId() == $selectedTemplate->getId()) echo 'selected'; ?>><?php echo htmlspecialchars($tpl->getName()); ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <form method="POST" class="mb-5" id = "rubricForm">
        <input type="hidden" name="action" value="<?php echo $selectedTemplate ? 'update' : 'create'; ?>">
        <?php if ($selectedTemplate): ?>
            <input type="hidden" name="templateId" value="<?php echo $selectedTemplate->getId(); ?>">
        <?php endif; ?>
        <div class="mb-3">
            <label for="rubricName" class="form-label">Rubric Name</label>
            <input type="text" class="form-control" id="rubricName" maxlength = "255" name="rubricName" required value="<?php echo $selectedTemplate ? htmlspecialchars($selectedTemplate->getName()) : ''; ?>">
        </div>
        <div id="itemsContainer" class = "mt-3 mb-4 ">
            <h5>Rubric Items</h5>

            <?php 
                $items = $selectedTemplate ? ($selectedTemplate->items ?? []) : [];
                global $logger;
                if (empty($items)) {

                    echo buildTemplateItemRow($allowedTypes, null);
                } else {
                    foreach ($items as $item) {

                        echo buildTemplateItemRow($allowedTypes, $item);
                    }
                }
            ?>
        </div>
            <!-- hidden template row that renders a item so that javascript can copy this renered html around-->
        <template id="templateRow">
            <?= buildTemplateItemRow($allowedTypes, null); ?>
        </template>


        <button type="button" class="btn btn-secondary mb-3" id="addItemBtn">Add Item</button>
        <div>
            <button type="submit" class="btn btn-primary">Save Template</button>
            <?php if ($selectedTemplate): ?>
                <button type="submit" name="action" value="copy" class="btn btn-info ms-2">Copy as New</button>
            <?php endif; ?>
        </div>
    </form>
</div>
<script>
    /* Keep the CKEditor <script> include you already had */
    let editorInstances = new Map(); // map textarea DOM node -> editor instance

    function createEditorForTextarea(textarea) {
        // If CKEditor already created and tracked, don't create again
        if (editorInstances.has(textarea)) return Promise.resolve(editorInstances.get(textarea));

        // Use the textarea's placeholder attribute for CKEditor's placeholder config
        return ClassicEditor.create(textarea, {
            toolbar: ['bold', 'italic', 'bulletedList', 'numberedList', 'undo', 'redo'],
            placeholder: textarea.getAttribute('placeholder') || ''
        })
        .then(editor => {
            editorInstances.set(textarea, editor);
            // mark the textarea as initialized to avoid re-init attempts by other code
            textarea.classList.add('ckeditor-enabled');
            return editor;
        })
        .catch(err => {
            console.error('CKEditor init error:', err);
            throw err;
        });
    }

    function initializeCKEditors() {
        // Initialize only textareas that have not been enabled yet and are already in the DOM
        document.querySelectorAll("textarea").forEach(textarea => {
            // Only init on textareas that are attached to document (avoid detached nodes)
            if (!document.documentElement.contains(textarea)) return;
            createEditorForTextarea(textarea).catch(()=>{/* already logged */});
        });
    }

    //Hanldes adding a row logic, including making names required and initializing CKEditor on the new textarea
    document.getElementById('addItemBtn').addEventListener('click', function() {
        console.log("Add item btn clicked");

        // Use the <template> node and clone it so we get real DOM nodes with real properties
        const template = document.getElementById("templateRow");
        if (!template) {
            console.error("Template row not found!");
            return;
        }

        // Clone the template content (deep clone)
        const newNode = template.content.firstElementChild.cloneNode(true);

        // Clear any content inside the textarea element directly (so CKEditor starts empty)
        const textarea = newNode.querySelector("textarea[name='itemDesc[]']");
        if (textarea) {
            // Clear the *text content* (what would show inside <textarea>...</textarea>)
            textarea.value = "";
            // Remove any preexisting ckeditor marker
            textarea.classList.remove('ckeditor-enabled');
        }

        // Append the new node to the container
        const container = document.getElementById("itemsContainer");
        container.appendChild(newNode);
        //Make all names required


        // Initialize CKEditor on JUST the newly appended textarea element
        // Initialize CKEditor for both name and description
        if (textarea) createEditorForTextarea(textarea).catch(()=>{/* logged above */});

    });

    //Handles highliting required fields on form submission to ensure they are filled out
    document.getElementById('rubricForm').addEventListener('submit', e => {
        let valid = true;
        editorInstances.forEach((editor, textarea) => {
            const content = editor.getData().trim(); // get CKEditor content
            textarea.value = content; // sync for backend

            /*
            if (textarea.name === 'itemName[]' && !content) {
                
                valid = false;
                // highlight the empty editor
                editor.ui.view.editable.element.style.border = '2px solid red';
            } else {
                editor.ui.view.editable.element.style.border = ''; // remove highlight if filled
            }
                */
        });

        if (!valid) {
            e.preventDefault(); // prevent form submission
            alert('Please fill out all Item Name fields.');
        }
    });

    // Handle removing items
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-item')) {
            // Find the nearest rubric-item-row and before removing, destroy any attached CKEditor instance
            const row = e.target.closest('.rubric-item-row');
            if (!row) {
                e.target.closest('.rubric-item-row')?.remove();
                return;
            }

            const textarea = row.querySelector("textarea[name='itemDesc[]']");
            if (textarea && editorInstances.has(textarea)) {
                const editor = editorInstances.get(textarea);
                // destroy the CKEditor instance gracefully
                editor.destroy().catch(err => console.warn('Error destroying editor', err));
                editorInstances.delete(textarea);
            }

            row.remove();
        }
    });

    // Run initialization for editors that came from server-render on DOMContentLoaded
    window.addEventListener("DOMContentLoaded", initializeCKEditors);
</script>

<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
