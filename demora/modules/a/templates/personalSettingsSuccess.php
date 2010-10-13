<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>
<?php use_helper('I18N') ?>

<?php use_helper('Url', 'jQuery') ?>

	<?php echo jq_form_remote_tag(
	  array(
	    'update' => "a-personal-settings",
	    "url" => "a/personalSettings",
			'complete' => '$(".a-page-overlay").hide();', 
	    "script" => true),
	  array(
	    "name" => "a-personal-settings-form", 
	    "id" => "a-personal-settings-form")) ?>

	<h3 id="a-personal-settings-heading"><?php echo __('User Preferences for %name%', array('%name%' => "<span>" . $sf_user->getGuardUser()->getUsername() . "</span>"), 'apostrophe') ?></h3>

	<?php // We need this to distinguish the original AJAX POST from an ?>
	<?php // actual form submit; we can't use a name attribute on the ?>
	<?php // submit tag because that doesn't work in jq_form_remote_tag ?>
  <input type="hidden" name="submit" value="1" />

	<?php echo $form ?>
	
	<ul class="a-ui a-controls" id="a-personal-settings-footer">
		<li>
		  <input type="submit" name="a-personal-settings-submit" value="<?php echo htmlspecialchars(__('Save Changes', null, 'apostrophe')) ?>" id="a-personal-settings-submit" class="a-btn a-submit" />
		</li>
		<li>
			<?php echo jq_link_to_function(__('Cancel', null, 'apostrophe'), '
				$("#a-personal-settings").slideUp(); 
				$("#a-personal-settings-button-open").show(); 
				$("#a-personal-settings-button-close").addClass("loading").hide()
				$(".a-page-overlay").hide();', 
				array(
					'class' => 'a-btn icon a-cancel', 
					'title' => 'cancel', 
				)) ?>
		</li>
	</ul>

</form>

<script type="text/javascript" charset="utf-8">
	$('#a-personal-settings').show();
</script>