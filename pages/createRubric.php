<?php
include_once '../bootstrap.php';
use DataAccess\RubricsDao;
use Model\RubricTemplate;
use Model\RubricItemTemplate; // Fix: Use correct class name for rubric template items

$rubricsDao = new RubricsDao($dbConn, $logger);

// Handle form submissions (add/edit/delete/copy)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create' || $action === 'copy') {
        $template = new RubricTemplate();
        $template->setName($_POST['rubricName'] ?? '')
                 ->setLastUsed(date('Y-m-d H:i:s'))
                 ->setLastModified(date('Y-m-d H:i:s'));
        $rubricsDao->addNewRubricTemplate($template);
        // Get the last inserted template id via DAO
        $templateId = $rubricsDao->getLastInsertedRubricTemplateId();
        if ($templateId) {
            $template->setId($templateId);
        }
        $allowedTypes = ['number', 'boolean', 'text', 'option'];
        if (!empty($_POST['itemName']) && $templateId !== null) {
            foreach ($_POST['itemName'] as $i => $name) {
                $desc = $_POST['itemDesc'][$i] ?? '';
                $type = $_POST['itemType'][$i] ?? '';
                if (!in_array($type, $allowedTypes)) {
                    $type = $allowedTypes[0]; // fallback to ''
                }
                $rubricsDao->createRubricTemplateItem($templateId, $name, $desc, $type);
            }
        }
        $message = 'Rubric template created!';
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

$allowedTypes = ['number', 'boolean', 'text', 'option'];
$templates = $rubricsDao->getAllRubricTemplates();
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
$selectedTemplate = null;
if (isset($_GET['templateId'])) {
    $selectedTemplate = $rubricsDao->getRubricTemplateById($_GET['templateId']);
    if ($logger && $selectedTemplate) {
        $logger->info('Selected Template ID: ' . $selectedTemplate->getId());
    }
}

include_once PUBLIC_FILES . '/modules/header.php';
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

    <form method="POST" class="mb-5">
        <input type="hidden" name="action" value="<?php echo $selectedTemplate ? 'update' : 'create'; ?>">
        <?php if ($selectedTemplate): ?>
            <input type="hidden" name="templateId" value="<?php echo $selectedTemplate->getId(); ?>">
        <?php endif; ?>
        <div class="mb-3">
            <label for="rubricName" class="form-label">Rubric Name</label>
            <input type="text" class="form-control" id="rubricName" name="rubricName" required value="<?php echo $selectedTemplate ? htmlspecialchars($selectedTemplate->getName()) : ''; ?>">
        </div>
        <h5>Rubric Items</h5>
        <div id="itemsContainer">
            <?php 
            $items = $selectedTemplate ? ($selectedTemplate->items ?? []) : [];
            if (empty($items)) {
                echo '<div class="row mb-2 rubric-item-row">
                    <div class="col-md-3"><input type="text" name="itemName[]" class="form-control" placeholder="Item Name" required></div>
                    <div class="col-md-5"><input type="text" name="itemDesc[]" class="form-control" placeholder="Description"></div>
                    <div class="col-md-3">
                        <select name="itemType[]" class="form-select" required>';
                foreach ($allowedTypes as $type) {
                    echo '<option value="' . htmlspecialchars($type) . '">' . htmlspecialchars($type) . '</option>';
                }
                echo '</select>
                    </div>
                    <div class="col-md-1"><button type="button" class="btn btn-danger btn-remove-item">&times;</button></div>
                </div>';
            } else {
                foreach ($items as $item) {
                    echo '<div class="row mb-2 rubric-item-row">
                        <input type="hidden" name="itemId[]" value="' . $item->getId() . '">
                        <div class="col-md-3"><input type="text" name="itemName[]" class="form-control" value="' . htmlspecialchars($item->getName()) . '" required></div>
                        <div class="col-md-5"><input type="text" name="itemDesc[]" class="form-control" value="' . htmlspecialchars($item->getDescription()) . '"></div>
                        <div class="col-md-3">
                            <select name="itemType[]" class="form-select" required>';
                    foreach ($allowedTypes as $type) {
                        $selected = ($item->getAnswerType() == $type) ? ' selected' : '';
                        echo '<option value="' . htmlspecialchars($type) . '"' . $selected . '>' . htmlspecialchars($type) . '</option>';
                    }
                    echo '</select>
                        </div>
                        <div class="col-md-1"><button type="button" class="btn btn-danger btn-remove-item">&times;</button></div>
                    </div>';
                }
            }
            ?>
        </div>
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
// Use PHP to output the allowedTypes array for JS
var allowedTypes = <?php echo json_encode($allowedTypes); ?>;
document.getElementById('addItemBtn').addEventListener('click', function() {
    var container = document.getElementById('itemsContainer');
    var row = document.createElement('div');
    row.className = 'row mb-2 rubric-item-row';
    var optionsHtml = '';
    for (var i = 0; i < allowedTypes.length; i++) {
        optionsHtml += '<option value="' + allowedTypes[i] + '">' + allowedTypes[i] + '</option>';
    }
    row.innerHTML = `
        <div class="col-md-3"><input type="text" name="itemName[]" class="form-control" placeholder="Item Name" required></div>
        <div class="col-md-5"><input type="text" name="itemDesc[]" class="form-control" placeholder="Description"></div>
        <div class="col-md-3">
            <select name="itemType[]" class="form-select" required>
                ${optionsHtml}
            </select>
        </div>
        <div class="col-md-1"><button type="button" class="btn btn-danger btn-remove-item">&times;</button></div>
    `;
    container.appendChild(row);
});
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-remove-item')) {
        e.target.closest('.rubric-item-row').remove();
    }
});
</script>
<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
