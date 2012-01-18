<?php // There is no placeholder for singleton slots because we often use singletons for handling content in non-standard places  ?>
<?php // Placeholder is sized automatically to the width of the column ?>
<?php // Clicking the placeholder serves the same purpose as clicking on 'Choose Images' ?>
<?php // The label text is centered vertically and horizontally within the box ?>

<?php if ($sf_user->isAuthenticated() && (isset($options['singleton']) != true)): ?>
	<a href="#<?php echo aTools::slugify($placeholderText) ?>" class="a-ui a-media-placeholder <?php echo (isset($clickToSelect) && ($clickToSelect === false)) ? '' : 'a-js-media-placeholder' ?>">
		<span><?php echo $placeholderText ?></span>
	</a>
<?php endif ?>