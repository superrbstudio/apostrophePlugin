<?php
/**
 * 
 * aWidgetFormRichTextarea represents a rich text editor.
 * Defaults to the FCK editor. Accesses that editor's functionality
 * via the aEditorFck class. You can specify a different editor wich
 * the editor option or by setting the default with app_a_editor. 
 * For backwards compatibility the option value 'fck' maps to the
 * class name 'aEditorFck', while 'ck' will map to 'aEditorCk', etc.
 *
 * FCK support is baked in, for CKEditor support you'll need a plugin that
 * provides aEditorCk, CKEditor itself, etc.
 *
 * Originally based on Dominic Scheirlinck's implementation. However now
 * it is pretty much a thin wrapper around code ported from the old
 * Symfony 1.x FCK rich text editor class (which is gone in 1.4).
 *
 * NOTE: THE ID IS IGNORED BY BOTH FCK AND CK, FCK always sets the name and 
 * id attributes of the hidden input field or fallback textarea to the same value. 
 * We must use name for that value to produce results the forms framework
 * can understand.
 *
 * This is a misfeature of FCK and CK, not something we can fix here without
 * breaking the association between the hidden field and the rich text
 * editor. ALWAYS USE setNameFormat() in your form class to give your
 * form fields names that will distinguish them from any other forms
 * in the same page, otherwise your rich text fields will behave in
 * unexpected ways. (Yes, this does mean IDs with brackets in them are in
 * use due to this limitation of FCK/CK, however all modern browsers
 * allow that in practice.) This is rarely an issue unless you have
 * numerous forms in the same page and they have the same name format string
 * (or the default %s).
 * @author     Tom Boutell <tom@punkave.com>
 * @package    apostrophePlugin
 * @subpackage    widget
 */
class aWidgetFormRichTextarea extends sfWidgetFormTextarea 
{
  /**
   * 
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('editor', sfConfig::get('app_a_editor', 'fck'));
    // For bc. Does nothing
    $this->addOption('css', false);
    $this->addOption('tool','Default');
    $this->addOption('height','225');
    $this->addOption('width','100%');
    
    parent::configure($options, $attributes);
  }
  
  protected $editor = null;
  
  protected function getEditor()
  {
    if (!$this->editor)
    {
      $editorClass = 'aEditor' . ucfirst($this->getOption('editor'));
      $this->editor = new $editorClass();
    }
    return $this->editor;
  }

  /**
   * 
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   * @return string An HTML tag string
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $editor = $this->getEditor();
    $options = $this->getOptions();
    
    // Try to guarantee an id. This is good form, but doesn't really work with FCK, which
    // does not support a name that is distinct from the id
    if (isset($options['id']) && (!isset($attributes['id'])))
    {
      $attributes['id'] = $options['id'];
    }
    $attributes = $this->fixFormId($attributes);
    
    return $editor->render($name, $value, $this->getOptions(), $attributes, $errors);
  }
  
}
