<?php slot('body_class') ?>a-media<?php end_slot() ?>

<?php use_helper('jQuery') ?>

<div id="a-media-plugin">

<?php include_component('aMedia', 'browser') ?>

<div class="a-media-toolbar">
  <h3>Add Video</h3>
</div>

<div class="a-media-library">				

 	<ul class="a-controls" id="a-media-video-buttons">
		<li><?php echo link_to_function("Search YouTube", 
	  		"$('#a-media-video-search-form').show(); 
		 		 $('#a-media-video-buttons').hide(); 
		 		 $('#a-media-video-search-heading').show();", 
		 		 array("class" => "a-btn")) ?></li>
      
		<li><?php echo link_to_function("Add by YouTube URL", 
	      "$('#a-media-video-add-by-url-form').show(); 
			   $('#a-media-video-buttons').hide(); 
			 	 $('#a-media-video-add-by-url-heading').show();", 
		 		 array("class" => "a-btn")) ?></li>

		<?php if (aMediaTools::getOption('embed_codes')): ?>
	  <li><?php echo link_to_function("Add by Embed Code", 
	      "$('#a-media-video-add-by-embed-form').show(); 
			 	 $('#a-media-video-buttons').hide(); 
			 	 $('#a-media-video-add-by-embed-heading').show();", 
			 	 array("class" => "a-btn")) ?>
		</li>
	  <?php endif ?>

	  <li><?php echo link_to("Cancel", "aMedia/resumeWithPage", array("class" => "a-cancel a-btn icon event-default")) ?></li>
	</ul>

	<h4 id="a-media-video-search-heading" class="a-media-video-heading">Search YouTube</h4>     

    <?php echo jq_form_remote_tag(
			array(
        'url' => 'aMedia/videoSearch',
        'update' => 'a-media-video-search-form',
				'before' => '$("#a-media-video-search-form .a-search-field").append("<span class=\"a-spinner\"></span>");'), 
      array(
        'id' => 'a-media-video-search-form', 
				'class' => 'a-media-search-form', )) ?>

    				
				<?php if (0) {
					# form tag has a height of 20px. this is messing up the height causing the footer to crash into the results.
					# Need to rework the input background or the markup to allow this stuff to work properly
				} ?>
    	<?php include_partial('aMedia/videoSearch', array('form' => $videoSearchForm, 'results' => false)) ?>
    </form>

 		<h4 id="a-media-video-add-by-url-heading" class="a-media-video-heading">Add by URL</h4>   

    <form id="a-media-video-add-by-url-form" class="a-media-search-form" method="POST" action="<?php echo url_for("aMedia/editVideo") ?>">

			<div class="a-form-row a-search-field" style="position:relative">
        <label for="a-media-video-url"></label>
        <?php // Achieve CSRF compatibility, use the actual form object ?>
        <?php $form = new aMediaVideoYoutubeForm() ?>
        <?php echo $form->renderHiddenFields() ?>
        <?php echo $form['service_url']->render(array('class' => 'a-search-video a-search-form', 'id' => 'a-media-video-url')) ?>
			</div>

			<div class="a-form-row example">
        <p>Example: http://www.youtube.com/watch?v=EwTZ2xpQwpA</p>
        <input type="hidden" name="first_pass" value="1" /> 
			</div>

			<ul class="a-controls a-media-upload-form-footer" id="a-media-video-add-by-url-form-submit">
        <li><input type="submit" value="Go" class="a-submit" /></li>
        <li><?php echo link_to_function("Cancel", "$('#a-media-video-add-by-url-form').hide(); $('#a-media-video-add-by-url-heading').hide(); $('#a-media-video-buttons').show();", array("class" => "a-cancel a-btn icon event-default")) ?></li>
      </ul>
		
     </form>
		
		<?php if (aMediaTools::getOption('embed_codes')): ?>
			<h4 id="a-media-video-add-by-embed-heading" class="a-media-video-heading">Add by Embed Code</h4>
			
			<form id="a-media-video-add-by-embed-form" class="a-media-search-form" method="POST" action="<?php echo url_for("aMedia/editVideo") ?>">

			<div class="a-form-row a-search-field" style="position:relative">
        <label for="a-media-video-embed"></label>
        <?php $form = new aMediaVideoEmbedForm() ?>
        <?php echo $form->renderHiddenFields() ?>
        <?php echo $form['embed']->render(array('class' => 'a-search-video a-search-form', 'id' => 'a-media-video-embed')) ?>
			</div>

			<div class="a-form-row example">
        <p>Example: <?php echo htmlspecialchars('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="437" height="291" ...</object>') ?></p>
        <input type="hidden" name="first_pass" value="1" /> 
			</div>
			

			<ul class="a-controls a-media-upload-form-footer" id="a-media-video-add-by-embed-form-submit">
        <li><input type="submit" value="Go" class="a-submit" /></li>
        <li>
					<?php echo link_to_function("Cancel", 
					"$('#a-media-video-add-by-embed-form').hide(); 
					 $('#a-media-video-add-by-embed-heading').hide(); 
					 $('#a-media-video-buttons').show();", 
					 array("class" => "a-cancel a-btn icon event-default")) ?>
				</li>
      </ul>
			
     </form>
     <?php endif ?>

			<div id="a-media-video-search-results-container">I want search results displayed here</div>
</div>

</div>

<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {
			aInputSelfLabel('#videoSearch_q', <?php echo json_encode(isset($search) ? $search : 'Search') ?>);
			aInputSelfLabel('#a-media-video-url', <?php echo json_encode(isset($search) ? $search : 'http://') ?>);
			aInputSelfLabel('#a-media-video-embed', <?php echo json_encode(isset($search) ? $search : '<object>...</object>') ?>);			
	});
	
</script>