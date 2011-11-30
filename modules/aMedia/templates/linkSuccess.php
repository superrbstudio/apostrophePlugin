<?php use_helper('a') ?>
<?php slot('body_class') ?>a-media<?php end_slot() ?>

<?php slot('a-page-header') ?>
	<?php include_partial('aMedia/mediaHeader', array('uploadAllowed' => $uploadAllowed, 'embedAllowed' => $embedAllowed)) ?>
<?php end_slot() ?>

<?php include_component('aMedia', 'browser') ?>
<div class="a-media-library">
	<div class="a-ui a-media-select clearfix">

	  <h3><?php echo a_('Linked Accounts') ?></h3>

    <p class="a-help">
      New videos and other media in linked accounts are automatically copied to your site's media repository every 20 minutes.
    </p>
    
		<form id="a-media-add-linked-account" method="post" action="<?php echo a_url('aMedia', 'linkAddAccount') ?>" class="a-ui a-media-services-form a-media-linked-accounts-form">
			<div class="a-form-row a-hidden">
				<?php echo $form->renderHiddenFields() ?>
			</div>		
			<div class="a-form-row service">
				<div class='a-form-field'>
					<?php echo $form['service']->render() ?>
				</div>
				<?php echo $form['service']->renderError() ?>
				<?php include_partial('aMedia/unconfiguredServices') ?>
			</div>
			<h4><?php echo a_('Add Linked Account') ?></h4>
			<div class="a-form-row username">
				<div class='a-form-field'>
					<?php echo $form['username']->render() ?>
				</div>
				<?php echo $form['username']->renderError() ?>
			</div>
		
		  <ul class="a-ui a-controls" id="a-media-video-add-by-embed-form-submit">
	      <li>
				<?php echo a_anchor_submit_button(a_('Preview'), array('icon','a-search','a-show-busy')) ?>
				</li>
	      <li>
	  			<?php echo link_to('<span class="icon"></span>'.a_("Cancel"), 'aMedia/resume', array("class" => "a-btn icon a-cancel alt")) ?>
	  		</li>
	    </ul>
		</form>
	
		<?php if (count($accounts)): ?>
		  <ul class="a-ui a-media-linked-accounts">
					<li><h5>Your Accounts</h5></li>
		    <?php foreach ($accounts as $account): ?>
		      <li>
		        <ul class="a-media-linked-account">
		          <li class="a-service a-<?php echo $account->service ?>"><?php echo $account->service ?></li>
		          <li class="a-account"><?php echo a_entities($account->username) ?></li>
		          <?php if (isset($form)): ?>
		            <li class="a-actions"><?php echo a_button(a_('Remove'), a_url('aMedia', 'linkRemoveAccount', array('id' => $account->id)), array('icon','a-close-small','no-label', 'no-bg'), null, null, 'Remove') ?></li>
		          <?php endif ?>
		        </ul>
		      </li>
		    <?php endforeach ?>
				<li class="a-help">
				  <?php echo a_('All new items in these accounts are automatically added to the media repository on a scheduled basis.') ?>
				</li>		
		  </ul>
		<?php endif ?>
	
		<div id="a-media-account-preview-wrapper"></div><?php // I am an AJAX target ?>
	
	</div>
</div>

<?php a_js_call('apostrophe.mediaEnableLinkAccount(?)', a_url('aMedia', 'linkPreviewAccount')) ?>
<?php a_js_call('apostrophe.selfLabel(?)', array('selector' => '#a_embed_media_account_username', 'title' => a_('Username'))) ?>