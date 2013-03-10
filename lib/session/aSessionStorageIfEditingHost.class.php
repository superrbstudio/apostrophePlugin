<?php

/**
 * Like plain ol' sfSessionStorage, but auto_start is jammed to false
 * if the user is not on the designated editing host
 */
class aSessionStorageIfEditingHost extends sfSessionStorage
{
  public function initialize($options = null)
  {
    if (!isset($options)) 
    {
      $options = array();
    }
    if ((!sfConfig::get('app_a_page_cache_enabled')) || (!sfConfig::get('app_a_page_cache_set_headers')))
    {
      return parent::initialize($options);
    }
    $editingHost = (sfContext::getInstance()->getRequest()->getHost() === sfConfig::get('app_a_page_cache_editing_host', 'none'));
    if ($options['auto_start']) 
    {
      $options['auto_start'] = $editingHost;
    }
    return parent::initialize($options);
  }
}
