<?php use_helper('Url', 'jQuery', 'I18N') ?>

	<?php echo jq_form_remote_tag(
	  array(
	    'update' => 'a-page-settings',
	    'url' => 'a/settings',
			'complete' => '$(".a-page-overlay").hide();', 
	    'script' => true),
	  array(
	    'name' => 'a-page-settings-form', 
	    'id' => 'a-page-settings-form',
			'class' => 'dropshadow a-options', )) ?>

	<?php echo $form->renderHiddenFields() ?>
	<?php echo $form->renderGlobalErrors() ?>

	<div class="a-page-settings-section page-info">

		<div class="a-form-row title">
			<label><?php echo __('Page Title', null, 'apostrophe') ?></label>
			<?php echo $form['title']->render() ?>
			<?php echo $form['title']->renderError() ?>
		</div>

		<?php if (isset($form['slug'])): ?>
		  <div class="a-form-row slug">
		    <label><?php echo __('Page Slug', null, 'apostrophe') ?></label>
		    <?php echo $form['slug'] ?>
		    <?php echo $form['slug']->renderError() ?>
		  </div>
		<?php endif ?>

		<div class="a-form-row engine">
		  <label><?php echo __('Page Engine', null, 'apostrophe') ?></label>
		  <?php echo $form['engine']->render(array('onChange' => 'aUpdateEngineAndTemplate()')) ?>
		  <?php echo $form['engine']->renderError() ?>
		</div>

		<div class="a-form-row template">
		  <label><?php echo __('Page Template', null, 'apostrophe') ?></label>
		  <?php echo $form['template'] ?>
		  <?php echo $form['template']->renderError() ?>
		</div>

		<div id="a_settings_engine_settings">
		  <?php if (isset($engineSettingsPartial)): ?>
		    <?php include_partial($engineSettingsPartial, array('form' => $engineForm)) ?>
	    <?php endif ?>
		</div>

	</div>


	<div class="a-page-settings-section page-status">

		<div class="a-form-row status">
		  <label><?php echo __('Page Status', null, 'apostrophe') ?></label>
		  	<div class="a-page-settings-status">
			    <?php echo $form['archived'] ?>
          <?php if(isset($form['cascade_archived'])): ?>
            <?php // If you want your <em> back here, do it in the translation file ?>
            <?php echo $form['cascade_archived'] ?> <?php echo __('Cascade status changes to children', null, 'apostrophe') ?>
          <?php endif ?> 
				</div>
		</div>			

		<div class="a-form-row privacy">
		  <label><?php echo __('Page Privacy', null, 'apostrophe') ?></label>
		  	<div class="a-page-settings-status">
					<?php echo $form['view_is_secure'] ?>
					<?php if(isset($form['cascade_view_is_secure'])): ?>
              <?php echo $form['cascade_view_is_secure'] ?> <?php echo __('Cascade privacy changes to children', null, 'apostrophe') ?>
          <?php endif ?> 
				</div>
		</div>

	</div>

	<div class="a-page-settings-section page-permissions">
		<h4><?php echo __('Page Permissions', null, 'apostrophe') ?></h4>
	
		<div class="a-page-permissions">
		  <?php include_partial('a/privileges', 
		    array('form' => $form, 'widget' => 'editors',
		      'label' => 'Editors', 'inherited' => $inherited['edit'],
		      'admin' => $admin['edit'])) ?>
		  <?php include_partial('a/privileges', 
		    array('form' => $form, 'widget' => 'managers',
		      'label' => 'Managers', 'inherited' => $inherited['manage'],
		      'admin' => $admin['manage'])) ?>
			</div>
	</div>

	<div class="a-page-settings-section page-submit">
			  <input type="submit" name="submit" value="<?php echo htmlspecialchars(__('Save Changes', null, 'apostrophe')) ?>" class="a-submit" id="a-page-settings-submit" />
				<?php echo jq_link_to_function(__('Cancel', null, 'apostrophe'), '',  array('class' => 'a-btn a-cancel', 'title' => __('Cancel', null, 'apostrophe'))) ?>

			<?php if ($page->userHasPrivilege('manage')): ?>
				<?php $childMessage = ''; ?>
				<?php if($page->hasChildren()): ?><?php $childMessage = __("This page has children that will also be deleted. ", null, 'apostrophe'); ?><?php endif; ?>
	      <?php echo link_to(__("Delete This Page", null, 'apostrophe'), "a/delete?id=" . $page->getId(), array("confirm" => $childMessage . __('Are you sure? This operation can not be undone. Consider unpublishing the page instead.', null, 'apostrophe'), 'class' => 'a-btn icon a-delete nobg mini')) ?>
			<?php endif ?>
		</ul>
	</div>
	

</form>
<script type="text/javascript" charset="utf-8">
	function aUpdateEngineAndTemplate()
	{
	  var val = $('#a_settings_settings_engine').val();
	  if (!val.length)
	  {
	    // $('#a_settings_settings_template').attr('disabled',false); // Symfony doesn't like this.
			$('#a_settings_settings_template').siblings('div.a-overlay').remove();
	    $('#a_settings_engine_settings').html('');
	  }
	  else
	  {
			$('#a_settings_settings_template').siblings('div.a-overlay').remove();
			$('#a_settings_settings_template').before("<div class='a-overlay'></div>");
			$('#a_settings_settings_template').siblings('div.a-overlay').fadeTo(0,0.5).css('display','block');
	    // $('#a_settings_settings_template').attr('disabled','disabled'); // Symfony doesn't like this.
	    <?php // AJAX replace engine settings form as needed ?>
	    $.get(<?php echo json_encode(url_for('a/engineSettings')) ?>, { id: <?php echo $page->id ?>, engine: val }, function(data) {
  	    $('#a_settings_engine_settings').html(data);
	    });
	  }
	}
	aUpdateEngineAndTemplate();
	<?php // you can do this: { remove: 'custom html for remove button' } ?>
	$(function() { aMultipleSelect('#a-page-settings', { 'choose-one': <?php echo json_encode(__("Choose a User to Add", null, 'apostrophe')) ?>}) });

	<?php // you can do this: { linkTemplate: "<a href='#'>_LABEL_</a>",  ?>
	<?php //                    spanTemplate: "<span>_LINKS_</span>",     ?>
	<?php //                    betweenLinks: " " }                       ?>
	aRadioSelect('.a-radio-select', { });
	$('#a-page-settings').show();
	aUI();
</script>