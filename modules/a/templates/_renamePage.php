<?php use_helper('jQuery', 'Url', 'I18N') ?>

<?php $page = aTools::getCurrentPage() ?>

<?php if ($edit): ?>

  <form method="POST" action="<?php echo url_for('a/rename') . '?' . http_build_query(array('id' => $page->id)) ?>" id="a-breadcrumb-rename-form" class="epc-form a-breadcrumb-form rename">

	<?php $form = new aRenameForm($page) ?>
	<?php echo $form->renderHiddenFields() ?>
	
	<?php echo $form['title']->render(array('id' => 'a-breadcrumb-rename-title')) ?>

	  <ul id="a-breadcrumb-rename-controls" class="a-form-controls a-breadcrumb-controls rename" style="display:none;">
			<li>
				<input type="submit" class="a-btn a-submit" value="<?php echo __('Rename', null, 'apostrophe') ?>" />							
			</li>
	  	<li>
				<?php echo jq_link_to_function(__("Cancel", null, 'apostrophe'), '', array('class' => 'a-btn a-cancel')) ?>
	  	</li>
	  </ul>

  </form>

	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {

			var renameForm = $('#a-breadcrumb-rename-form');
			renameForm.prepend('<b id="a-breadcrumb-rename-title-spacer" style="display:none;float:left;white-space:nowrap;">' + <?php echo json_encode(str_replace(' ','-',$page->getTitle())) ?> + '</b>');

			var renameControls = $('#a-breadcrumb-rename-controls');
			var renameSpacer = $('#a-breadcrumb-rename-title-spacer');
			var renameSubmitBtn = $('#a-breadcrumb-rename-submit');
			var renameInput = $('#a-breadcrumb-rename-title');
			var renameInputWidth = checkInputWidth(renameSpacer.width());		
			renameInput.css('width', renameInputWidth);		

      var currentTitle = renameInput[0].value;
			var liveTitle = renameInput[0].value;
			
			renameInput.bind('cancel', function(e){
				renameSpacer.text(cleanTitle(currentTitle));
				renameInput[0].value = currentTitle;
				renameInputWidth = checkInputWidth(renameSpacer.width());
				renameInput.css('width', renameInputWidth);
				renameControls.hide();
				renameInput.blur();
			});

			renameInput.focus(function(){
				renameControls.fadeIn();
				renameInput.select();
				$(this).stopTime();
			});

			renameInput.blur(function(){
				$(this).oneTime(250, "hide", function() {
					renameControls.hide();
				});
			});
			
			renameSubmitBtn.click(function(){
				renameInput.focus();
			});

			renameInput.keydown(function(e){
				liveTitle = renameInput[0].value;
				renameSpacer.text(cleanTitle(liveTitle));				
				renameInputWidth = checkInputWidth(renameSpacer.width());
				renameInput.css('width', renameInputWidth);
			});

			renameInput.keyup(function(e){
				if ($.charcode(e) == 'escape')
				{
					renameInput.trigger('cancel');
				}			
				renameInputWidth = checkInputWidth(renameSpacer.width());
				renameInput.css('width', renameInputWidth);
			});

			renameControls.find('a.a-cancel').click(function(){
				renameInput.trigger('cancel');
			});

			function checkInputWidth(w)
			{
				var minWidth = 20;
				var maxWidth = 250;
				if (w < minWidth)
				{
					return minWidth;
				} 
				else if (w > maxWidth)
				{
					// we are not enforcing maxWidth at the moment;
					// return maxWidth;
					return w+1;
				}
				else
				{
					return w+1;
				}
			}
			
			function cleanTitle(t)
			{
				return t.replace(/ /g,'-');
			}
		});
	</script>

<?php else: ?>

  <?php echo $page->getTitle() ?>

<?php endif ?>