<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aEngineActions extends sfActions
{

  /**
   * DOCUMENT ME
   */
  public function preExecute()
  {
    aEngineTools::preExecute($this);
  }
}