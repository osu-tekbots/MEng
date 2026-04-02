
<?php
/**
 * FAQ Modal Module
 * 
 * This file renders an empty Bootstrap modal shell. When the FAQ button is clicked,
 * JavaScript fetches the relevant FAQs from the API based on the current page.
 * No database calls are made until the user actually opens the modal.
 * 
 * Include this file once in the footer.
 */
$faqIsLoggedIn = isset($isLoggedIn) && $isLoggedIn;
?>

<!-- FAQ Modal -->
<div class="modal fade" id="faqModal" tabindex="-1" aria-labelledby="faqModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="faqModalLabel">
                    <i class="fas fa-question-circle me-2"></i>Frequently Asked Questions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search Bar -->
                <div class="mb-3">
                    <input 
                        type="text" 
                        class="form-control" 
                        id="faqSearchInput" 
                        placeholder="Search FAQs..."
                        autocomplete="off"
                    >
                </div>
                <div id="faqNoResults" class="text-muted text-center py-3 d-none">
                    No FAQs match your search.
                </div>
                <!-- FAQ content loaded here dynamically -->
                <div id="faqModalContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    #faqAccordion .accordion-button:not(.collapsed) {
        color: #0d6efd;
        background-color: #e7f1ff;
        box-shadow: none;
    }
    #faqAccordion .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(13, 110, 253, 0.25);
    }
    .faq-edit-btn {
        flex-shrink: 0;
    }
</style>

<script>
(function() {
    const faqIsLoggedIn = <?php echo $faqIsLoggedIn ? 'true' : 'false'; ?>;
    let faqsLoaded = false;
    let faqData = [];

    // Detect current page filename
    function getCurrentPage() {
        const path = window.location.pathname;
        const parts = path.split('/');
        let page = parts[parts.length - 1] || 'index.php';
        // The rewrite engine strips .php, so add it back if missing
        if (page && !page.includes('.')) {
            page += '.php';
        }
        return page;
    }

    // Build accordion HTML from FAQ data
    function renderFaqs(faqs) {
        if (faqs.length === 0) {
            return '<p class="text-muted text-center mb-0">No FAQs available for this page.</p>';
        }

        let html = '<div class="accordion" id="faqAccordion">';
        faqs.forEach(function(faq) {
            const collapseId = 'faqCollapse' + faq.id;
            const headingId = 'faqHeading' + faq.id;
            const escapedQuestion = escapeHtml(faq.question);
            const escapedAnswer = escapeHtml(faq.answer).replace(/\n/g, '<br>');

            let editBtn = '';
            if (faqIsLoggedIn) {
                editBtn = '<a href="createFaq?id=' + faq.id + '" ' +
                    'class="btn btn-sm btn-outline-secondary ms-2 me-2 faq-edit-btn" ' +
                    'title="Edit this FAQ" style="white-space: nowrap;">' +
                    '<i class="fas fa-edit"></i></a>';
            }

            html += '<div class="accordion-item faq-accordion-item">' +
                '<h2 class="accordion-header" id="' + headingId + '">' +
                    '<div class="d-flex align-items-center w-100">' +
                        '<button class="accordion-button collapsed flex-grow-1" type="button" ' +
                            'data-bs-toggle="collapse" data-bs-target="#' + collapseId + '" ' +
                            'aria-expanded="false" aria-controls="' + collapseId + '" ' +
                            'style="color: #0d6efd; font-weight: 500;">' +
                            escapedQuestion +
                        '</button>' +
                        editBtn +
                    '</div>' +
                '</h2>' +
                '<div id="' + collapseId + '" class="accordion-collapse collapse" ' +
                    'aria-labelledby="' + headingId + '" data-bs-parent="#faqAccordion">' +
                    '<div class="accordion-body">' + escapedAnswer + '</div>' +
                '</div>' +
            '</div>';
        });
        html += '</div>';
        return html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text || ''));
        return div.innerHTML;
    }

    // Fetch FAQs from API when modal opens
    document.getElementById('faqModal').addEventListener('show.bs.modal', function() {
        if (faqsLoaded) return;

        const category = getCurrentPage();
        api.post('/faqs.php', { action: 'getFaqsByCategory', category: category })
            .then(function(res) {
                faqData = res.content || [];
                document.getElementById('faqModalContent').innerHTML = renderFaqs(faqData);
                faqsLoaded = true;

                // Hide search bar if no FAQs
                if (faqData.length === 0) {
                    document.getElementById('faqSearchInput').style.display = 'none';
                }
            })
            .catch(function(err) {
                document.getElementById('faqModalContent').innerHTML =
                    '<p class="text-danger text-center mb-0">Failed to load FAQs.</p>';
            });
    });

    // Search/filter
    document.getElementById('faqSearchInput').addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const items = document.querySelectorAll('.faq-accordion-item');
        let visibleCount = 0;

        items.forEach(function(item) {
            const question = item.querySelector('.accordion-button').textContent.toLowerCase();
            const answer = item.querySelector('.accordion-body').textContent.toLowerCase();

            if (question.includes(query) || answer.includes(query)) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        const noResults = document.getElementById('faqNoResults');
        if (visibleCount === 0 && query.length > 0) {
            noResults.classList.remove('d-none');
        } else {
            noResults.classList.add('d-none');
        }
    });
})();
</script>
