<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class aMinifyActions extends sfActions {

  public function executeSwitch(sfWebRequest $request) {
    $this->getUser()->setAttribute('a_minify_mode',!$this->getUser()->getAttribute('a_minify_mode',false));
    $this->redirect($request->getReferer());
  }  
}

?>
