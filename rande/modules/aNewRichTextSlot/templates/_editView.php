<?php use_helper('jQuery') ?>
<?php echo $form->renderHiddenFields() ?>
<?php echo $form['value']->render() ?>

<script type="text/javascript" charset="utf-8">
<?php // An AJAX form submission doesn't fire submit handlers so we have to ?>
<?php // pull the value from the contentEditable div into the hidden field ourselves ?>
window.apostrophe.registerOnSubmit("<?php echo $id ?>", 
  function(slotId)
  {
    var id = "<?php echo $id ?>";
    var content = $('#slotform-' + id + '_value-editor').html();
    $('#slotform-' + id + '_value').val(content);
  }
);
</script>
