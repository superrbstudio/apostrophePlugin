  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $this->getUser()->setFlash('notice', $form->getObject()->isNew() ? $this->__('The item was created successfully.', null, 'apostrophe') : $this->__('The item was updated successfully.', null, 'apostrophe'));

      $<?php echo $this->getSingularName() ?> = $form->save();

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $<?php echo $this->getSingularName() ?>)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $this->getUser()->getFlash('notice').' ' . $this->__('You can add another one below.', null, 'apostrophe'));

        $this->redirect('@<?php echo $this->getUrlForAction('new') ?>');
      }
      elseif ($request->hasParameter('_save'))
      {
        $this->redirect('@<?php echo $this->getUrlForAction('edit') ?>?<?php echo $this->getPrimaryKeyUrlParams() ?>);
      }
      // The default is _save_and_list
      else
      {
        $this->getUser()->setFlash('notice', $this->getUser()->getFlash('notice'));

        $this->redirect('@<?php echo $this->getUrlForAction('list') ?>');
      }
    }
    else
    {
      $this->getUser()->setFlash('error', $this->__('The item has not been saved due to some errors.', null, 'apostrophe'));
    }
  }

  protected function __($s, $params, $catalogue)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    return __($s, $params, $catalogue);
  }
