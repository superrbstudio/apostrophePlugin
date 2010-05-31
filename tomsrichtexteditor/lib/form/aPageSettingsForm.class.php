<?php

// TODO: move the post-validation cleanup of the slug into the
// validator so that we don't get a user-unfriendly error or
// failure when /Slug Foo fails to be considered a duplicate
// of /slug_foo the first time around

class aPageSettingsForm extends aPageForm
{
  // Use this to i18n select choices that SHOULD be i18ned and other things that the
  // sniffer would otherwise miss. It never gets called, it's just here for our i18n-update 
  // task to sniff. Don't worry about widget labels or validator error messages,
  // the sniffer is smart about those
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
  
  public function configure()
  {
    parent::configure();
    
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
    
    $this->useFields(array('slug', 'template', 'engine', 'archived', 'view_is_secure'));
    
    unset(
      $this['author_id'],
      $this['deleter_id'],
      $this['Accesses'],
      $this['created_at'],
      $this['updated_at'],
      $this['view_credentials'],
      $this['edit_credentials'],
      $this['lft'],
      $this['rgt'],
      $this['level']
    );

    $this->setWidget('template', new sfWidgetFormSelect(array('choices' => aTools::getTemplates())));
     
    $this->setWidget('engine', new sfWidgetFormSelect(array('choices' => aTools::getEngines())));

    // On vs. off makes more sense to end users, but when we first
    // designed this feature we had an 'archived vs. unarchived'
    // approach in mind
    $this->setWidget('archived', new sfWidgetFormChoice(array(
      'expanded' => true,
      'choices' => array(false => "Published", true => "Unpublished"),
      'default' => false
    )));

    if ($this->getObject()->hasChildren())
    {
      $this->setWidget('cascade_archived', new sfWidgetFormInputCheckbox());
      $this->setValidator('cascade_archived', new sfValidatorBoolean(array(
        'true_values' =>  array('true', 't', 'on', '1'),
        'false_values' => array('false', 'f', 'off', '0', ' ', '')
      )));
      $this->setWidget('cascade_view_is_secure', new sfWidgetFormInputCheckbox());
      $this->setValidator('cascade_view_is_secure', new sfValidatorBoolean(array(
        'true_values' =>  array('true', 't', 'on', '1'),
        'false_values' => array('false', 'f', 'off', '0', ' ', '')
      )));
    }

    $this->setWidget('view_is_secure', new sfWidgetFormChoice(array(
      'expanded' => true,
      'choices' => array(
        false => "Public",
        true => "Login Required"
      ),
      'default' => false
    )));

    $this->addPrivilegeWidget('edit', 'editors');
    $this->addPrivilegeWidget('manage', 'managers');

    $this->setValidator('slug', new aValidatorSlug(array('required' => true, 'allow_slashes' => true), array('required' => 'The slug cannot be empty.',
        'invalid' => 'The slug must contain only slashes, letters, digits, dashes and underscores. Also, you cannot change a slug to conflict with an existing slug.')));

    $this->setValidator('template', new sfValidatorChoice(array(
      'required' => true,
      'choices' => array_keys(aTools::getTemplates())
    )));

    // Making the empty string one of the choices doesn't seem to be good enough
    // unless we expressly clear 'required'
    $this->setValidator('engine', new sfValidatorChoice(array(
      'required' => false,
      'choices' => array_keys(aTools::getEngines())
    )));   

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

    $user = sfContext::getInstance()->getUser();
    if (!$user->hasCredential('cms_admin'))
    {
      unset($this['editors']);
      unset($this['managers']);
      unset($this['slug']);
    }
    // We changed the form formatter name, so we have to reset the translation catalogue too 
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }
  
  protected function addPrivilegeWidget($privilege, $widgetName)
  {
    // For i18n-update we need to tolerate being run without a proper page
    if ($this->getObject()->isNew())
    {
      $all = array();
      $selected = array();
      $inherited = array();
      $sufficient = array();
    }
    else
    {
      list($all, $selected, $inherited, $sufficient) = $this->getObject()->getAccessesById($privilege);
    }
    foreach ($inherited as $userId)
    {
      unset($all[$userId]);
    }

    foreach ($sufficient as $userId)
    {
      unset($all[$userId]);
    }

    $this->setWidget($widgetName, new sfWidgetFormSelect(array(
      // + operator is correct: we don't want renumbering when
      // ids are numeric
      'choices' => $all,
      'multiple' => true,
      'default' => $selected
    )));

    $this->setValidator($widgetName, new sfValidatorChoice(array(
      'required' => false, 
      'multiple' => true,
      'choices' => array_keys($all)
    )));
  }

  public function updateObject($values = null)
  {
    $oldSlug = $this->getObject()->slug;
    $object = parent::updateObject($values);
    
    // Check for cascading operations
    if($this->getValue('cascade_archived') || $this->getValue('cascade_view_is_secure'))
    {
      $q = Doctrine::getTable('aPage')->createQuery()
        ->update()
        ->where('lft > ? and rgt < ?', array($object->getLft(), $object->getRgt()));
      if($this->getValue('cascade_archived'))
      {
        $q->set('archived', '?', $object->getArchived());
      }
      if($this->getValue('cascade_view_is_secure'))
      {
        $q->set('view_is_secure', '?', $object->getViewIsSecure());
      }
      $q->execute();
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
    $this->savePrivileges($object, 'edit', 'editors');
    $this->savePrivileges($object, 'manage', 'managers');
    
    // Has to be done on shutdown so it comes after the in-memory cache of
    // sfFileCache copies itself back to disk, which otherwise overwrites
    // our attempt to invalidate the routing cache [groan]
    register_shutdown_function(array($this, 'invalidateRoutingCache'));
  }
  
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
  
  protected function savePrivileges($object, $privilege, $widgetName)
  {
    if (isset($this[$widgetName]))
    {
      $editorIds = $this->getValue($widgetName);
      // Happens when the list is empty (sigh)
      if ($editorIds === null)
      {
        $editorIds = array();
      }
      
      $object->setAccessesById($privilege, $editorIds);
    }
  }
}
