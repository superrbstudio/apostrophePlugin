<?php

 /**
 * sfWidgetFormRichTextarea represents a rich text editor.
 * The FCK editor is always used in this implementation. 
 *
 * Originally based on Dominic Scheirlinck's implementation. However now
 * it is pretty much a thin wrapper around code ported from the old 
 * Symfony 1.x FCK rich text editor class (which is gone in 1.4).
 * 
 * NOTE: THE ID IS IGNORED, FCK always sets the name and id attributes
 * of the hidden input field or fallback textarea to the same value. We
 * must use name for that value to produce results the forms framework
 * can understand.
 *
 * This is a misfeature of FCK, not something we can fix here without
 * breaking the association between the hidden field and the rich text
 * editor. ALWAYS USE setNameFormat() in your form class to give your
 * form fields names that will distinguish them from any other forms
 * in the same page, otherwise your rich text fields will behave in
 * unexpected ways. (Yes, this does mean IDs with brackets in them are in
 * use due to this limitation of FCK, however all modern browsers 
 * allow that in practice.) This is rarely an issue unless you have
 * numerous forms in the same page and they have the same name format string
 * (or the default %s).
 *
 * @author     Tom Boutell <tom@punkave.com>
 */
class sfWidgetFormRichTextarea extends sfWidgetFormTextarea 
{
  /**
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('editor', 'fck');
    $this->addOption('css', false);
		$this->addOption('tool','Default');
		$this->addOption('height','225');
		$this->addOption('width','100%');
    
    parent::configure($options, $attributes);
  }
  
  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * This is mostly borrowed from the Symfony 1.3 sf Rich Text Editor FCK class,
   * (don't want to say it out loud and make project:validate worry),
   * which is gone in Symfony 1.4 and not autoloadable in Symfony 1.3.
   * Note that we are now officially FCK-specific. That was pretty much
   * true already (notice our fckextraconfig.js trick below). 
   *
   * NOTE: THE ID IS IGNORED, FCK always sets the name and id attributes
   * of the hidden input field or fallback textarea to the same value. 
   * This is a misfeature of FCK, not something we can fix here without
   * breaking the association between the hidden field and the rich text
   * editor. ALWAYS USE setNameFormat() in your form class to give your
   * form fields names that will distinguish them from any other forms
   * in the same page, otherwise your rich text fields will behave in
   * unexpected ways.
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $attributes = array_merge($attributes, $this->getOptions());
    $attributes = array_merge($attributes, array('name' => $name));
    // This is good form, but doesn't really work with FCK, which
    // does not support a name that is distinct from the id
    $attributes = $this->fixFormId($attributes);
    
    // TBB: a sitewide additional config settings file is used, if it
    // exists and a different one has not been explicitly specified
    if (isset($attributes['editor']) && (strtolower($attributes['editor']) === 'fck'))
    {
      if (!isset($attributes['config']))
      {
        if (file_exists(sfConfig::get('sf_web_dir') . '/js/fckextraconfig.js'))
        {
          $attributes['config'] = '/js/fckextraconfig.js'; 
        }
      }
    }
    
    // Merged in from Symfony 1.3's FCK rich text editor implementation,
    // since that is no longer available in 1.4
    
    $options = $attributes;
    $id = $options['id'];

    $php_file = sfConfig::get('sf_rich_text_fck_js_dir').DIRECTORY_SEPARATOR.'fckeditor.php';

    if (!is_readable(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$php_file))
    {
      throw new sfConfigurationException('You must install FCKEditor to use this widget (see rich_text_fck_js_dir settings).');
    }

    // FCKEditor.php class is written with backward compatibility of PHP4.
    // This reportings are to turn off errors with public properties and already declared constructor
    $error_reporting = error_reporting(E_ALL);

    require_once(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$php_file);

    // turn error reporting back to your settings
    error_reporting($error_reporting);

    // What if the name isn't an acceptable id? 
    $fckeditor           = new FCKeditor($options['name']);
    $fckeditor->BasePath = sfContext::getInstance()->getRequest()->getRelativeUrlRoot().'/'.sfConfig::get('sf_rich_text_fck_js_dir').'/';
    $fckeditor->Value    = $value;

    if (isset($options['width']))
    {
      $fckeditor->Width = $options['width'];
    }
    elseif (isset($options['cols']))
    {
      $fckeditor->Width = (string)((int) $options['cols'] * 10).'px';
    }

    if (isset($options['height']))
    {
      $fckeditor->Height = $options['height'];
    }
    elseif (isset($options['rows']))
    {
      $fckeditor->Height = (string)((int) $options['rows'] * 10).'px';
    }

    if (isset($options['tool']))
    {
      $fckeditor->ToolbarSet = $options['tool'];
    }

    if (isset($options['config']))
    {
      // We need the asset helper to load things via javascript_path
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Asset'));
      $fckeditor->Config['CustomConfigurationsPath'] = javascript_path($options['config']);
    }

    $content = $fckeditor->CreateHtml();

    // Skip the braindead 'type='text'' hack that breaks Safari
    // in 1.0 compat mode, since we're in a 1.2+ widget here for sure

    return $content;
  }
}
