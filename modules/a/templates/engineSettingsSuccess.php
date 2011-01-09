<?php // Render the engine-specific partial, if any, and then make sure a_js_call etc. have their day ?>
<?php if (isset($partial)): ?>
  <?php include_partial($partial, array('form' => $form)) ?>
<?php endif ?>
<?php include_partial('a/globalJavascripts') ?>