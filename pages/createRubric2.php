<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../bootstrap.php';
use DataAccess\RubricsDao;
use Model\Rubric;
use Model\RubricItem; 
use Model\RubricItemOption;

$js = array(
   "https://cdn.ckeditor.com/ckeditor5/31.1.0/classic/ckeditor.js"
);

$message = '';

$rubricsDao = new RubricsDao($dbConn, $logger);
$rubrics = $rubricsDao->getAllRubrics();

$selectedRubric = null;
$rubricId = null;

//Are we workign on a specific rubric Id? If so, Log it.
if (isset($_REQUEST['rubricId'])){
	$rubricId = $_REQUEST['rubricId'];
	$selectedRubric = $rubricsDao->getRubricById($rubricId);
	if ($logger && $selectedRubric)
        $logger->info('Selected rubric ID: ' . $selectedRubric->getId());
} 

	
//Use PHP to build the array of data to display. We will build the html into an array for each rubric item. 
//We will be putting options into the rubric items.
$rubricItemsHTML = Array();
if ($rubricId != null){
	if (!($rubric = $rubricsDao->getRubricById($rubricId))){
		$message = "Invalid Rubric ID.";
		echo $message;
		exit();
	}
	$rubricItems = $rubric->getItems();
	foreach ($rubricItems as $item){
		$rubricItemsHTML[$item->getId()] = '
		<div class="row mt-4 rubric-item-row align-items-start">
		<input type="hidden" name="itemId[]" id="item'.$item->getId().'" value="'.$item->getId().'">
        <div class="col-md-8">

            <input
                name="itemName'.$item->getId().'"
                id="itemName'.$item->getId().'"
                class="form-control mb-2 rubric-name-editor"
                placeholder="Item Name"
                value = "'.$item->getName().'"
            >

            <textarea 
                name="itemDesc'.$item->getId().'" 
                class="form-control" 
                placeholder="Description" 
                rows="4">'.$item->getDescription().'</textarea> ';

		$options = $item->getItemOptions();
		$rubricItemsHTML[$item->getId()] .= 'Possible Options:<BR>
		<div class="row">
		<div class="col-md-2">
			<input name="optionTitleNew"
				class="form-control mb-2 rubric-name-editor"
				placeholder = "Title"
				>
			<input name="optionValueNew"
					class="form-control mb-2 rubric-name-editor"
					placeholder="Value"
					type = "number"
			>
		<button type="button" class="btn btn-warning btn-add-option" data-itemid="'.$item->getId().'" id="addOption'.$item->getId().'" aria-label="Add Option"><i class="bi bi-plus"></i> Add Option</button>
		</div>';
		
		if ($options)
			foreach ($options as $o){
				$rubricItemsHTML[$item->getId()] .= '
					<div class="col-md-2" style="border-color:grey;border-style:solid;padding:2px;margin:2px;" id="option'. $o->getId().'">
					<input name="optionTitle'. $o->getId().'"
					class="form-control mb-2 rubric-name-editor"
					value = "'.$o->getTitle().'"
					>
					<input
							name="optionValue'. $o->getId().'"
							class="form-control mb-2 rubric-name-editor"
							value="'.$o->getValue().'"
							type = "number"
					>
					<button type="button" class="btn btn-warning btn-remove-option" data-optionid="'.$o->getId().'" aria-label="Delete"><i class="bi bi-trash"></i>Delete</button>
					</div>';
			}

		$rubricItemsHTML[$item->getId()] .= '</div></div>
        <div class="col-md-4 d-flex gap-2">
            <select name="itemCommentRequired'.$item->getId().'" class="form-select" required>
                <option value="true" '.(($item && $item->getCommentRequired()) ? 'selected' : '') .'>
                    Comments Required
                </option>
                <option value="false" '.(($item && !$item->getCommentRequired()) ? 'selected' : '') .'>
                    Comments Optional
                </option>
            </select>
            
            <button type="button" class="btn btn-danger btn-remove-item" data-itemid="'.$item->getId().'"><i class="bi bi-trash"></i></button>
        </div>

    </div>
		';
		}
	}
	
/*	
$action = $_POST['action'] ?? '';
//change copy to include -copy in name
if ($action === 'create' || $action === 'copy') {
	$rubric = new Rubric();

	$name = $_POST['rubricName'] ?? '';
        if($action === 'copy' && !empty($name)) {
		$name .= '-copy';
	}

	$rubric->setName($name)
			 ->setLastUsed(date('Y-m-d H:i:s'))
			 ->setLastModified(date('Y-m-d H:i:s'));
	$rubricsDao->addNewRubric($rubric);
	// Get the last inserted rubric id via DAO
	$rubricId = $rubricsDao->getLastInsertedRubricId();
	if ($rubricId) { 
		$rubric->setId($rubricId);
	}

	if (!empty($_POST['itemName']) && $rubricId !== null) {
		foreach ($_POST['itemName'] as $i => $name) {
			if(trim($name) === '') {
				continue; // Skip empty names
			}
			$desc = $_POST['itemDesc'][$i] ?? '';
			$commentRequired = $_POST['itemCommentRequired'][$i] ?? 'false';

			//? comments required false right now, should be changed in the future
			$rubricsDao->createRubricItem($rubricId, $name, $desc, $commentRequired);
		}
	}
	$message = 'Rubric created!';
	//Redirects to new rubric (either copy or entirely new)
	echo "<script>window.location.href = '?rubricId={$rubricId}';</script>";
	exit;
} elseif ($action === 'update') {

	$rubricId = $_POST['rubricId'];
	$rubric = $rubricsDao->getRubricById($rubricId);
	$rubric->setName($_POST['rubricName'] ?? '')
			 ->setLastModified(date('Y-m-d H:i:s'));
	$rubricsDao->updateRubric($rubric);
	// Update items
	$existingItems = $rubric->getItems();
	$existingIds = array_map(function($item){ return $item->getId(); }, $existingItems);
	$submittedIds = $_POST['itemId'] ?? [];
	// Delete removed items
	foreach ($existingIds as $eid) {
		if (!in_array($eid, $submittedIds)) {
			$rubricsDao->deleteRubricItem($eid);
		}
	}
	// Add/update items
	if (!empty($_POST['itemName'])) {
		foreach ($_POST['itemName'] as $i => $name) {
			$desc = $_POST['itemDesc'][$i] ?? '';
			$commentRequired = $_POST['itemCommentRequired'][$i] ?? 'false';
			$id = $_POST['itemId'][$i] ?? null;
			if ($id && in_array($id, $existingIds)) {
				// Update
				$item = new RubricItem($id);
				$item->setFkRubricId($rubricId)
					 ->setName($name)
					 ->setDescription($desc)
					 ->setCommentRequired($commentRequired);
				$rubricsDao->updateRubricItem($item);
			} else {
				// New
				$rubricsDao->createRubricItem($rubricId, $name, $desc, $commentRequired);
			}
		}
	}
	$message = 'Rubric updated!<BR>'. print_r($_POST, true);;
}
*/

$rubricSelectorHTML = '
	<form method="GET" class="mb-4">
        <label for="rubricId" class="form-label">Select Rubric to Edit or Copy:</label>
        <select name="rubricId" id="rubricId" class="form-select" onchange="this.form.submit()">
            <option value="">-- Create New Rubric --</option>';
            foreach ($rubrics as $rubric){
                $rubricSelectorHTML .= '<option value="'.$rubric->getId().'">'. htmlspecialchars($rubric->getName()).'</option>';
            }
$rubricSelectorHTML .= '</select>
    </form>
';



include_once PUBLIC_FILES . '/modules/header.php';

?>


<div class="container mt-4">
    <h2>Rubric Management</h2>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
	<?php echo $rubricSelectorHTML; ?>

    <form method="POST" class="mb-5" id = "rubricForm">
        <input type="hidden" name="action" value="<?php echo $selectedRubric ? 'update' : 'create'; ?>">
        <?php if ($selectedRubric): ?>
            <input type="hidden" name="rubricId" id="rubricId" value="<?php echo $selectedRubric->getId(); ?>">
        <?php endif; ?>
        <div class="mb-3">
            <label for="rubricName" class="form-label">Rubric Name</label>
            <input type="text" class="form-control" id="rubricName" maxlength = "255" name="rubricName" required value="<?php echo $selectedRubric ? htmlspecialchars($selectedRubric->getName()) : ''; ?>">
        </div>
        <div id="itemsContainer" class = "mt-3 mb-4 ">
            <h5>Rubric Items</h5>

            <?php 
                
                    foreach ($rubricItemsHTML as $item) {
						echo $item;
                    }
           
            ?>
        </div>
            <!-- hidden template row that renders a item so that javascript can copy this renered html around-->
        <template id="templateRow">
		<div class="row mt-4 rubric-item-row align-items-start"> From the template </div>
        </template>


        <button type="button" class="btn btn-secondary mb-3" id="addItemBtn">Add Item</button>
        <div>
            <button type="submit" class="btn btn-primary">Save Rubric</button>
            <?php if ($selectedRubric): ?>
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

    //Handles adding a row logic, including making names required and initializing CKEditor on the new textarea
    document.getElementById('addItemBtn').addEventListener('click', function() {
        // TODO: API call for adding item to database
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
	//TODO: Needs to be expanded to perform API calls for updating Rubric Items and associated Options
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
		if (e.target.classList.contains('btn-remove-option')) {
            // TODO: API call for removal from database
			console.log("Remove option btn clicked: " + e.target.dataset.optionid);
			parentDiv = e.target.parentElement;
			parentDiv.remove();	
        }
		
		if (e.target.classList.contains('btn-add-option')) {
            // TODO: Add the option to the DOM and API call for adding option to data associiated with correct Rubric Item
			console.log("Add option btn clicked: " + e.target.dataset.itemid);
			parentDiv = e.target.parentElement.parentElement;
			parentDiv.innerHTML += '<div class="col-md-2" >New Stuff</div>';
			
        }
    });

    // Run initialization for editors that came from server-render on DOMContentLoaded
    window.addEventListener("DOMContentLoaded", initializeCKEditors);
</script>

<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
