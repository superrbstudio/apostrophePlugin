<?php

require_once dirname(__FILE__).'/../lib/BaseaUserAdminActions.class.php';
require_once dirname(__FILE__).'/../lib/aUserAdminGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/aUserAdminGeneratorHelper.class.php';/**
 * 
 * aUserAdmin actions.
 * @package    sfShibbolethPlugin
 * @subpackage aUserAdmin
 * @author     Fabien Potencier
 * @version    SVN: $Id: actions.class.php 12896 2008-11-10 19:02:34Z fabien $
 */
class aUserAdminActions extends BaseaUserAdminActions
{

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function buildQuery()
  {
    $query = parent::buildQuery();
    // This user is for running scheduled tasks only. It must remain a superuser and
    // should never be marked active or have a known password (it has a randomly generated
    // password just in case someone somehow marks it active). So we hide it from 
    // the admin panel, where nothing good could ever happen to it.
    $query->andWhere($query->getRootAlias() . '.username <> ?', array('ataskuser'));
    return $query;
  }
}
