<?php
/**
 * 
 * aAdmin actions.
 * This engine is assigned to the /admin page, which is currently not used, so it just
 * redirects to the home page. We're reserving this conceptual space for future
 * administrative things. Note that engines installed on pages below /admin, such
 * as the aMedia engine which is always present at /admin/media (and sometimes
 * elsewhere if you so choose), will still work fine because Apostrophe always looks
 * for the longest match when searching the page table for matching engines.
 * @package    asandbox
 * @subpackage aAdmin
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class BaseaAdminActions extends aEngineActions
{

  /**
   * 
   * Executes index action
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    // TODO WHY DOESN'T THIS FIRE? THE ADMIN PAGE IS CURRENTLY ACCESSIBLE!
    $this->redirect('@homepage');
  }
}
