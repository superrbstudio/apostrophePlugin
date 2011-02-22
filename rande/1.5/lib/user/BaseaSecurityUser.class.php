<?php

class BaseaSecurityUser extends sfGuardSecurityUser
{
  function clearCredentials()
  {
    parent::clearCredentials();
    $this->getAttributeHolder()->removeNamespace('apostrophe');
    $this->getAttributeHolder()->removeNamespace('apostrophe_media');
  }
}
