<?php

/**
 * Automatically supplies the http:// if the user types
 * something that starts with a reasonable hostname. Otherwise
 * identical to sfValidatorUrl
 */

class aValidatorUrl extends sfValidatorUrl
{
  protected function doClean($value)
  {
    if (preg_match('/^[\w\+-]+\./', $value))
    {
      $value = 'http://' . $value;
    }
    return parent::doClean($value);
  }
}
