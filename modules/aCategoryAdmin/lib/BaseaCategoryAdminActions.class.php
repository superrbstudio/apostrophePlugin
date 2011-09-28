<?php

/**
 * 
 * Base actions for the aPlugin aCategoryAdmin module.
 * @package     aPlugin
 * @subpackage  aCategoryAdmin
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class BaseaCategoryAdminActions extends autoaCategoryAdminActions
{

  /**
   * DOCUMENT ME
   * @param sfWebRequest $request
   * @param sfForm $form
   * @return mixed
   */
  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $this->getUser()->setFlash('notice', $form->getObject()->isNew() ? $this->__('The item was created successfully.', null, 'apostrophe') : $this->__('The item was updated successfully.', null, 'apostrophe'));

      $a_category = $form->save();

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $a_category)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $this->getUser()->getFlash('notice') . ' ' . $this->__('You can add another one below.', null, 'apostrophe'));

        $this->redirect('@a_category_admin_new');
      } 
      elseif ($request->hasParameter('_save'))
      {
        $this->redirect('@a_category_admin_edit?id=' . $a_category->getId());
      }
      // save_and_list is the default
      else
      {
        $this->getUser()->setFlash('notice', $this->getUser()->getFlash('notice'));

        $this->redirect('@a_category_admin');
      }
    } else
    {
      $error = $this->form->getErrorSchema()->offsetGet('name');
      if (!($this->form->getObject()->isNew()) && $error && $error->getValidator() instanceof sfValidatorDoctrineUnique)
      {
        $taintedValues = $this->form->getTaintedValues();
        $newCategory = Doctrine::getTable('aCategory')->findOneBy('name', $taintedValues['name']);
        $conn = Doctrine_Manager::connection();
        try{
          $conn->beginTransaction();
          $this->dispatcher->notify(new sfEvent($this, 'a.merge_category', array('old_id' => $this->getRoute()->getObject()->id, 'new_id' => $newCategory->id)));
          $this->getRoute()->getObject()->delete();
          $conn->commit();
          $this->getUser()->setFlash('notice', $this->__(sprintf('Category %s merged into %s.', $this->getRoute()->getObject()->getName(), $newCategory->getName()), null, 'apostrophe'));
          return $this->redirect('aCategoryAdmin/index');
        } catch (Exception $e) {
          echo($e);
          $conn->rollback();
        }
      }

      $this->getUser()->setFlash('error', $this->__('The item has not been saved due to some errors.', null, 'apostrophe'));
    }
  }
}
