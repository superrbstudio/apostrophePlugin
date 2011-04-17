<?php

/**
 * @class aEditor
 * Subclass me and implement the render() method to add support for
 * another rich text editor. Set app_a_editor to foo instead of fck and 
 * Apostrophe will start using aEditorFoo instead of aEditorFck
 *
 * Basically just a wrapper for a render method. Almost a widget class,
 * but the options have already been determined for you since you must be 
 * compatible with those expected by the traditional FCK editor widget: 
 * tool, width and height. tool defaults to Default. You must support
 * reasonable fallback behavior for a tool setting you don't recognize.
 * Your default toolbar should not provide inline image editing as the standard
 * in Apostrophe is to use media slots and the inline images would be
 * filtered out anyway.
 *
 * Since different editors define toolbars in different ways (FCKEditor requires
 * a JavaScript configuration file while CKEditor can do it in PHP, etc), you
 * must provide and document your own way of configuring the features of each
 * available toolbar set.
 *
 * Since FCK can't really separate the id and the name of the editor,
 * it is permissible for your rich text editor to have the same limitation.
 * When in doubt get the name of your hidden form element right.
 */

abstract class aEditor
{
  /**
   * 
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $options     The options set on the aWidgetFormRichTextarea object (tool, width, height)
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   * @return string An HTML tag string
   *
   * You must render a hidden form field or invisible textarea with the correct name that
   * Apostrophe can get a value from at form submission time.
   *
   * $attributes['id'] is always set, but some editors can't distinguish the id from the name. 
   * Err on the side of getting the name right. Apostrophe's forms that use rich text are 
   * set up to cope with this.
   *
   * YOUR EDITOR MUST SUPPORT UPDATING ITS HIDDEN FIELD ON AN a.update jQuery event 
   * sent to the hidden field, which must have the a-needs-update class. Really. See aEditorFck for an example
   * 
   */
  
  abstract public function render($name, $value, $options, $attributes, $errors);
}
