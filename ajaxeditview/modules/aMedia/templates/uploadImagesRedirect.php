<?php // Must use query_string so that in all projects we wind up with ?>
<?php // intact parameters ?>
<script type="text/javascript" charset="utf-8">
	window.parent.location = <?php echo json_encode(url_for("aMedia/editImages") . "?" . http_build_query($parameters)) ?>;
</script>
