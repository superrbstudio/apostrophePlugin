<fieldset id="a-fieldset-[?php echo preg_replace('/[^a-z0-9_]/', '_', strtolower($fieldset)) ?]">
  [?php if ('NONE' != $fieldset): ?]
    <h2>[?php echo __($fieldset, array(), '<?php echo $this->getI18nCatalogue() ?>') ?]</h2>
  [?php endif; ?]

  [?php foreach ($fields as $name => $field): ?]
    [?php if ((isset($form[$name]) && $form[$name]->isHidden()) || (!isset($form[$name]) && $field->isReal())) continue ?]
    [?php include_partial('<?php echo $this->getModuleName() ?>/form_field', array(
      'name'       => $name,
      'attributes' => $field->getConfig('attributes', array()),
      'label'      => $field->getConfig('label'),
      'help'       => $field->getConfig('help'),
      'form'       => $form,
      'field'      => $field,
      'class'      => 'a-form-row a-admin-'.strtolower($field->getType()).' a-admin-form-field-'.$name,
    )) ?]
  [?php endforeach; ?]
</fieldset>
