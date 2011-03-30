<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaRenameForm extends BaseForm
{
  protected $page;

  /**
   * PARAMETERS ARE REQUIRED, no-parameters version is strictly to satisfy i18n-update
   * @param mixed $page
   */
  public function __construct($page = null)
  {
    if (!$page)
    {
      $page = new aPage();
    }
    $this->page = $page;
    parent::__construct();
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    $this->setWidget('title', new sfWidgetFormInputText(array('default' => html_entity_decode($this->page->getTitle(), ENT_COMPAT, 'UTF-8')), array('class' => 'epc-value a-breadcrumb-input')));
    $this->setValidator('title', new sfValidatorString(array('required' => true)));
    $this->widgetSchema->setNameFormat('aRenameForm[%s]');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe'); 
  }
}

