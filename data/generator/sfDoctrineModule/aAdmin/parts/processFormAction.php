  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $this->getUser()->setFlash('notice', $form->getObject()->isNew() ? sfContext::getInstance()->getI18N()->__('The item was created successfully.', null, 'apostrophe') : sfContext::getInstance()->getI18N()->__('The item was updated successfully.', null, 'apostrophe'));

      $<?php echo $this->getSingularName() ?> = $form->save();

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $<?php echo $this->getSingularName() ?>)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $this->getUser()->getFlash('notice').' ' . sfContext::getInstance()->getI18N()->__('You can add another one below.', null, 'apostrophe'));

        $this->redirect('@<?php echo $this->getUrlForAction('new') ?>');
      }
      else
      {
        $this->redirect('@<?php echo $this->getUrlForAction('edit') ?>?<?php echo $this->getPrimaryKeyUrlParams() ?>);
      }
    }
    else
    {
      $this->getUser()->setFlash('error', sfContext::getInstance()->getI18N()->__('The item has not been saved due to some errors.', null, 'apostrophe'));
    }
  }
