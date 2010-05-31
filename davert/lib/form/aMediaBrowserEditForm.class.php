<?php

// NOT BUILT YET, for now media browsing is still done via the standalone media plugin.

// This form edits the constraints placed on this media browser.
// It's part of the edit view. Not to be confused with any forms 
// involved in actually browsing as an end user.

class aMediaBrowserEditForm extends sfForm
{
  public function configure()
  {
    $api = new aMediaAPI();
    $tags = $api->getTags();
    $tagsHash = array("" => "NONE");
    foreach ($tags as $choice)
    {
      $tagsHash[$choice] = $choice;
    }
    $tags = array_keys($tagsHash);
    $typesHash = array(
      "" => "ALL",
      "image" => "Image",
      "video" => "Video");
    $this->setWidgets(array(
      "tag" => new sfWidgetFormChoice(array('choices' => $tagsHash)),
      "search" => new sfWidgetFormInput(),
      "type" => new sfWidgetFormChoice(array('choices' => $typesHash))
      ));
    $this->setValidators(array(
      "tag" => new sfValidatorChoice(array("choices" => $tags, "required" => false)),
      "search" => new sfValidatorPass(),
      "type" => new sfValidatorChoice(array("choices" => array_keys($typesHash), "required" => false))));    
  }
  
  public function setParams($params)
  {
    foreach ($params as $param => $value)
    {
      $this->setDefault($param, $value);
    }
  }
  
  public function setId($id)
  {
    $this->widgetSchema->setNameFormat("a-mediabrowser-edit-form-$id" . "[%s]");
  }
}
