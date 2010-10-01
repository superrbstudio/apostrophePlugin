<?php
  // Compatible with sf_escaping_strategy: true
  $search = isset($search) ? $sf_data->getRaw('search') : null;
  $videoSearchForm = isset($videoSearchForm) ? $sf_data->getRaw('videoSearchForm') : null;
  $service = isset($service) ? $sf_data->getRaw('service') : null;
?>
<?php use_helper('jQuery', 'I18N','a') ?>
<?php slot('body_class') ?>a-media<?php end_slot() ?>

<div class="a-media-library">

	<?php include_component('aMedia', 'browser') ?>

	<div class="a-media-toolbar">
	  <h3><?php echo __('Add Video', null, 'apostrophe') ?></h3>
	</div>

	<div class="a-media-items">				
	 	<ul class="a-ui a-controls" id="a-media-video-buttons">
	 	  
	 	  <li><?php echo link_to_function(__("Search YouTube", null, 'apostrophe'), 
		  		"$('#a-media-video-search-form').show(); 
			 		 $('#a-media-video-buttons').hide(); 
			 		 $('#a-media-video-search-heading').show();", 
			 		 array("class" => "a-btn")) ?></li>
      
			<li><?php echo link_to_function(__("Add by YouTube URL", null, 'apostrophe'), 
		      "$('#a-media-video-add-by-url-form').show(); 
				   $('#a-media-video-buttons').hide(); 
				 	 $('#a-media-video-add-by-url-heading').show();", 
			 		 array("class" => "a-btn")) ?></li>

			<?php if (aMediaTools::getOption('embed_codes')): ?>
		  <li><?php echo link_to_function(__("Add by Embed Code", null, 'apostrophe'), 
		      "$('#a-media-video-add-by-embed-form').show(); 
				 	 $('#a-media-video-buttons').hide(); 
				 	 $('#a-media-video-add-by-embed-heading').show();", 
				 	 array("class" => "a-btn")) ?>
			</li>
		  <?php endif ?>

		  <li><?php echo link_to(__("Cancel", null, 'apostrophe'), "aMedia/resumeWithPage", array("class" => "a-btn icon a-cancel")) ?></li>
		</ul>

	<h4 id="a-media-video-search-heading" class="a-media-video-heading"><?php echo __('Search YouTube', null, 'apostrophe') ?></h4>     

    <?php echo jq_form_remote_tag(
			array(
        'url' => 'aMedia/videoSearch',
        'update' => 'a-media-video-search-results-container',
        'complete' => '$("#a-media-video-search-results-container").show()',
				'before' => '$("#a-media-video-search-form .a-search-field").append("<span class=\"a-spinner\"></span>");'), 
      array(
        'id' => 'a-media-video-search-form', 
				'class' => 'a-media-search-form', )) ?>
			<?php // We don't ever re-present this form, so why use a separate partial anymore ?>
      <?php echo $videoSearchForm ?>
      
      <ul class="a-controls">
        <li><input type="submit" value="<?php echo __('Go', null, 'apostrophe') ?>" class="a-submit" /></li>
      	<li>
      		<?php echo link_to_function(__("Cancel", null, 'apostrophe'), 
      			"$('#a-media-video-search-form').hide(); 
      			 $('#a-media-video-search-results-container').hide(); 
      			 $('#a-media-video-search-heading').hide(); 
      			 $('#a-media-video-buttons').show();", 
      			array("class" => "a-btn a-cancel")) ?>
      	</li>
      </ul>
    </form>

 		<h4 id="a-media-video-add-by-url-heading" class="a-media-video-heading"><?php echo __('Add by URL', null, 'apostrophe') ?></h4>   

    <form id="a-media-video-add-by-url-form" class="a-media-search-form" method="POST" action="<?php echo url_for("aMedia/editVideo") ?>">

			<div class="a-form-row a-search-field" style="position:relative">
        <label for="a-media-video-url"></label>
        <?php // Achieve CSRF compatibility, use the actual form object ?>
        <?php $form = new aMediaVideoYoutubeForm() ?>
        <?php echo $form->renderHiddenFields() ?>
        <?php echo $form['service_url']->render(array('class' => 'a-search-video a-search-form', 'id' => 'a-media-video-url')) ?>
			</div>

			<div class="a-form-row example">
        <p><?php echo __('Example: http://www.youtube.com/watch?v=EwTZ2xpQwpA', null, 'apostrophe') ?></p>
        <input type="hidden" name="first_pass" value="1" /> 
			</div>

			<ul class="a-ui a-controls" id="a-media-video-add-by-url-form-submit">
        <li><input type="submit" value="<?php echo __('Go', null, 'apostrophe') ?>" class="a-btn a-submit" /></li>
        <li><?php echo link_to_function(__("Cancel", null, 'apostrophe'), "$('#a-media-video-add-by-url-form').hide(); $('#a-media-video-add-by-url-heading').hide(); $('#a-media-video-buttons').show();", array("class" => "a-btn icon a-cancel")) ?></li>
      </ul>
		
     </form>
		
		<?php if (aMediaTools::getOption('embed_codes')): ?>
			<h4 id="a-media-video-add-by-embed-heading" class="a-media-video-heading"><?php echo __('Add by Embed Code', null, 'apostrophe') ?></h4>
			
			<form id="a-media-video-add-by-embed-form" class="a-media-search-form" method="POST" action="<?php echo url_for("aMedia/editVideo") ?>">

			<div class="a-form-row a-search-field" style="position:relative">
        <label for="a-media-video-embed"></label>
        <?php $form = new aMediaVideoEmbedForm() ?>
        <?php echo $form->renderHiddenFields() ?>
        <?php echo $form['embed']->render(array('class' => 'a-search-video a-search-form', 'id' => 'a-media-video-embed')) ?>
			</div>

			<div class="a-form-row example">
        <p><?php echo __('Example: %embed%', array('%embed%' => htmlspecialchars('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="437" height="291" ...</object>')), 'apostrophe') ?></p>
        <input type="hidden" name="first_pass" value="1" /> 
			</div>
			

			<ul class="a-ui a-controls" id="a-media-video-add-by-embed-form-submit">
        <li><input type="submit" value="<?php echo __('Go', null, 'apostrophe') ?>" class="a-btn a-submit" /></li>
        <li>
					<?php echo link_to_function(__("Cancel", null, 'apostrophe'), 
					"$('#a-media-video-add-by-embed-form').hide(); 
					 $('#a-media-video-add-by-embed-heading').hide(); 
					 $('#a-media-video-buttons').show();", 
					 array("class" => "a-btn icon a-cancel")) ?>
				</li>
      </ul>
			
     </form>
     <?php endif ?>

			<div id="a-media-video-search-results-container">Placeholder text</div>
	</div>
</div>

<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {
		aInputSelfLabel('#videoSearch_q', <?php echo json_encode(isset($search) ? $search : __('Search', null, 'apostrophe')) ?>);
		aInputSelfLabel('#a-media-video-url', <?php echo json_encode(isset($search) ? $search : 'http://') ?>);
		aInputSelfLabel('#a-media-video-embed', <?php echo json_encode(isset($search) ? $search : '<object>...</object>') ?>);			
	});
</script>