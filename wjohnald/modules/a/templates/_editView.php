<?php
  // Compatible with sf_escaping_strategy: true
  $form = isset($form) ? $sf_data->getRaw('form') : null;
?>
<?php
  // Displays the slot's contents in an editable form. An HTML form
  // tag is already open at this point (see _slot.php).
  //
  // You will virtually always override this by writing your own
  // executeEditView method in your own slot module's components class
  // which extends BaseaSlotComponents, and providing an
  // _editView.php template in that module. Be sure to call 
  // parent::executeEditView() in that method
  //
  // Since all standard slots are now implemented via Symfony 1.2+ forms,
  // the default implementation of this partial now assumes you have set 
  // up a form object (although your own slots might not take that approach)
?>
<?php echo $form ?>
