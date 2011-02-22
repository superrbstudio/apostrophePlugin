<?php

class aValidatedFile extends sfValidatedFile
{
  protected function getExtensionFromType($type, $default = '')
  {
    $extensionsByMimeType = array_flip(aMediaTools::getOption('mime_types'));
    if (isset($extensionsByMimeType[$type]))
    {
      return '.' . $extensionsByMimeType[$type];
    }
    return $default;
  }
}
