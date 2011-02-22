<?php

class aSecurityUser extends sfGuardSecurityUser
{
  function clearCredentials()
  {
    parent::clearCredentials();
    $this->getAttributeHolder()->removeNamespace('apostrophe');
  }
}