<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>
<?php use_helper('a') ?>

<div class="a-ui a-signin-popup clearfix" id="a-signin">
  <form action="<?php echo url_for('@sf_guard_signin') ?>" method="post" id="a-signin-form" <?php echo ($form->hasErrors())? 'class="has-errors"':''; ?>>

		<div class="a-form-row a-hidden clearfix">
  		<?php echo $form->renderHiddenFields() ?>
		</div>

		<div class="a-form-row username clearfix">
    	<?php echo $form['username']->renderLabel() ?>
    	<?php echo $form['username']->render() ?>
    	<?php echo $form['username']->renderError() ?>
		</div>
		
		<div class="a-form-row password clearfix">		
    	<?php echo $form['password']->renderLabel() ?>
    	<?php echo $form['password']->render() ?>
    	<?php echo $form['password']->renderError() ?>
		</div>
		
		<div class="a-form-row submit clearfix">
			<ul class="a-ui a-controls">
				<li><?php echo a_anchor_submit_button(a_('Sign In'), array('big','a-show-busy')) ?></li>
				<li><?php echo a_js_button(a_('Cancel'), array('icon', 'a-cancel', 'a-login-cancel-button', 'big', 'alt')) ?></li>
			</ul>
		</div>
		
  </form>
</div>
