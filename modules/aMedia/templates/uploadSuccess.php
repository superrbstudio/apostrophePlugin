<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>
<?php use_helper('I18N','jQuery','a') ?>
<?php slot('body_class') ?>a-media<?php end_slot() ?>

<div class="a-media-library">

<?php include_component('aMedia', 'browser') ?>

<div class="a-media-toolbar">
  
    <h3><?php echo a_('Upload ' . aMediaTools::getBestTypeLabel()) ?></h3>

    <?php echo $form->renderGlobalErrors() ?>

    <?php // Error message if they don't select anything at all. This is the only validation done in this pass ?>
    <?php if ($mustUploadSomething): ?>
      <h4>You must select a file to upload with the Browse Files button.</h4>
    <?php endif ?>
    
    <form method="POST" action="<?php echo url_for("aMedia/upload") ?>" enctype="multipart/form-data" id="a-media-upload-form">
      <?php echo $form->renderHiddenFields() ?>
    	
      <?php // I use this in js code, don't kill it please, style it if you want ?>
      <div id="a-media-upload-form-subforms">
        <?php for ($i = 0; ($i < aMediaTools::getOption('batch_max')); $i++): ?>
          <?php // What we're passing here is actually a widget schema ?>
          <?php // (they get nested when embedded forms are present), but ?>
          <?php // it supports the same methods as a form for rendering purposes ?>
          <?php include_partial('aMedia/upload', array("form" => $form["item-$i"], "first" => ($i == 0))) ?>
        <?php endfor ?>
      </div>

			<ul class="a-ui a-controls">
      	<li><a href="#" id="a-media-add-photo" class="a-btn icon a-add"><?php echo a_('Add Multiple Files') ?></a></li>
			</ul>
			
			<br class="c"/>
      
			<ul class="a-ui a-controls">
      	<li><?php echo link_to_function(a_('Upload ' . aMediaTools::getBestTypeLabel()), "$('#a-media-upload-form').submit()", array("class"=>"a-btn")) ?></li>
      	<li><?php echo link_to(a_('cancel'), "aMedia/resumeWithPage", array("class"=>"a-btn icon a-cancel")) ?></li>
      </ul>
    </form>

    <?php // Elements get moved here by jQuery when they are not in use. ?>
    <?php // This form is never submitted so file upload elements that are ?>
    <?php // in it are never uploaded. ?>

    <form style="display: none" action="#" enctype="multipart/form-data" id="a-media-upload-form-inactive"></form>
</div>

	<script type="text/javascript" charset="utf-8">
		$(function() {
	  <?php // Why don't I just do this once? Because I have to re-bind handlers ?>
	  <?php // to elements when I remove them and then re-add them elsewhere ?>
	  <?php // in the document. ?>
	  function aMediaUploadSetRemoveHandler(element)
	  {
	    $(element).find('.a-close').click(function() {
	        // Move the entire row to the inactive form
	        var element = $($(this).parent().parent().parent()).remove();
	        $('#a-media-upload-form-inactive').append(element);
	        $('#a-media-add-photo').show();
	        return false;
	      });
	  }
	  // Move the first inactive element back to the active form
	  $('#a-media-add-photo').click(function() {
	      var elements = $('#a-media-upload-form-inactive .a-form-row');
	        $('#a-media-upload-form-subforms').append(elements);
	        $('#a-media-add-photo').hide();
	      return false;
	    });
	  // Move all the initially inactive elements to the inactive form
	  function aMediaUploadInitialize()
	  {
	    $('#a-media-upload-form-inactive').append($('#a-media-upload-form-subforms .a-form-row.initially-inactive').remove());
	    aMediaUploadSetRemoveHandler($('#a-media-upload-form-subforms'));
	  }
	  aMediaUploadInitialize();
	});
	</script>
</div>