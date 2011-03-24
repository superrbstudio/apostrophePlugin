<?php if (aMediaTools::userHasUploadPrivilege() && ($uploadAllowed || $embedAllowed)): ?>
	<div id="a-media-add" class="a-ui a-media-select a-media-add clearfix">
    <?php if ($uploadAllowed): ?>
      <?php include_partial('aMedia/uploadMultiple', array('form' => new aMediaUploadMultipleForm())) ?>    
    <?php endif ?>
    <?php if ($uploadAllowed && $embedAllowed): ?>
      <h2 class="a-media-or">OR</h2>
    <?php endif ?>
    <?php if ($embedAllowed): ?>
      <?php include_partial('aMedia/embed') ?>    
    <?php endif ?>
  </div>
  
  <?php if ($sf_params->get('add') || $sf_user->getFlash('aMedia.postMaxSizeExceeded')): ?>
    <?php // This is a validation error pass ?>
    <script type="text/javascript">
      $(function() {
        $('#a-media-add').show();
      });
    </script>
  <?php endif ?>
  <script type="text/javascript">
    $(function() {
      $('#a-media-add-button').click(function() {
        $('#a-media-add').show();
        return false;
      });
      $('#a-media-add .a-cancel').click(function() {
        $('#a-media-add').hide();
        return false;
      });
    });
  </script>
<?php endif ?>