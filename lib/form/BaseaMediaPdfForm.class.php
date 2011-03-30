<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaMediaPdfForm extends aMediaItemForm
{

  /**
   * Use this to i18n select choices that SHOULD be i18ned. It never gets called,
   * it's just here for our i18n-update task to sniff
   */
  private function i18nDummy()
  {
    __('Public', null, 'apostrophe');
    __('Hidden', null, 'apostrophe');
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    // This call was missing, preventing easy extension of all media item edit forms at the project level
    parent::configure();
    
    unset($this['id']);
    unset($this['type']);
    unset($this['service_url']);
    unset($this['slug']);
    unset($this['width']);
    unset($this['height']);
    unset($this['format']);
    if ($this->getObject())
    {
      $id = $this->getObject()->getId();
    }
    else
    {
      $id = false;
    }
    $this->setWidget('file', 
      new aWidgetFormInputFilePersistent(
        array(
          // "image-preview" => aMediaTools::getOption('gallery_constraints')
          )));
    $this->setValidator('file', new aValidatorFilePersistent(
      array("mime_types" => array("application/pdf", "application/x-pdf"),
        "required" => (!$this->getObject()->getId())),
      array("mime_types" => "PDF only.",
        "required" => "Select a PDF file")));
    // These have to be brief to work with Rick's styles
    $this->setValidator('title', 
      new sfValidatorString(
        array("min_length" => 3, "max_length" => 200, "required" => true),
        array("min_length" => "Title must be at least 3 characters.",
          "max_length" => "Title must be <200 characters.",
          "required" => "You must provide a title.")));

    $this->setWidget('view_is_secure', new sfWidgetFormChoice(
      array(
        'expanded' => true,
        'choices' => array(
        0 => "Public",
        1 => "Hidden"
        ),
        'default' => 0
        )));
  
    $this->setValidator('view_is_secure', new sfValidatorBoolean());

    $this->widgetSchema->setLabel("view_is_secure", "Permissions");
    $this->widgetSchema->setNameFormat('a_media_item[%s]');
    $this->widgetSchema->setFormFormatterName('aAdmin');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }

  /**
   * DOCUMENT ME
   * @param mixed $values
   * @return mixed
   */
  public function updateObject($values = null)
  {
    $object = parent::updateObject($values);
    $object->type = 'pdf';
    return $object;
  }
}