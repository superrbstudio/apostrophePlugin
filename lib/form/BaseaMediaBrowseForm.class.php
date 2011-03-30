<?php
/**
 * @package    apostrophePlugin
 * @subpackage    form
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaMediaBrowseForm extends BaseForm
{

  /**
   * Use this to i18n select choices that SHOULD be i18ned. It never gets called,
   * it's just here for our i18n-update task to sniff
   */
  private function i18nDummy()
  {
    __('All', null, 'apostrophe');
    __('Image', null, 'apostrophe');
    __('Video', null, 'apostrophe');
    __('PDF', null, 'apostrophe');
  }

  /**
   * DOCUMENT ME
   */
  public function configure()
  {
    $typeOptions = array(
      '' => 'All',
      'image' => 'Image',
      'video' => 'Video',
      'pdf' => 'PDF'
    );
    
    $allTags = TagTable::getAllTagNameWithCount(null, array('model' => 'aMediaItem'));

    $tagOptions = array();
    foreach ($allTags as $tag => $count)
    {
      $tagOptions[$tag] = "$tag ($count)";
    }
    
    $tagOptions = array_merge(array('' => 'All'), $tagOptions);
    
    $this->setWidgets(array(
      'search' => new sfWidgetFormInput(array(), array('class'=>'a-search-field',)),
      'type'   => new sfWidgetFormSelect(array('choices' => $typeOptions)),
      'tag'    => new sfWidgetFormSelect(array('choices' => $tagOptions))
    ));
    
    $this->setValidators(array(
      'search' => new sfValidatorPass(array('required' => false)),
      'type'   => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($typeOptions))),
      'tag'    => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($tagOptions)))
    ));
    
    // This is safe - it doesn't actually retrieve any extra
    // fields, it just declines to generate an error merely because
    // they exist
    $this->validatorSchema->setOptions('allow_extra_fields', true);
    $this->widgetSchema->setIdFormat('a_media_browser_%s');
    $this->widgetSchema->setFormFormatterName('aAdmin');

    // Yes, really: this makes it contextual without extra effort
    $this->widgetSchema->setNameFormat('%s');
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('apostrophe');
    
  }
}
