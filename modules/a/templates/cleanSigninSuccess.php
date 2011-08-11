<?php use_helper('a') ?>
<script type="text/javascript">
<?php // Break out of iframe or AJAX ?>
	top.location.href = "<?php echo a_url('a', 'cleanSigninPhase2') ?>";
</script>
<?php // Just in case of surprises ?>
<?php echo link_to(__("Click here to continue.", null, 'apostrophe'), "a/cleanSigninPhase2") ?>