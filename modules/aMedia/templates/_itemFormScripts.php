<?php use_helper('a') ?>
<?php use_javascript('/sfDoctrineActAsTaggablePlugin/js/pkTagahead.js') ?>
<?php $options = array('choose-one' => a_('Choose Categories')) ?>
<?php if (sfContext::getInstance()->getUser()->hasCredential(aMediaTools::getOption('admin_credential'))): ?>
  <?php $options['add'] = a_('+ New Category') ?>
<?php endif ?>
<?php a_js_call('aMultipleSelectAll(?)', $options) ?>
