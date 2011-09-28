<?php
/**
 * TODO: move the post-validation cleanup of the slug into the
 * validator so that we don't get a user-unfriendly error or
 * failure when /Slug Foo fails to be considered a duplicate
 * of /slug_foo the first time around
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaPageSettingsForm extends aPageForm
{

  /**
   * Use this to i18n select choices that SHOULD be i18ned and other things that the
   * sniffer would otherwise miss. It never gets called, it's just here for our i18n-update
   * task to sniff. Don't worry about widget labels or validator error messages,
   * the sniffer is smart about those
   */
  private function i18nDummy()
  {
    __('Choose a User to Add', null, 'apostrophe');
    __('Home Page', null, 'apostrophe');
    __('Default Page', null, 'apostrophe');
    __('Template-Based', null, 'apostrophe');
    __('Media', null, 'apostrophe');
    __('Published', null, 'apostrophe');
    __('Unpublished', null, 'apostrophe');
    __('results', null, 'apostrophe');    
    __('Login Required', null, 'apostrophe');
  }
  
  protected $new = false;
  protected $parent = null;

  /**
   * If you are making a new page pass a new page object and set $parent also.
   * To edit an existing page, just set $page and leave $parent null.
   * At least one must not be null for normal use, but both can be null for
   * ai18nupdate
   * @param mixed $page
   * @param mixed $parent
   */
  public function __construct($page = null, $parent = null)
  {
    if (!$page)
    {
      $page = new aPage();
      if (!$parent)
      {
        echo("Creating parent object, this does not make sense unless you are running ai18nupdate\n");
        $parent = new aPage();
      }
    }
    if ($page->isNew())
    {
      $this->parent = $parent;
      $this->new = true;
    }
    parent::__construct($page);
    if ($this->getObject()->isNew())
    {
      $slug = $this->parent->slug;
      if (substr($slug, -1, 1) !== '/')
      {
        $slug .= '/';
      }
      $this->getWidget('slug')->setDefault($slug);
    }
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    parent::configure();    
    $manage = $this->getObject()->isNew() ? true : $this->getObject()->userHasPrivilege('manage');
   
    $user = sfContext::getInstance()->getUser();
    
    // $page->setArchived(!sfConfig::get('app_a_default_published', sfConfig::get('app_a_default_on', true)));
    
    // We must explicitly limit the fields because otherwise tables with foreign key relationships
    // to the pages table will extend the form whether it's appropriate or not. If you want to do
    // those things on behalf of an engine used in some pages, define a form class called
    // enginemodulenameEngineForm. It will automatically be instantiated with the engine page
    // as an argument to the constructor, and rendered beneath the main page settings form.
    // On submit, it will be bound to the parameter name that begins its name format and, if valid,
    // saved consecutively after the main page settings form. The form will be rendered via
    // the _renderPageSettingsForm partial in your engine module, which must exist, although it
    // can be as simple as echo $form. (Your form is passed to the partial as $form.)
    // 
    // We would use embedded forms if we could. Unfortunately Symfony has unresolved bugs relating
    // to one-to-many relations in embedded forms.
    
    $fields = array('slug', 'archived');
    if ($user->hasCredential('cms_admin'))
    {
      $fields[] = 'edit_admin_lock';
    }
    $this->useFields($fields);

    $object = $this->getObject();
    
    // The states we really have now are:
    // Public
    // Login required, with various settings plus a boolean for "guest" access
    // Admin only (for which we have added view_admin_lock)
    
    // The problem is that right now, public and "guest" are distinguished by a single boolean.
    // It's a pain to turn that into an enumeration. 
    
    // So we need an additional "view_guest" boolean consulted when "view_is_secure" is true, and
    // that new boolean should default to true.
    
    // In 2.0 we'll probably clean these flags up a bit.
    
    $choices = array('public' => 'Public', 'login' => 'Login Required', 'admin' => 'Admins Only');
    
    if (!$user->hasCredential('cms_admin'))
    {
      // You can't stop yourself and your peers from viewing a page
      unset($choices['admin']);
    }
    
    if (sfConfig::get('app_a_simple_permissions'))
    {
      unset($this['archived']);
      unset($this['view_is_secure']);
      unset($this['view_admin_lock']);
      $choices = array('everyone' => 'Everyone', 'guests' => 'Guests', 'editors' => 'Editors');
      
      if ($object['view_is_secure'])
      {
        if ($object['view_guest'])
        {
          $value = 'guests';
        }
        else
        {
          $value = 'editors';
        }
      } 
      else 
      {
        $value = 'everyone';
      }
      
      $this->setWidget('simple_status', new sfWidgetFormChoice(array('choices' => $choices, 'default' => $value)));
      $this->setValidator('simple_status', new sfValidatorChoice(array('choices' => array_keys($choices))));
    }
    else
    {
      if ($manage)
      {
        $default = 'public';
        if ($object->view_admin_lock)
        {
          $default = 'admin';
        }
        elseif ($object->view_is_secure)
        {
          $default = 'login';
        }
        
        $this->setWidget('view_options', new sfWidgetFormChoice(array('choices' => $choices, 'expanded' => true, 'default' => $default)));
        $this->setValidator('view_options', new sfValidatorChoice(array('choices' => array_keys($choices), 'required' => true)));
        if ($this->getObject()->hasChildren(false))
        {
          $this->setWidget('view_options_apply_to_subpages', new sfWidgetFormInputCheckbox(array('label' => 'Apply to Subpages')));
          $this->setValidator('view_options_apply_to_subpages', new sfValidatorBoolean(array(
            'true_values' =>  array('true', 't', 'on', '1'),
            'false_values' => array('false', 'f', 'off', '0', ' ', '')
          )));
        }
        $this->setWidget('view_individuals', new sfWidgetFormInputHidden(array('default' => $this->getViewIndividualsJSON())));
        $this->setValidator('view_individuals', new sfValidatorCallback(array('callback' => array($this, 'validateViewIndividuals'), 'required' => true)));
        $this->setWidget('view_groups', new sfWidgetFormInputHidden(array('default' => $this->getViewGroupsJSON())));
        $this->setValidator('view_groups', new sfValidatorCallback(array('callback' => array($this, 'validateViewGroups'), 'required' => true)));
      }
    }
    
    // Changed the name so Doctrine doesn't get uppity
    
    $engine = $object->engine;
    $template = $object->template;
    if (!strlen($object->template))
    {
      $object->template = 'default';
    }
    if (!is_null($object->engine))
    {
      $joinedTemplateName = $object->engine . ':' . $object->template;
    }
    else
    {
      $joinedTemplateName = 'a' . ':' . $object->template;
    }
    $choices = aTools::getTemplateChoices();
    $this->setWidget('joinedtemplate', new sfWidgetFormSelect(array('choices' => $choices, 'default' => $joinedTemplateName)));
    $this->setValidator('joinedtemplate', new sfValidatorChoice(array(
      'required' => true,
      'choices' => array_keys($choices)
    )));

    // Published vs. Unpublished makes more sense to end users, but when we first
    // designed this feature we had an 'archived vs. unarchived'
    // approach in mind
    $this->setWidget('archived', new sfWidgetFormChoice(array(
      'expanded' => true,
      'choices' => array(false => "Published", true => "Unpublished"),
      'default' => false
    )));

    if ($this->getObject()->hasChildren(false))
    {
      $this->setWidget('cascade_archived', new sfWidgetFormInputCheckbox());
      $this->setValidator('cascade_archived', new sfValidatorBoolean(array(
        'true_values' =>  array('true', 't', 'on', '1'),
        'false_values' => array('false', 'f', 'off', '0', ' ', '')
      )));
    }

    // Tags
    $options['default'] = implode(', ', $this->getObject()->getTags());  // added a space after the comma for readability
    if (sfConfig::get('app_a_all_tags', true))
    {
      $options['all-tags'] = PluginTagTable::getAllTagNameWithCount();
    }
    else
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers('Url');
      $options['typeahead-url'] = a_url('taggableComplete', 'complete');
    }
    $options['popular-tags'] = PluginTagTable::getPopulars(null, array('sort_by_popularity' => true), false, 10);
    $options['commit-selector'] = '#' . ($this->getObject()->isNew() ? 'a-create-page' : 'a-page-settings') . '-submit';
    $options['tags-label'] = '';
    // class tag-input enabled for typeahead support
    $this->setWidget('tags', new pkWidgetFormJQueryTaggable($options, array('class' => 'tags-input')));
    $this->setValidator('tags', new sfValidatorString(array('required' => false)));

    // Meta Description
    // Call the widget real_meta_description to avoid conflicts with the automatic behavior
    // of Doctrine forms (which will call setMetaDescription before the page is saved, something
    // that newAreaVersion does not support)
    $metaDescription = $this->getObject()->getMetaDescription();
    $this->setWidget('real_meta_description', new sfWidgetFormTextArea(array('default' => html_entity_decode($metaDescription, ENT_COMPAT, 'UTF-8'))));
    $this->setValidator('real_meta_description', new sfValidatorString(array('required' => false)));

    $privilegePage = $this->getObject();
    if ($privilegePage->isNew())
    {
      $privilegePage = $this->parent;
    }
    
    if ($user->hasCredential('cms_admin'))
    {
      $this->setWidget('edit_individuals', new sfWidgetFormInputHidden(array('default' => $this->getEditIndividualsJSON())));
      $this->setValidator('edit_individuals', new sfValidatorCallback(array('callback' => array($this, 'validateEditIndividuals'), 'required' => true)));
      $this->setWidget('edit_groups', new sfWidgetFormInputHidden(array('default' => $this->getEditGroupsJSON())));
      $this->setValidator('edit_groups', new sfValidatorCallback(array('callback' => array($this, 'validateEditGroups'), 'required' => true)));
    }
    
    // If you can delete the page, you can change the slug
    if ($manage)
    {
      $this->setValidator('slug', new aValidatorSlug(array('required' => true, 'allow_slashes' => true, 'require_leading_slash' => true), array('required' => 'The permalink cannot be empty.',
          'invalid' => 'The permalink must contain only slashes, letters, digits, dashes and underscores. There must be a leading slash. Also, you cannot change a permalink to conflict with an existing permalink.')));
      $this->setWidget('slug', new sfWidgetFormInputText());
    }

    // Named 'realtitle' to avoid excessively magic Doctrine form behavior.
    // Specifically, updateObject() will automatically call setTitle() if there
    // is such a method and a widget named 'title', and we don't want that to 
    // happen until after the page is saved so we can store it in a slot
    
    $this->setValidator('realtitle', new sfValidatorString(array('required' => true), array('required' => 'The title cannot be empty.')));

    $title = $this->getObject()->getTitle();
    $this->setWidget('realtitle', new sfWidgetFormInputText(array('default' => aHtml::toPlaintext($this->getObject()->getTitle(), ENT_COMPAT, 'UTF-8'))));
    
    // The slug of the home page cannot change (chicken and egg problems)
    if ($this->getObject()->getSlug() === '/')
    {
      unset($this['slug']);
    }
    else
    {
      $this->validatorSchema->setPostValidator(new sfValidatorDoctrineUnique(array(
        'model' => 'aPage',
        'column' => 'slug'
      ), array('invalid' => 'There is already a page with that slug.')));
    }
    
    $this->widgetSchema->setIdFormat('a_settings_%s');
    $this->widgetSchema->setNameFormat('settings[%s]');
    $this->widgetSchema->setFormFormatterName('list');

    // We changed the form formatter name, so we have to reset the translation catalogue too 
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }

  /**
   * DOCUMENT ME
   * @param mixed $forceParent
   * @return mixed
   */
  protected function getIndividualPermissions($forceParent = false)
  {
    $relativeId = ($this->getObject()->isNew() || $forceParent) ? $this->parent->id : $this->getObject()->id;
    return Doctrine::getTable('aPage')->getPrivilegesInfoForPageId($relativeId);
  }

  /**
   * DOCUMENT ME
   * @param mixed $forceParent
   * @return mixed
   */
  protected function getEditIndividualsJSON($forceParent = false)
  {
    $candidates = Doctrine::getTable('aPage')->getEditorCandidates();
    $infos = $this->getIndividualPermissions($forceParent);
    $jinfos = array();
    foreach ($candidates as $candidate)
    {
      $id = $candidate['id'];
      $jinfo = array('id' => $id, 'name' => $this->formatName($candidate), 'selected' => false, 'extra' => false, 'applyToSubpages' => false);
      if (isset($infos[$id]))
      {
        $info = $infos[$id];
        if (isset($info['privileges']['edit']) || isset($info['privileges']['manage']))
        {
          $jinfo['selected'] = true;
          $jinfo['extra'] = isset($info['privileges']['manage']);
        }
      }
      $jinfos[] = $jinfo;
    }
    return json_encode($jinfos);
  }

  /**
   * DOCUMENT ME
   * @param mixed $forceParent
   * @return mixed
   */
  protected function getGroupPermissions($forceParent = false)
  {
    $relativeId = ($this->getObject()->isNew() || $forceParent) ? $this->parent->id : $this->getObject()->id;
    return Doctrine::getTable('aPage')->getGroupPrivilegesInfoForPageId($relativeId);
  }

  /**
   * DOCUMENT ME
   * @param mixed $forceParent
   * @return mixed
   */
  protected function getEditGroupsJSON($forceParent = false)
  {
    $candidates = Doctrine::getTable('aPage')->getEditorCandidateGroups();
    $infos = $this->getGroupPermissions($forceParent);
    $jinfos = array();
    foreach ($candidates as $candidate)
    {
      $id = $candidate['id'];
      $jinfo = array('id' => $id, 'name' => $this->getGroupCandidateName($candidate), 'selected' => false, 'extra' => false, 'applyToSubpages' => false);
      if (isset($infos[$id]))
      {
        $info = $infos[$id];
        if (isset($info['privileges']['edit']) || isset($info['privileges']['manage']))
        {
          $jinfo['selected'] = true;
          $jinfo['extra'] = isset($info['privileges']['manage']);
        }
      }
      $jinfos[] = $jinfo;
    }
    return json_encode($jinfos);
  }

  /**
   * DOCUMENT ME
   * @param mixed $validator
   * @param mixed $value
   * @return mixed
   */
  public function validateEditIndividuals($validator, $value)
  {
    $values = json_decode($value, true);
    if (!is_array($values))
    {
      throw new sfValidatorError($validator, 'Bad permissions JSON');
    }
    $candidates = Doctrine::getTable('aPage')->getEditorCandidates();
    $candidates = aArray::listToHashById($candidates);
    foreach ($values as $info)
    {
      if (!isset($candidates[$info['id']]))
      {
        throw new sfValidatorError($validator, 'noncandidate');
      }
    }
    return $value;
  }

  /**
   * DOCUMENT ME
   * @param mixed $validator
   * @param mixed $value
   * @return mixed
   */
  public function validateEditGroups($validator, $value)
  {
    $values = json_decode($value, true);
    if (!is_array($values))
    {
      throw new sfValidatorError($validator, 'Bad permissions JSON');
    }
    $candidates = Doctrine::getTable('aPage')->getEditorCandidateGroups();
    $candidates = aArray::listToHashById($candidates);
    foreach ($values as $info)
    {
      if (!isset($candidates[$info['id']]))
      {
        throw new sfValidatorError($validator, 'noncandidate');
      }
    }
    return $value;
  }

  /**
   * DOCUMENT ME
   * @param mixed $forceParent
   * @return mixed
   */
  protected function getViewIndividualsJSON($forceParent = false)
  {
    $candidates = Doctrine::getTable('aPage')->getViewCandidates();
    $infos = $this->getIndividualPermissions($forceParent);
    $jinfos = array();
    foreach ($candidates as $candidate)
    {
      $id = $candidate['id'];
      $jinfo = array('id' => $id, 'name' => $this->formatName($candidate), 'selected' => false, 'applyToSubpages' => false);
      if (isset($infos[$id]))
      {
        $info = $infos[$id];
        if (isset($info['privileges']['view_custom']))
        {
          $jinfo['selected'] = true;
        }
      }
      $jinfos[] = $jinfo;
    }
    return json_encode($jinfos);
  }

  /**
   * We don't have a full sfGuardUser object so we can't stringify, must duplicate this logic for now. This is
   * not ideal from an I18N perspective
   * @param mixed $candidate
   * @return mixed
   */
  protected function formatName($candidate)
  {
    return $candidate['first_name'] . ' ' . $candidate['last_name'] . ' (' . $candidate['username'] . ')';
  }

  /**
   * DOCUMENT ME
   * @param mixed $forceParent
   * @return mixed
   */
  protected function getViewGroupsJSON($forceParent = false)
  {
    $candidates = Doctrine::getTable('aPage')->getViewCandidateGroups();
    $infos = $this->getGroupPermissions($forceParent);
    $jinfos = array();
    // Virtual group for Symfony 1.4-style "everyone who is cool" privilege based on the view_locked permission
    $jinfos[] = array('id' => 'editors_and_guests', 'name' => 'Editors & Guests', 'selected' => $this->getObject()->view_guest, 'applyToSubpages' => false);
    foreach ($candidates as $candidate)
    {
      $id = $candidate['id'];
      $jinfo = array('id' => $id, 'name' => $this->getGroupCandidateName($candidate), 'selected' => false, 'applyToSubpages' => false);
      if (isset($infos[$id]))
      {
        $info = $infos[$id];
        if (isset($info['privileges']['view_custom']))
        {
          $jinfo['selected'] = true;
        }
      }
      $jinfos[] = $jinfo;
    }
    return json_encode($jinfos);
  }

  /**
   * A useful override point if you prefer to show the description
   * @param mixed $groupCandidate
   * @return mixed
   */
  protected function getGroupCandidateName($groupCandidate)
  {
    return $groupCandidate['name'];
  }

  /**
   * DOCUMENT ME
   * @param mixed $validator
   * @param mixed $value
   * @return mixed
   */
  public function validateViewIndividuals($validator, $value)
  {
    $values = json_decode($value, true);
    if (!is_array($values))
    {
      throw new sfValidatorError($validator, 'Bad permissions JSON');
    }
    $candidates = Doctrine::getTable('aPage')->getViewCandidates();
    $candidates = aArray::listToHashById($candidates);
    foreach ($values as $info)
    {
      if (!isset($candidates[$info['id']]))
      {
        throw new sfValidatorError($validator, 'noncandidate');
      }
    }
    return $value;
  }

  /**
   * DOCUMENT ME
   * @param mixed $validator
   * @param mixed $value
   * @return mixed
   */
  public function validateViewGroups($validator, $value)
  {
    $values = json_decode($value, true);
    if (!is_array($values))
    {
      throw new sfValidatorError($validator, 'Bad permissions JSON');
    }
    $candidates = Doctrine::getTable('aPage')->getViewCandidateGroups();
    $candidates = aArray::listToHashById($candidates);
    foreach ($values as $info)
    {
      if ($info['id'] === 'editors_and_guests')
      {
        continue;
      }
      if (!isset($candidates[$info['id']]))
      {
        throw new sfValidatorError($validator, 'noncandidate');
      }
    }
    return $value;
  }

  /**
   * DOCUMENT ME
   * @param mixed $values
   */
  public function updateObject($values = null)
  {
    if (is_null($values))
    {
      $values = $this->getValues();
    }

    $oldSlug = $this->getObject()->slug;

    if ((!isset($values['slug'])) && (isset($values['realtitle'])) && ($oldSlug !== '/'))
    {
      // If they can manually edit the title but not the slug, we need to autogenerate and
      // autoupdate the slug so they have reasonable options to avoid collisions
      $oldSlug = $this->getObject()->slug;
      if (!strlen($oldSlug))
      {
        // New page, provide a starter slug to replace
        $oldSlug = $this->parent->slug . '/';
      }
      $newSlug = preg_replace('|/[^/]*$|', '/' . aTools::slugify($values['realtitle'], false, false), $oldSlug);
      $suffix = '';
      $n = 0;
      while (true)
      {
        $values['slug'] = $newSlug . $suffix;
        if ($values['slug'] === $oldSlug)
        {
          break;
        }
        $existing = Doctrine::getTable('aPage')->findOneBySlug($values['slug']);
        if (!$existing)
        {
          break;
        }
        $suffix = '-' . $n;
        $n++;
      }
      $this->getObject()->slug = $values['slug'];
    }
    
    // Slashes break routes in most server configs. Do NOT force case of tags.
    
    $values['tags'] = str_replace('/', '-', isset($values['tags']) ? $values['tags'] : '');

    $object = parent::updateObject($values);

    // Check for cascading operations
    if ($this->getValue('cascade_archived'))
    {
      $q = Doctrine::getTable('aPage')->createQuery()
        ->update()
        ->where('lft > ? and rgt < ?', array($object->getLft(), $object->getRgt()));
      if($this->getValue('cascade_archived'))
      {
        $q->set('archived', '?', $object->getArchived());
      }
      $q->execute();
    }

    if (isset($values['joinedtemplate']))
    {
      $template = $values['joinedtemplate'];
      // $templates = aTools::getTemplates();
      list($engine, $etemplate) = preg_split('/:/', $template);
      if ($engine === 'a')
      {
        $object->engine = null;
      }
      else
      {
        $object->engine = $engine;
      }
      $object->template = $etemplate;
    }
    
    // On manual change of slug, set up a redirect from the old slug,
    // and notify child pages so they can update their slugs if they are
    // not already deliberately different
    if ($object->slug !== $oldSlug)
    {
      Doctrine::getTable('aRedirect')->update($oldSlug, $object);
      $children = $object->getChildren();
      foreach ($children as $child)
      {
        $child->updateParentSlug($oldSlug, $object->slug);
      }
    }
    
    if (isset($object->engine) && (!strlen($object->engine)))
    {
      // Store it as null for plain ol' executeShow page templating
      $object->engine = null;
    }
    
    // A new page must be added as a child of its parent
    if ($this->parent)
    {
      $this->getObject()->getNode()->insertAsFirstChildOf($this->parent);
    }
    
    if (sfConfig::get('app_a_simple_permissions'))
    {
      $object['view_admin_lock'] = false;
      $object['archived'] = false;
      if ($values['simple_status'] === 'everyone')
      {
        $object['view_guest'] = false;
        $object['view_is_secure'] = false;
      }
      elseif ($values['simple_status'] === 'guests')
      {
        $object['view_guest'] = true;
        $object['view_is_secure'] = true;
      }
      elseif ($values['simple_status'] === 'editors')
      {
        $object['view_guest'] = false;
        $object['view_is_secure'] = true;
      }
    }
    else
    {
      $jvalues = json_decode($this->getValue('view_groups'), true);
      // Most custom permissions are saved in separate methods called from save()
      // after the object exists. However the "Editors + Guests" group is a special
      // case which really maps to everyone who has the 'view_locked' permission, so
      // we have to scan for it in the list of groups
      foreach ($jvalues as $value)
      {
        if ($value['id'] === 'editors_and_guests')
        {
          // Editors + Guests special case
          $object->view_guest = $value['selected'] && ($value['selected'] !== 'remove');
        }
      }
    
      // Check for cascading operations
      if ($this->getValue('cascade_archived'))
      {
        $q = Doctrine::getTable('aPage')->createQuery()
          ->update()
          ->where('lft > ? and rgt < ?', array($object->getLft(), $object->getRgt()));
        $q->set('archived', '?', $object->getArchived());
        $q->execute();
      }
    
      if ($values['view_options'] === 'public')
      {
        $object->view_admin_lock = false;
        $object->view_is_secure = false;
      }
      elseif ($values['view_options'] === 'login')
      {
        $object->view_admin_lock = false;
        $object->view_is_secure = true;
      }
      elseif ($values['view_options'] === 'admin')
      {
        $object->view_admin_lock = true;
        $object->view_is_secure = true;
      }
    
      if ($this->getValue('view_options_apply_to_subpages'))
      {
        $q = Doctrine::getTable('aPage')->createQuery()
          ->update()
          ->where('lft > ? and rgt < ?', array($object->getLft(), $object->getRgt()));
        $q->set('view_admin_lock', '?', $object->view_admin_lock);
        $q->set('view_is_secure', '?', $object->view_is_secure);
        $q->set('view_guest', '?', $object->view_guest);
        $q->execute();
      }
    }
    
    // We have no UI for scheduling publication yet, so make sure
    // we set the publication date when we save with archived false
    if ((!isset($values['archived'])) || (!$values['archived']))
    {
      $object->setPublishedAt(aDate::mysql());
    }
    
    // Has to be done on shutdown so it comes after the in-memory cache of
    // sfFileCache copies itself back to disk, which otherwise overwrites
    // our attempt to invalidate the routing cache [groan]
    register_shutdown_function(array($this, 'invalidateRoutingCache'));
  }

  /**
   * Privileges are saved after the object itself to avoid chicken and egg problems
   * if the page is new
   * @param mixed $con
   * @return mixed
   */
  public function save($con = null)
  {
    $object = parent::save($con);
    if (!sfConfig::get('app_a_simple_permissions'))
    {
      $this->saveIndividualViewPrivileges($object);
      $this->saveGroupViewPrivileges($object);
      $this->saveIndividualEditPrivileges($object);
      $this->saveGroupEditPrivileges($object);
    }
    // Update meta-description on Page
    // This involves creating a slot so it has to happen last
    if ($this->getValue('real_meta_description') != '')
    {
      $object->setMetaDescription(htmlentities($this->getValue('real_meta_description'), ENT_COMPAT, 'UTF-8'));
    }
    $this->getObject()->setTitle(htmlentities($this->getValue('realtitle'), ENT_COMPAT, 'UTF-8'));
    
    if ($this->new)
    {
      $event = new sfEvent($object, 'a.pageAdded', array());
      sfContext::getInstance()->getEventDispatcher()->notify($event);
    }
    
    return $object;
  }

  /**
   * DOCUMENT ME
   */
  public function invalidateRoutingCache()
  {
    // Clear the routing cache on page settings changes. TODO:
    // finesse this to happen only when the engine is changed,
    // and then perhaps further to clear only cache entries
    // relating to this page
    $routing = sfContext::getInstance()->getRouting();
    if ($routing)
    {
      $cache = $routing->getCache();
      if ($cache)
      {
        $cache->clean();
      }
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $object
   * @return mixed
   */
  protected function saveIndividualEditPrivileges($object)
  {
    if (isset($this['edit_individuals']))
    {
      $value = $this->getValue('edit_individuals');
    }
    elseif ($this->parent)
    {
      // We don't have the credentials to edit privileges, but we do need to
      // copy privileges when making a new page
      $value = $this->getEditIndividualsJSON(true);
    }
    else
    {
      return;
    }
    $values = json_decode($value, true);
    
    $t = Doctrine::getTable('aPage');
    if ($object->id)
    {
      $this->clearAccessForPrivilege($object->id, 'edit');
      $this->clearAccessForPrivilege($object->id, 'manage');
    }
    foreach ($values as $value)
    {
      if ($value['selected'] != '')
      {
        $this->setAccessForPrivilege($object, $value['id'], 'edit', ($value['selected'] === 'remove') ? false : true, $value['applyToSubpages']);
        $this->setAccessForPrivilege($object, $value['id'], 'manage', ($value['selected'] === 'remove') ? false : $value['extra'], $value['applyToSubpages']);
      }
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $object
   * @return mixed
   */
  protected function saveIndividualViewPrivileges($object)
  {
    if (isset($this['view_individuals']))
    {
      $value = $this->getValue('view_individuals');
    }
    elseif ($this->parent)
    {
      // We don't have the credentials to edit privileges, but we do need to
      // copy privileges when making a new page
      $value = $this->getViewIndividualsJSON(true);
    }
    else
    {
      return;
    }
    $values = json_decode($value, true);
    
    $t = Doctrine::getTable('aPage');
    if ($object->id)
    {
      $this->clearAccessForPrivilege($object->id, 'view_custom');
    }
    foreach ($values as $value)
    {
      if ($value['selected'] != '')
      {
        $this->setAccessForPrivilege($object, $value['id'], 'view_custom', ($value['selected'] === 'remove') ? false : true, $value['applyToSubpages']);
      }
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $pageId
   * @param mixed $privilege
   */
  protected function clearAccessForPrivilege($pageId, $privilege)
  {
    Doctrine_Query::create()->delete('aAccess')->where('page_id = ?', $pageId)->andWhere('privilege = ?', $privilege)->execute();
  }

  /**
   * DOCUMENT ME
   * @param mixed $pageId
   * @param mixed $privilege
   */
  protected function clearGroupAccessForPrivilege($pageId, $privilege)
  {
    Doctrine_Query::create()->delete('aGroupAccess')->where('page_id = ?', $pageId)->andWhere('privilege = ?', $privilege)->execute();
  }

  /**
   * DOCUMENT ME
   * @param mixed $object
   * @return mixed
   */
  protected function saveGroupEditPrivileges($object)
  {
    if (isset($this['edit_groups']))
    {
      $value = $this->getValue('edit_groups');
    }
    elseif ($this->parent)
    {
      // We don't have the credentials to edit privileges, but we do need to
      // copy privileges when making a new page
      $value = $this->getEditGroupsJSON(true);
    }
    else
    {
      return;
    }
    
    $values = json_decode($value, true);
    
    $t = Doctrine::getTable('aPage');
    if ($object->id)
    {
      $this->clearGroupAccessForPrivilege($object->id, 'edit');
      $this->clearGroupAccessForPrivilege($object->id, 'manage');
    }
    foreach ($values as $value)
    {
      if ($value['id'] === '')
      {
        // We dealt with the "Editors + Guests" special case in updateObject
        continue;
      }
      if ($value['selected'] != '')
      {
        $this->setGroupAccessForPrivilege($object, $value['id'], 'edit', ($value['selected'] === 'remove') ? false: true, $value['applyToSubpages']);
        $this->setGroupAccessForPrivilege($object, $value['id'], 'manage', ($value['selected'] === 'remove') ? false : $value['extra'], $value['applyToSubpages']);
      }
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $object
   * @return mixed
   */
  protected function saveGroupViewPrivileges($object)
  {
    if (isset($this['view_groups']))
    {
      $value = $this->getValue('view_groups');
    }
    elseif ($this->parent)
    {
      // We don't have the credentials to edit privileges, but we do need to
      // copy privileges when making a new page
      $value = $this->getViewGroupsJSON(true);
    }
    else
    {
      return;
    }
    
    $values = json_decode($value, true);
    
    $t = Doctrine::getTable('aPage');
    if ($object->id)
    {
      $this->clearGroupAccessForPrivilege($object->id, 'view_custom');
    }
    foreach ($values as $value)
    {
      // Ignore groups with non-integer IDs. This is a clean way to
      // allow special cases that aren't real groups, such as "Guests + Editors"
      if (preg_match('/^\d+$/', $value['id']))
      {
        if ($value['selected'] != '')
        {
          $this->setGroupAccessForPrivilege($object, $value['id'], 'view_custom', ($value['selected'] === 'remove') ? false: true, $value['applyToSubpages']);
        }
      }
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $page
   * @param mixed $userId
   * @param mixed $privilege
   * @param mixed $set
   * @param mixed $applyToSubpages
   */
  protected function setAccessForPrivilege($page, $userId, $privilege, $set, $applyToSubpages)
  {
    $ids = array();
    if ($applyToSubpages)
    {
      $results = Doctrine::getTable('aPage')->createQuery('p')->where('p.lft >= ? AND p.rgt <= ?', array($page->lft, $page->rgt))->select('p.id')->execute(array(), Doctrine::HYDRATE_SCALAR);
      foreach ($results as $result)
      {
        $ids[] = $result['p_id'];
      }
    }
    else
    {
      $ids = array($page->id);
    }
    if ($set)
    {
      foreach ($ids as $id)
      {
        $access = new aAccess();
        $access->user_id = $userId;
        $access->privilege = $privilege;
        $access->page_id = $id;
        $access->save();
        $access->free();
        unset($access);
      }
    }
    else
    {
      // Doctrine delete query syntax is a little odd
      Doctrine_Query::create()->delete('aAccess a')->andWhereIn('a.page_id', $ids)->andWhere('a.user_id = ?', $userId)->andWhere('a.privilege = ?', $privilege)->execute();
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $page
   * @param mixed $groupId
   * @param mixed $privilege
   * @param mixed $set
   * @param mixed $applyToSubpages
   */
  protected function setGroupAccessForPrivilege($page, $groupId, $privilege, $set, $applyToSubpages)
  {
    $ids = array();
    if ($applyToSubpages)
    {
      $results = Doctrine::getTable('aPage')->createQuery('p')->where('p.lft >= ? AND p.rgt <= ?', array($page->lft, $page->rgt))->select('p.id')->execute(array(), Doctrine::HYDRATE_SCALAR);
      foreach ($results as $result)
      {
        $ids[] = $result['p_id'];
      }
    }
    else
    {
      $ids = array($page->id);
    }
    if ($set)
    {
      foreach ($ids as $id)
      {
        $access = new aGroupAccess();
        $access->group_id = $groupId;
        $access->privilege = $privilege;
        $access->page_id = $id;
        $access->save();
        $access->free();
        unset($access);
      }
    }
    else
    {
      // Doctrine delete query syntax is a little odd
      Doctrine_Query::create()->delete('aGroupAccess a')->andWhereIn('a.page_id', $ids)->andWhere('a.group_id = ?', $groupId)->andWhere('a.privilege = ?', $privilege)->execute();
    }
  }
  
}
