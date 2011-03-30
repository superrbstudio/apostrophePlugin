<?php
/**
 * 
 * aPage form.
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfPropelFormTemplate.php 10377 2008-07-21 07:10:32Z dwhittle $
 */
class aPageForm extends BaseaPageForm
{

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
  }
}
