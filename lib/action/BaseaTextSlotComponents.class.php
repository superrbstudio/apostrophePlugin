<?php
/**
 * @package    apostrophePlugin
 * @subpackage    action
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class BaseaTextSlotComponents extends aSlotComponents
{

  /**
   * DOCUMENT ME
   */
  public function executeEditView()
  {
		$this->setup();
		$this->setupOptions();
		// Careful, sometimes we get an existing form from a previous validation pass
		if (!isset($this->form))
		{
			$this->form = new aTextForm($this->id, $this->slot->value, $this->options);
		}
  }

  /**
   * DOCUMENT ME
   */
  public function executeNormalView()
  {
		$this->setup();
		$this->setupOptions();
		// Yes, we store basic HTML markup for "plaintext" slots.
		// However we obfuscate the mailto links on the fly as a last step
		// (so it's not as fast as we originally intended, but this is an
		// essential feature that makes transformation of the code difficult).
		$this->value = aHtml::obfuscateMailto($this->value);
  }

  /**
   * DOCUMENT ME
   */
  protected function setupOptions()
  {
		$this->options['multiline'] = $this->getOption('multiline', true);
		$this->options['defaultText'] = $this->getOption('defaultText', a_('Click edit to add text.'));
	}
}
