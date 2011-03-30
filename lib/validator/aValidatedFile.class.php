<?php
/**
 * @package    apostrophePlugin
 * @subpackage    validator
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aValidatedFile extends sfValidatedFile
{

  /**
   * DOCUMENT ME
   * @param mixed $type
   * @param mixed $default
   * @return mixed
   */
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
