<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $id = isset($id) ? $sf_data->getRaw('id') : null;
?>
<?php echo $form->renderHiddenFields() ?>
<?php echo $form['value']->render() ?>

<script type="text/javascript" charset="utf-8">
window.apostrophe.registerOnSubmit("<?php echo $id ?>", 
  function(slotId)
  {
    <?php # FCK doesn't do this automatically on an AJAX "form" submit on every major browser ?>
    var value = FCKeditorAPI.GetInstance('slot-form-<?php echo $id ?>-value').GetXHTML();
    $('#slot-form-<?php echo $id ?>-value').val(value);
  }
);
</script>
