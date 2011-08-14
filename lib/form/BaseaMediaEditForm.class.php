<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaMediaEditForm extends aMediaItemForm
{

  /**
   * Use this to i18n select choices that SHOULD be i18ned. It never gets called,
   * it's just here for our i18n-update task to sniff
   */
  private function i18nDummy()
  {
    __('Public', null, 'apostrophe');
    __('Hidden', null, 'apostrophe');
    __('Replace File', null, 'apostrophe');
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
    unset($this['embed']);
    
    $this->setWidget('file', new aWidgetFormInputFilePersistent(array(
    )));

    $item = $this->getObject();
    
    // A safe assumption because we always go through the separate upload form on the first pass.
    // This label is a hint that changing the file is not mandatory
    
    // The 'Replace File' label is safer than superimposing a file button
    // on something that may or may not be a preview or generally a good thing 
    // to try to read a button on top of
    
    $this->getWidget('file')->setLabel('Select a new file');
    if (!$item->isNew())
    {
      $this->getWidget('file')->setOption('default-preview', $item->getOriginalPath());
    }
    
    $mimeTypes = aMediaTools::getOption('mime_types');
    // It comes back as a mapping of extensions to types, get the types
    $extensions = array_keys($mimeTypes);
    $mimeTypes = array_values($mimeTypes);
    
    $type = false;
    if (!$this->getObject()->isNew()) {
      // You can't change the major type of an existing media object as
      // this would break slots (a .doc where a .gif should be...)
      $type = $this->getObject()->type;
    }
    // What we are selecting to add to a page
    if (!$type)
    {
      $type = aMediaTools::getType();
    }
    if (!$type)
    {
      // What we are filtering for 
      $type = aMediaTools::getSearchParameter('type');
    }
    if ($type)
    {
      // This supports composite types like _downloadable
      $infos = aMediaTools::getTypeInfos($type);
      $extensions = array();
      foreach ($infos as $info)
      {
        if ($info['embeddable'])
        {
          // This widget is actually supplying a thumbnail - allow gif, jpg and png
          $info['extensions'] = array('gif', 'jpg', 'png');
        }
        foreach ($info['extensions'] as $extension)
        {
          $extensions[] = $extension;
        }
      }
      $mimeTypes = array();
      $mimeTypesByExtension = aMediaTools::getOption('mime_types');
      foreach ($extensions as $extension)
      {
        // Careful, if we are filtering for a particular type then not everything
        // will be on the list
        if (isset($mimeTypesByExtension[$extension]))
        {
          $mimeTypes[] = $mimeTypesByExtension[$extension];
        }
      }
    }
    
    // The file is mandatory if it's new. Otherwise
    // we have a problem when they get the file type wrong
    // for one of two and we have to reject that one,
    // then they resubmit - we can add an affirmative way
    // to remove one item from the annotation form later
    
    // Make the validator aware of the minimum dimensions for
    // the select
        
    $minimumWidth = null;
    $minimumHeight = null;
    if (aMediaTools::isSelecting())
    {
      $minimumWidth = aMediaTools::getAttribute('minimum-width');
      $minimumHeight = aMediaTools::getAttribute('minimum-height');
    }
    
    $options = array("mime_types" => $mimeTypes,
      'validated_file_class' => 'aValidatedFile',
      "required" => $this->getObject()->isNew() ? true : false);
    if ($minimumWidth)
    {
      $options['minimum-width'] = $minimumWidth;
    }
    if ($minimumHeight)
    {
      $options['minimum-height'] = $minimumHeight;
    }
    $this->setValidator("file", new aValidatorFilePersistent($options,
      array("mime_types" => "The following file types are accepted: " . implode(', ', $extensions))));

    // Necessary to work around FCK's "id and name cannot differ" problem
    // ... But we do it in the action which knows when that matters
    // $this->widgetSchema->setNameFormat('a_media_item_'.$this->getObject()->getId().'_%s');
    // $this->widgetSchema->setFormFormatterName('aAdmin');
  }

  /**
   * DOCUMENT ME
   * @param mixed $values
   * @return mixed
   */
  public function updateObject($values = null)
  {
    if (!isset($values))
    {
      $values = $this->getValues();
    }
    
    return parent::updateObject($values);
  }
}