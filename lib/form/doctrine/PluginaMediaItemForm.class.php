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
    $this->setWidget('description', new aWidgetFormRichTextarea(array('editor' => 'fck', 'tool' => 'Media', 'height' => 125, )));
		$this->setValidator('view_is_secure', new sfValidatorChoice(array('required' => false, 'choices' => array('1', ''))));

    $user = sfContext::getInstance()->getUser();
    $admin = $user->hasCredential(aMediaTools::getOption('admin_credential'));
		$q = Doctrine::getTable('aCategory')->addCategoriesForUser(sfContext::getInstance()->getUser()->getGuardUser(), $admin)->orderBy('name');
		$this->setWidget('categories_list', new sfWidgetFormDoctrineChoice(array('query' => $q, 'model' => 'aCategory', 'multiple' => true)));
		$this->setValidator('categories_list', new sfValidatorDoctrineChoice(array('query' => $q, 'model' => 'aCategory', 'multiple' => true, 'required' => false)));
		$categories = $q->execute();
		$this->widgetSchema->setLabel('categories_list', 'Categories');

		$this->setWidget('categories_list_add', new sfWidgetFormInput());
		//TODO: Make this validator better, should check for duplicate categories, etc.
		$this->setValidator('categories_list_add', new sfValidatorPass(array('required' => false)));

		// If I don't unset this saving the form will purge existing relationships to slots
		unset($this['slots_list']);
		$this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
		$this->validatorSchema->setPostValidator(
      new sfValidatorCallback(array('callback' => array($this, 'postValidator')))
    );
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
    $values['tags'] = aString::strtolower($values['tags']);
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

  public function postValidator($validator, $values)
  {
    if(isset($values['categories_list_add']) && is_array($values['categories_list_add']))
    {
      $stringValidator = new sfValidatorString();
      foreach($values['categories_list_add'] as $key => $value)
      {
        $values['categories_list_add'][$key] = $stringValidator->clean($value);
      }
    }
    return $values;
  }

 public function updateCategoriesList(&$values)
  {
    $cvalues = $values['categories_list_add'];
    $link = array();
    if(!is_array($cvalues))
    {
      $cvalues = array();
    }
    foreach ($cvalues as $value)
    {
      $existing = Doctrine::getTable('aCategory')->findOneBy('name', $value);
      if($existing)
      {
        $aCategory = $existing;
      }
      else
      {
        $aCategory = new aCategory();
        $aCategory['name'] = $value;
      }
      $aCategory->save();
      $link[] = $aCategory['id'];
    }
    if(!is_array($values['categories_list']))
    {
      $values['categories_list'] = array();
    }
    $values['categories_list'] = array_merge($link, $values['categories_list']);
    // Needed when this is an embedded form
    return $values['categories_list'];
  }

  protected function doSave($con = null)
  {
    if(isset($this['categories_list_add']))
    {
      $this->updateCategoriesList($this->values);
    }
    parent::doSave($con);
  }
    
}
