<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $id = isset($id) ? $sf_data->getRaw('id') : null;
?>
<div class="a-form-row a-hidden">
<?php echo $form->renderHiddenFields() ?>
</div>

<?php echo $form['value']->render() ?>

<?php a_js_call('apostrophe.slotEnhancements(?)', array('slot' => '#a-slot-'.$pageid.'-'.$name.'-'.$permid, 'editClass' => 'a-options')) ?>