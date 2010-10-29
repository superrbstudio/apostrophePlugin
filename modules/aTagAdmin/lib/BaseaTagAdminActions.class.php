<?php

require_once dirname(__FILE__) . '/aTagAdminGeneratorConfiguration.class.php';
require_once dirname(__FILE__) . '/aTagAdminGeneratorHelper.class.php';

/**
 * Base actions for the aPlugin aTagAdmin module.
 *
 * @package     aPlugin
 * @subpackage  aTagAdmin
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class BaseaTagAdminActions extends autoaTagAdminActions
{
  public function preExecute()
  {
    parent::preExecute();
    $this->dispatcher->connect('admin.build_query', array($this, 'addCounts'));
  }

  public function addCounts($event, $query)
  {
    Doctrine::getTable('Tag')->queryTagsWithCountsByModel($this->configuration->getTaggableModels(), $query);
    
    return $query;
  }

	protected function buildQuery()
  {
    $tableMethod = $this->configuration->getTableMethod();
    $query = Doctrine::getTable('Tag')->createQuery('r');

    $this->addSortQuery($query);

    $event = $this->dispatcher->filter(new sfEvent($this, 'admin.build_query'), $query);
    $query = $event->getReturnValue();

    return $query;
  }

  public function executeClean(sfWebRequest $request)
  {
    $deleted = PluginTagTable::purgeOrphans();
    $count = count($deleted);
    $this->getUser()->setFlash('notice', "$count unused tags removed.");

    $this->redirect('a_tag_admin');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    parent::processForm($request, $form);
    
    $this->getUser()->setFlash('error', null);

    $taintedValues = $this->form->getTaintedValues();
    $mergeTo = Doctrine::getTable('Tag')->createQuery()
      ->where('id <> ? AND name = ?', array($this->tag->id, $taintedValues['name']))
      ->fetchOne();

    if($mergeTo)
    {
      Doctrine::getTable('Tag')->mergeTags($this->tag->id, $mergeTo->id);
      $this->tag->delete();

      $this->getUser()->setFlash('notice', $this->__(sprintf('Tag "%s" merged into tag "%s."', $this->tag->name, $mergeTo->name)));
      $this->redirect('a_tag_admin');
    }
    $this->getUser()->setFlash('error', $this->__('The item has not been saved due to some errors.', null, 'apostrophe'));
  }


}