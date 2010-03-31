<script type="text/javascript" charset="utf-8">
<?php // We're in an AJAX request, so we can't do a normal redirect call ?>
window.location = <?php echo json_encode($page->getUrl()) ?>;
</script>
