<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaMediaUploadForm extends BaseForm
{

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    $this->setWidget("file", new aWidgetFormInputFilePersistent(
      array(
        // Not yet
        // "iframe" => true, "progress" => "Uploading...", 
        "image-preview" => array("width" => 50, "height" => 50, "resizeType" => "c"))));
    
    $mimeTypes = aMediaTools::getOption('mime_types');
    // It comes back as a mapping of extensions to types, get the types
    $extensions = array_keys($mimeTypes);
    $mimeTypes = array_values($mimeTypes);
    $this->setValidator("file", new aValidatorFilePersistent(
      array("mime_types" => $mimeTypes,
        'validated_file_class' => 'aValidatedFile',
        "required" => false),
      array("mime_types" => "The following file types are accepted: " . implode(', ', $extensions))));
      
    // Without this, the radio buttons on the edit form will not have a default
    $this->setWidget("view_is_secure", new sfWidgetFormInputHidden(array('default' => '0')));
    $this->setValidator("view_is_secure", new sfValidatorPass(array('required' => false)));
    $this->setDefault('view_is_secure', 0);
    // The same as the edit form by design
    $this->widgetSchema->setNameFormat('a_media_item[%s]');
    $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
}
