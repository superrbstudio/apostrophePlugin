<?php
require_once dirname(__FILE__).'/aCategoryAdminGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/aCategoryAdminGeneratorHelper.class.php';
/**
 * Base actions for the aPlugin aCategoryAdmin module.
 * 
 * @package     aPlugin
 * @subpackage  aCategoryAdmin
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class BaseaCategoryAdminActions extends autoaCategoryAdminActions
{
  public function preExecute()
  {
    parent::preExecute();
    // Loading assets here is inappropriate call use_helper('a') anywhere you need them
  }
}
