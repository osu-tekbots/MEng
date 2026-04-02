</main>
<?php 
if(!isset($contentOnly) || !$contentOnly): 
?>
<button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#faqModal" style="position: fixed; right: 20px; bottom: 20px; z-index: 1001; background: rgb(195, 69, 0); color: #fff; font-size: 1rem; padding: .375rem .75rem; border-radius: 0.25rem; font-weight: normal;">
    <i class="fas fa-question-circle me-1"></i>
    FAQ
</button>
<?php include_once PUBLIC_FILES . '/modules/faq-modal.php'; ?>
<footer>
    &copy;&nbsp;<?php echo date('Y'); ?> Oregon State University&nbsp;&nbsp;&nbsp;
    <a class="disclaimer" href="https://oregonstate.edu/official-web-disclaimer" target="_blank">Disclaimer</a>
</footer>
<?php
endif;
?>
</body>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });
</script>
</html>