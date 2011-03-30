<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaFeedSlotComponents extends aSlotComponents
{

  /**
   * DOCUMENT ME
   */
  public function executeEditView()
  {
    // Must be at the start of both view components
    $this->setup();
    
    // Careful, don't clobber a form object provided to us with validation errors
    // from an earlier pass
    if (!isset($this->form))
    {
      $this->form = new aFeedForm($this->id, $this->slot->getArrayValue());
    }
  }

  /**
   * DOCUMENT ME
   */
  public function executeNormalView()
  {
    $this->setup();
    $this->values = $this->slot->getArrayValue();
    
    if (!empty($this->values['url']))
    {
      $this->url = $this->values['url'];
    
      $this->invalid = false;
      
      // This is a nice wrapper around sfCache and sfFeed
      $this->feed = aFeed::fetchCachedFeed($this->url, $this->getOption('interval', sfConfig::get('app_a_feed_interval', 300)));
      if ($this->feed === false)
      {
        $this->invalid = true;
      }

      $this->options['posts'] = $this->getOption('posts', 5);
      $this->options['links'] = $this->getOption('links', true);
      $this->options['markup'] = $this->getOption('markup', '<strong><em><p><br><ul><li><a>');
      $this->options['attributes'] = $this->getOption('attributes', false);
      $this->options['styles'] = $this->getOption('styles',false);
      $this->options['dateFormat'] = $this->getOption('dateFormat', false);
      $this->options['itemTemplate'] = $this->getOption('itemTemplate','aFeedItem');
    }
  }
}
