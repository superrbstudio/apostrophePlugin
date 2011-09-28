<?php

require_once dirname(__FILE__) . '/aTagAdminGeneratorConfiguration.class.php';
require_once dirname(__FILE__) . '/aTagAdminGeneratorHelper.class.php';

/**
 * 
 * Base actions for the aPlugin aTagAdmin module.
 * @package     aPlugin
 * @subpackage  aTagAdmin
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class BaseaTagAdminActions extends autoaTagAdminActions
{
  protected $models;

  /**
   * DOCUMENT ME
   */
  public function preExecute()
  {
    parent::preExecute();
    $this->models = $this->configuration->getTaggableModels();
    $this->dispatcher->connect('admin.build_query', array($this, 'addCounts'));
  }

  /**
   * DOCUMENT ME
   * @param mixed $event
   * @param mixed $query
   * @return mixed
   */
  public function addCounts($event, $query)
  {
    Doctrine::getTable('Tag')->queryTagsWithCountsByModel($this->models, $query);
    
    return $query;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function buildQuery()
  {
    $tableMethod = $this->configuration->getTableMethod();
    $query = Doctrine::getTable('Tag')->createQuery('r');

    $event = $this->dispatcher->filter(new sfEvent($this, 'admin.build_query'), $query);
    $this->addSortQuery($query);

    $query = $event->getReturnValue();

    return $query;
  }

  /**
   * DOCUMENT ME
   * @param mixed $query
   * @return mixed
   */
  protected function addSortQuery($query)
  {
    if (array(null, null) == ($sort = $this->getSort()))
    {
      return;
    }
 
    if (!in_array(strtolower($sort[1]), array('asc', 'desc')))
    {
      $sort[1] = 'asc';
    }
    
 
    if (preg_match('/^tag_(\w+)/', $sort[0], $matches))
    {
      $model = $matches[1];
      if (in_array($model, $this->models))
      {
          // Can act as a filter too, but I think that's inconsistent 
          // $query->andWhere('tg.taggable_model = ?', array($model));
        $query->addOrderBy($model . 'Count ' . $sort[1]);
      }
    }
    else
    {
      $query->addOrderBy($sort[0] . ' ' . $sort[1]);
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $column
   * @return mixed
   */
  protected function isValidSortColumn($column)
  {
    return Doctrine_Core::getTable('Tag')->hasColumn($column) || in_array($column, $this->models);
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   */
  public function executeClean(sfWebRequest $request)
  {
    $deleted = PluginTagTable::purgeOrphans();
    $count = count($deleted);
    $this->getUser()->setFlash('notice', "$count unused tags removed.");

    $this->redirect('a_tag_admin');
  }

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @param sfForm $form
   */
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