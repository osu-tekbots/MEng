<?php
include_once '../bootstrap.php';

use DataAccess\FaqDao;

$dao = new FaqDao($dbConn, $logger);

// Determine if we're editing an existing FAQ
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = "";
}

$faq = $dao->getFaqById($id);

if ($faq) {
    $category = $faq->getCategory();
    $question = $faq->getQuestion();
    $answer = $faq->getAnswer();
} else {
    $category = "";
    $question = "";
    $answer = "";
}

// Fetch all FAQs for the listing table
$allFaqs = $dao->getAllFaqs();
if (!$allFaqs) $allFaqs = [];

// Available page categories for the dropdown
$categories = array(
    'assignReviews.php',
    'createRubric.php',
    'evaluateRubrics.php',
    'evaluationUpload.php',
    'index.php',
    'profile.php',
    'reviewerAssignments.php',
    'studentUpload.php',
    'userList.php',
    'viewReviews.php',
    'viewUploads.php',
    'createFaq.php'
);

$title = 'Manage FAQs';
include_once PUBLIC_FILES . '/modules/header.php';
?>

<div class="container-fluid">
    <div class="container mt-5 mb-5">
        
        <div class="row mb-4">
            <div class="col">
                <h2><?php echo ($faq ? 'Edit FAQ' : 'Create New FAQ'); ?></h2>
                <p class="text-muted">Use this form to create or edit a frequently asked question. FAQs are shown on pages based on their category.</p>
            </div>
        </div>

        <!-- FAQ Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><?php echo ($faq ? 'Edit FAQ #' . htmlspecialchars($id) : 'New FAQ'); ?></h5>
            </div>
            <div class="card-body">
                <form id="faqForm">

                    <div class="mb-3">
                        <label for="faqCategory" class="form-label"><strong>Category (Page):</strong></label>
                        <select name="category" id="faqCategory" class="form-select">
                            <option value="">-- Select a Page --</option>
                            <?php 
                            foreach ($categories as $cat) {
                                $selected = ($category == $cat) ? 'selected' : '';
                                echo '<option ' . $selected . ' value="' . htmlspecialchars($cat) . '">' . htmlspecialchars($cat) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="faqQuestion" class="form-label"><strong>Question:</strong></label>
                        <textarea class="form-control" rows="3" name="question" id="faqQuestion"><?php echo htmlspecialchars($question); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="faqAnswer" class="form-label"><strong>Answer:</strong></label>
                        <textarea class="form-control" rows="6" name="answer" id="faqAnswer"><?php echo htmlspecialchars($answer); ?></textarea>
                    </div>

                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

                    <div class="d-flex gap-2">
                        <?php if ($faq): ?>
                            <button type="button" class="btn btn-primary" onclick="onUpdateFaqClick();">
                                <i class="fas fa-save"></i> Update FAQ
                            </button>
                            <button type="button" class="btn btn-danger" onclick="onDeleteFaqClick();">
                                <i class="fas fa-trash"></i> Delete FAQ
                            </button>
                            <a href="createFaq" class="btn btn-secondary">
                                <i class="fas fa-plus"></i> Create New Instead
                            </a>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary" onclick="onCreateFaqClick();">
                                <i class="fas fa-plus"></i> Add New FAQ
                            </button>
                        <?php endif; ?>
                    </div>

                </form>
            </div>
        </div>

        <!-- All FAQs Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">All FAQs</h5>
            </div>
            <div class="card-body">
                <?php if (count($allFaqs) > 0): ?>
                <table id="faqsTable" class="table table-striped table-hover table-bordered w-100">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Category</th>
                            <th scope="col">Question</th>
                            <th scope="col" class="text-center" style="width: 100px;">Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allFaqs as $f): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($f->getId()); ?></td>
                            <td><?php echo htmlspecialchars($f->getCategory()); ?></td>
                            <td><?php echo htmlspecialchars($f->getQuestion()); ?></td>
                            <td class="text-center">
                                <a href="createFaq?id=<?php echo htmlspecialchars($f->getId()); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="text-muted mb-0">No FAQs have been created yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
    // Initialize DataTable on the FAQs listing
    if (document.getElementById('faqsTable')) {
        new DataTable('#faqsTable', {
            order: [[1, 'asc']]
        });
    }

    function onCreateFaqClick() {
        let data = serializeFormAsJson('faqForm');
        data.action = 'createFaq';

        api.post('/faqs.php', data)
            .then(res => {
                snackbar(res.message, 'success');
                setTimeout(() => { window.location.href = 'createFaq'; }, 1500);
            })
            .catch(err => {
                snackbar(err.message || 'Failed to create FAQ', 'error');
            });
    }

    function onUpdateFaqClick() {
        let data = serializeFormAsJson('faqForm');
        data.action = 'updateFaq';

        api.post('/faqs.php', data)
            .then(res => {
                snackbar(res.message, 'success');
                setTimeout(() => { window.location.reload(); }, 1500);
            })
            .catch(err => {
                snackbar(err.message || 'Failed to update FAQ', 'error');
            });
    }

    function onDeleteFaqClick() {
        if (!confirm('Are you sure you want to delete this FAQ?')) return;

        let data = serializeFormAsJson('faqForm');
        data.action = 'deleteFaq';

        api.post('/faqs.php', data)
            .then(res => {
                snackbar(res.message, 'success');
                setTimeout(() => { window.location.href = 'createFaq'; }, 1500);
            })
            .catch(err => {
                snackbar(err.message || 'Failed to delete FAQ', 'error');
            });
    }
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
