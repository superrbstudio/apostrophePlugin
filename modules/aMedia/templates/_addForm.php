<style>
<?php // John you should move/fix me ?>
#a-media-add
{
	display: none;
	clear: both;
}
.a-media-add-subheading
{
  float: left;
  width: 250px;
}
#a-media-add h2
{
  float: left;
  padding-left: 20px;
  padding-right: 20px;
}
</style>

<?php if (aMediaTools::userHasUploadPrivilege() && ($uploadAllowed || $embedAllowed)): ?>
	<div id="a-media-add">
    <?php if ($uploadAllowed): ?>
      <?php include_partial('aMedia/uploadMultiple', array('form' => new aMediaUploadMultipleForm())) ?>    
    <?php endif ?>
    <?php if ($uploadAllowed && $embedAllowed): ?>
      <h2>OR</h2>
    <?php endif ?>
    <?php if ($embedAllowed): ?>
      <?php include_partial('aMedia/embed') ?>    
    <?php endif ?>
  </div>
  
  <?php if ($sf_params->get('add')): ?>
    <?php // This is a validation error pass ?>
    <script type="text/javascript" charset="utf-8">
      $(function() {
        $('#a-media-add').show();
      });
    </script>
  <?php endif ?>
  <script type="text/javascript" charset="utf-8">
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