<?php use_helper('jQuery', 'I18N') ?>

<div id="a-signin">
  <form action="<?php echo url_for('@sf_guard_signin') ?>" method="post" id="a-signin-form" <?php echo ($form->hasErrors())? 'class="has-errors"':''; ?>>

		<div class="a-form-row a-hidden">
  		<?php echo $form->renderHiddenFields() ?>
		</div>

		<div class="a-form-row">
    	<?php echo $form['username']->renderLabel() ?>
    	<?php echo $form['username']->render() ?>
    	<?php echo $form['username']->renderError() ?>
		</div>
		
		<div class="a-form-row">		
    	<?php echo $form['password']->renderLabel() ?>
    	<?php echo $form['password']->render() ?>
    	<?php echo $form['password']->renderError() ?>
		</div>

		<?php if (0): ?>
		<?php // When this thing starts working, it can get displayed. ?>
		<div class="a-form-row">
    	<?php echo $form['remember']->renderLabel() ?>
			<?php echo $form['remember']->render() ?>
			<?php echo $form['remember']->renderError() ?>
		</div>
		<?php endif ?>
		
		<ul class="a-form-row submit">
    	<li>
				<input type="submit" class="a-submit" value="<?php echo __('Sign In', null, 'apostrophe') ?>" />
			</li>
			<li>
				<?php echo jq_link_to_function(__('Cancel', null, 'apostrophe'), "$('#a-login-form-container').fadeOut('fast'); $('.a-page-overlay').fadeOut('fast');", array('class' => 'a-btn a-cancel', )) ?>
			</li>
		</ul>
		
  </form>
</div>
