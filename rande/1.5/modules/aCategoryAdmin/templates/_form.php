<?php
  // Compatible with sf_escaping_strategy: true
  $a_category = isset($a_category) ? $sf_data->getRaw('a_category') : null;
  $configuration = isset($configuration) ? $sf_data->getRaw('configuration') : null;
  $form = isset($form) ? $sf_data->getRaw('form') : null;
  $helper = isset($helper) ? $sf_data->getRaw('helper') : null;
?>
<?php use_helper('a') ?>
<?php include_stylesheets_for_form($form) ?>
<?php include_javascripts_for_form($form) ?>

<div class="a-admin-form-container">
  <?php echo form_tag_for($form, '@a_category_admin', array('id'=>'a-admin-form')) ?>
    <?php echo $form->renderHiddenFields() ?>

    <?php if ($form->hasGlobalErrors()): ?>
      <?php echo $form->renderGlobalErrors() ?>
    <?php endif; ?>

    <?php foreach ($configuration->getFormFields($form, $form->isNew() ? 'new' : 'edit') as $fieldset => $fields): ?>
      <?php include_partial('aCategoryAdmin/form_fieldset', array('a_category' => $a_category, 'form' => $form, 'fields' => $fields, 'fieldset' => $fieldset)) ?>
    <?php endforeach; ?>

    <?php include_partial('aCategoryAdmin/form_actions', array('a_category' => $a_category, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
  </form>
</div>

<?php a_js_call('aMultipleSelect(?, ?)', '.a-admin-form-field-users_list', array('choose-one' => a_('Choose Users'))) ?>
<?php a_js_call('aMultipleSelect(?, ?)', '.a-admin-form-field-groups_list', array('choose-one' => a_('Choose Groups'))) ?>
