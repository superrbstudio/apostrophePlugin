<?php

/**
 * @class aEditorFck
 * Implements FCK editor support. @see aEditor for how to implement
 * support for other editors
 *
 * There is code here borrowed from earlier versions of Symfony and their
 * FCK wrapper widgets and such
 *
 * Since FCK can't really separate the id and the name of the editor,
 * it is permissible for your rich text editor to have the same limitation.
 */
 
class aEditorFck extends aEditor
{
  /**
   * 
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $options     The options set on the aWidgetFormRichTextarea object (id, tool, width, height)
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   * @return string An HTML tag string
   * @see aEditor
   */
  
  public function render($name, $value, $options, $attributes, $errors)
  {
    $attributes = array_merge($attributes, $options);
    $attributes = array_merge($attributes, array('name' => $name));
  
    // TBB: a sitewide additional config settings file is used, if it
    // exists and a different one has not been explicitly specified
    if (!isset($attributes['config']))
    {
      if (file_exists(sfConfig::get('sf_web_dir') . '/js/fckextraconfig.js'))
      {
        $attributes['config'] = '/js/fckextraconfig.js'; 
      }
    }
  
    // Merged in from Symfony 1.3's FCK rich text editor implementation,
    // since that is no longer available in 1.4
  
    $options = $attributes;

    // sf_web_dir already contains the relative root, don't append it twice
    $php_file = '/'.sfConfig::get('sf_rich_text_fck_js_dir').DIRECTORY_SEPARATOR.'fckeditor.php';

    if (!is_readable(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$php_file))
    {
      throw new sfConfigurationException('You must install FCKEditor to use this widget (see rich_text_fck_js_dir settings). ' . sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$php_file);
    }

    // FCKEditor.php class is written with backward compatibility of PHP4.
    // This reportings are to turn off errors with public properties and already declared constructor
    $error_reporting = error_reporting(E_ALL);

    require_once(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$php_file);

    // turn error reporting back to your settings
    error_reporting($error_reporting);

    // What if the name isn't an acceptable id? 
    $fckeditor           = new FCKeditor($options['name']);
    
    // We'd like to use app_a_static_url but we can't because FCK loads in an iframe
    // and therefore can't talk to the page if it's loaded from S3
    $sf_relative_url_root = sfContext::getInstance()->getRequest()->getRelativeUrlRoot();
    
    $fckeditor->BasePath = $sf_relative_url_root.'/'.sfConfig::get('sf_rich_text_fck_js_dir').'/';
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

    // We must register an a.onSubmit handler to be sure of updating the
    // hidden field when a richtext slot or other AJAX context is updated
    $content .= <<<EOM
<script type="text/javascript" charset="utf-8">
$(function() {
  $('#$name').addClass('a-needs-update');
  $('#$name').bind('a.update', function() {
    var value = FCKeditorAPI.GetInstance('$name').GetXHTML();
    $('#$name').val(value);
  });
});
</script>
EOM
;    
    return $content;
  }
}