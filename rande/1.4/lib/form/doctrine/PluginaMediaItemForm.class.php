<?php

/**
 * PluginaMediaItem form.
 *
 * @package    form
 * @subpackage aMediaItem
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
abstract class PluginaMediaItemForm extends BaseaMediaItemForm
{
  public function setup()
  {
    parent::setup();
    unset($this['created_at']);
    unset($this['updated_at']);
    unset($this['owner_id']);
    $this->setWidget('tags', new sfWidgetFormInput(array("default" => implode(", ", $this->getObject()->getTags())), array("class" => "tag-input", "autocomplete" => "off")));
    $this->setValidator('tags', new sfValidatorPass());
		$this->setWidget('view_is_secure', new sfWidgetFormSelect(array('choices' => array('1' => 'Hidden', '' => 'Public'))));
    $this->setWidget('description', new sfWidgetFormRichTextarea(array('editor' => 'fck', 'tool' => 'Media', )));
		$this->setValidator('view_is_secure', new sfValidatorChoice(array('required' => false, 'choices' => array('1', ''))));
		$this->widgetSchema->setLabel('media_categories_list', 'Categories');
		// If I don't unset this saving the form will purge existing relationships to slots
		unset($this['slots_list']);
		$this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
  public function updateObject($values = null)
  {
    $object = parent::updateObject($values);
    // Do some postvalidation of what parent::updateObject did
    // (it would be nice to turn this into an sfValidator subclass)
    $object->setDescription(aHtml::simplify($object->getDescription(),
      "<p><br><b><i><strong><em><ul><li><ol><a>"));
    // The tags field is not a native Doctrine field 
    // so we can't rely on parent::updateObject to sort out
    // whether to use $values or $this->getValue. So we need
    // to sanitize and figure out what set of values to use
    // (embedded forms get a $values parameter, non-embedded
    // use $this->values) 
    if (is_null($values))
    {
      $values = $this->values;
    }
    // Now we're ready to play
    // We like all-lowercase tags for consistency
    $values['tags'] = strtolower($values['tags']);
    $object->setTags($values['tags']);
    $object->setOwnerId(
      sfContext::getInstance()->getUser()->getGuardUser()->getId());
    return $object;
  }

  // We don't include the form class in the token because we intentionally
  // switch form classes in midstream. You can't learn the session ID from
  // the cookie on your local box, so this is sufficient
  public function getCSRFToken($secret = null)
  {
    if (null === $secret)
    {
      $secret = self::$CSRFSecret;
    }

    return md5($secret.session_id());
  }    
}
