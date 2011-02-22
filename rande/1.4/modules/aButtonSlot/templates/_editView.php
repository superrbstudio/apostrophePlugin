<?php use_helper('I18N') ?>
<?php if ($form['url']->hasError()): ?>
  <div class="a-error"><?php echo __('Invalid URL. A valid example: http://www.punkave.com/', null, 'apostrophe') ?></div>
<?php endif ?>
<div class="a-form-row"><?php echo __('URL: %r%', array('%r%' => $form['url']->render()), 'apostrophe') ?></div>
<div class="a-form-row"><?php echo __('Title: %t%', array('%t%' => $form['title']->render()), 'apostrophe') ?></div>
<?php echo $form->renderHiddenFields() ?>
