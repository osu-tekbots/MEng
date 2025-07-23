</main>
<?php 
if(!isset($contentOnly) || !$contentOnly): 
?>
<a href="https://docs.google.com/forms/d/e/1FAIpQLSdK6-dYdAUel_5yGWeJWiO7ptoXFscGZzRHhRI4FY7I1BDRog/viewform?" target="_blank" class="btn" type="button" style="position: fixed; right: 20px; bottom: 20px; z-index: 1001; background: rgb(195, 69, 0); color: #fff; font-size: 1rem; padding: .375rem .75rem; border-radius: 0.25rem; font-weight: normal;">
    <i class="far fa-comment mr-1"></i>
    Feedback
</a>
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