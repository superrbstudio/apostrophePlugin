<div class="a-chad"></div>

<?php use_helper('Url', 'jQuery', 'I18N') ?>

	<?php echo jq_form_remote_tag(
	  array(
	    'update' => "a-page-settings",
	    "url" => "a/settings",
			'complete' => '$(".a-page-overlay").hide();', 
	    "script" => true),
	  array(
	    "name" => "a-page-settings-form", 
	    "id" => "a-page-settings-form")) ?>

	<h3 id="a-page-settings-heading"><?php echo __('Page Settings', null, 'apostrophe') ?></h3>

	<?php // We need this to distinguish the original AJAX POST from an ?>
	<?php // actual form submit; we can't use a name attribute on the ?>
	<?php // submit tag because that doesn't work in jq_form_remote_tag ?>
  <input type="hidden" name="submit" value="1" />

	<?php echo $form->renderHiddenFields() ?>
	<?php echo $form->renderGlobalErrors() ?>

		<div id="a-page-settings-left">
			<?php if (isset($form['slug'])): ?>
			  <div class="a-form-row slug">
			    <label><?php echo __('Page Slug', null, 'apostrophe') ?></label>
			    <?php echo $form['slug'] ?>
			    <?php echo $form['slug']->renderError() ?>
			  </div>
			<?php endif ?>
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
	
  <div id="a-page-settings-right">
    <?php include_partial('a/allPrivileges', array('form' => $form, 'inherited' => $inherited, 'admin' => $admin)) ?>
  </div>

	<div class="a-form-row template" id="a-page-template">
	  <label><?php echo __('Page Template', null, 'apostrophe') ?></label>
	  <?php echo $form['template'] ?>
	  <?php echo $form['template']->renderError() ?>
	</div>
	
	<div class="a-form-row engine">
	  <label><?php echo __('Page Engine', null, 'apostrophe') ?></label>
	  <?php echo $form['engine']->render(array('onChange' => 'aUpdateEngineAndTemplate()')) ?>
	  <?php echo $form['engine']->renderError() ?>
	</div>
	<div id="a_settings_engine_settings">
	  <?php if (isset($engineSettingsPartial)): ?>
	    <?php include_partial($engineSettingsPartial, array('form' => $engineForm)) ?>
    <?php endif ?>
	</div>
	
	<ul id="a-page-settings-footer" class="a-controls a-page-settings-form-controls">
		<li>
		  <input type="submit" name="submit" value="<?php echo htmlspecialchars(__('Save Changes', null, 'apostrophe')) ?>" class="a-submit" id="a-page-settings-submit" />
		</li>
		<li>
			<?php echo jq_link_to_function(__('Cancel', null, 'apostrophe'), '
				$("#a-page-settings").slideUp(); 
				$("#a-page-settings-button-open").show(); 
				$("#a-page-settings-button-close").addClass("loading").hide()
				$(".a-page-overlay").hide();', 
				array(
					'class' => 'a-btn a-cancel', 
					'title' => 'cancel', 
				)) ?>
		</li>
		<?php if ($page->userHasPrivilege('manage')): ?>
		<li>
			<?php $childMessage = ''; ?>
			<?php if($page->hasChildren()): ?>
			<?php $childMessage = __("This page has children that will also be deleted. ", null, 'apostrophe'); ?>
			<?php endif; ?>
      <?php echo link_to(__("Delete This Page", null, 'apostrophe'), "a/delete?id=" . $page->getId(), array("confirm" => $childMessage . __('Are you sure? This operation can not be undone. Consider unpublishing the page instead.', null, 'apostrophe'), 'class' => 'a-btn icon a-delete')) ?>
    </li>
		<?php endif ?>
	</ul>

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

	<?php // you can do this: { linkTemplate: "<a href='#'>_LABEL_</a>",  ?>
	<?php //                    spanTemplate: "<span>_LINKS_</span>",     ?>
	<?php //                    betweenLinks: " " }                       ?>
	aRadioSelect('.a-radio-select', { });
	$('#a-page-settings').show();
	aUI();
</script>