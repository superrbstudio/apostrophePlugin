<?php
/**
 * 
 * PluginaMediaItem form.
 * @package    form
 * @subpackage aMediaItem
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
abstract class PluginaMediaItemForm extends BaseaMediaItemForm
{

  /**
   * DOCUMENT ME
   */
  public function setup()
  {
    parent::setup();
    unset($this['created_at']);
    unset($this['updated_at']);
    unset($this['owner_id']);
    unset($this['lucene_dirty']);
    $this->setWidget('tags', new sfWidgetFormInput(array("default" => implode(", ", $this->getObject()->getTags())), array("class" => "tag-input", "autocomplete" => "off")));
    $this->setValidator('tags', new sfValidatorPass());
    $this->setWidget('view_is_secure', new sfWidgetFormSelect(array('choices' => array('1' => 'Hidden', '' => 'Public'))));
    $this->setWidget('description', new aWidgetFormRichTextarea(array('tool' => 'Media', 'height' => 182 ))); // FCK doesn't like to be smaller than 182px in Chrome 
    $this->setValidator('view_is_secure', new sfValidatorChoice(array('required' => false, 'choices' => array('1', ''))));

    $q = $this->getCategoriesQuery();
    $this->setWidget('categories_list', new sfWidgetFormDoctrineChoice(array('query' => $q, 'model' => 'aCategory', 'multiple' => true)));
    $this->setValidator('categories_list', new sfValidatorDoctrineChoice(array('query' => $q, 'model' => 'aCategory', 'multiple' => true, 'required' => false)));
    $categories = $q->execute();
    $this->widgetSchema->setLabel('categories_list', 'Categories');

    $this->setValidator('title', new sfValidatorString(array(
      'min_length' => 3,
      'max_length' => 200,
      'required' => true
    ), array(
      'min_length' => 'Title must be at least 3 characters.',
      'max_length' => 'Title must be <200 characters.',
      'required' => 'You must provide a title.')
    ));

    $this->setWidget('view_is_secure', new sfWidgetFormSelectRadio(array(
      'choices' => array(0 => 'Public', 1 => 'Hidden'),
      'default' => 0
    )));
  
    $this->setValidator('view_is_secure', new sfValidatorBoolean());

    $this->widgetSchema->setLabel('view_is_secure', 'Permissions');
    
    $this->widgetSchema->setLabel('categories_list', 'Categories');
    
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');

    if ($this->isAdmin())
    {
      // Only admins can add more categories
      $this->setWidget('categories_list_add', new sfWidgetFormInput());
      $this->setValidator('categories_list_add', new sfValidatorPass());
    }
    
    // If I don't unset this saving the form will purge existing relationships to slots
    unset($this['slots_list']);
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
    $this->validatorSchema->setPostValidator(
      new sfValidatorCallback(array('callback' => array($this, 'postValidator')))
    );
  }
  
  /**
   * Returns a Doctrine query that fetches categories this user is actually allowed
   * to assign content to, in alphabetical order. A useful override point 
   * @return array
   */
  protected function getCategoriesQuery()
  {
    $user = sfContext::getInstance()->getUser();
    $admin = $user->hasCredential(aMediaTools::getOption('admin_credential'));
    return Doctrine::getTable('aCategory')->addCategoriesForUser(sfContext::getInstance()->getUser()->getGuardUser(), $admin)->orderBy('name');
  }

  /**
   * DOCUMENT ME
   * @param mixed $values
   * @return mixed
   */
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

    // Slashes break routes in most server configs. Do NOT force case of tags.
    
    $values['tags'] = str_replace('/', '-', isset($values['tags']) ? $values['tags'] : '');

    $object->setTags($values['tags']);
    return $object;
  }

  /**
   * We don't include the form class in the token because we intentionally
   * switch form classes in midstream. You can't learn the session ID from
   * the cookie on your local box, so this is sufficient
   * @param mixed $secret
   * @return mixed
   */
  public function getCSRFToken($secret = null)
  {
    if (null === $secret)
    {
      $secret = self::$CSRFSecret;
    }
    return md5($secret.session_id());
  }

  /**
   * DOCUMENT ME
   * @param mixed $validator
   * @param mixed $values
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function isAdmin()
  {
    return sfContext::getInstance()->getUser()->hasCredential(aMediaTools::getOption('admin_credential'));
  }

  /**
   * Returns categories set on this item that this user is not eligible to remove.
   * Used for static display
   * @return mixed
   */
  public function getAdminCategories()
  {
    return $this->object->getAdminCategories();
  }

  /**
   * DOCUMENT ME
   * @param mixed $values
   * @return mixed
   */
  public function updateCategoriesList(&$values)
  {
    $cvalues = isset($values['categories_list_add']) ? $values['categories_list_add'] : array();
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

    // Never allow a non-admin to remove categories they are not eligible to add
    $reserved = aArray::getIds($this->getAdminCategories());
    foreach ($reserved as $id)
    {
      if (!in_array($id, $values['categories_list']))
      {
        $values['categories_list'][] = $id;
      }
    }
    // Needed when this is an embedded form
    return $values['categories_list'];
  }

  /**
   * DOCUMENT ME
   * @param mixed $con
   */
  protected function doSave($con = null)
  {
    $this->updateCategoriesList($this->values);
    parent::doSave($con);
  }
    
}
