<?php
/**
 * 
 * sfValidatorHtml validates an HTML string. It also converts the input value to a string.
 * It utilizes aHtml::simplify
 * @package    symfony
 * @subpackage validator
 * @author     Alex Gilbert <alex@punkave.com>
 * @author     Tom Boutell <tom@punkave.com>
 * @version    SVN: $Id: sfValidatorHtml.class.php 12641 2008-11-04 18:22:00Z fabien $
 */
class sfValidatorHtml extends sfValidatorString
{

  /**
   * 
   * Configures the current validator.
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addMessage('allowed_tags', 'Your field contains unsupported HTML tags.');

    // See aHtml::simplify for the meaning of these options
    $this->addOption('allowed_tags', null);
    $this->addOption('allowed_attributes', null);
    $this->addOption('allowed_styles', null);
    $this->addOption('complete', false);

    $this->addOption('strip', true);
    // Mandatory. We don't complain about HTML here, we clean it
    $this->setOption('strip', true);
    
    parent::configure($options, $messages);
  }

  /**
   * 
   * @see sfValidatorBase
   * @param mixed $value
   * @return mixed
   */
  protected function doClean($value)
  {
    $clean = (string) $value;

    if ($this->getOption('strip'))
    {
      $clean = aHtml::simplify($clean, $this->getOptionOrFalse('allowed_tags'), $this->getOptionOrFalse('complete'), $this->getOptionOrFalse('allowed_attributes'), $this->getOptionOrFalse('allowed_styles'));
    }
    else
    {
      throw new sfException('That should not happen strip is set in configure in sfValidatorHtml');
    }
    
    $clean = parent::doClean($clean);
    
    return $clean;
  }

  /**
   * aHtml::simplify uses false to skip things, not null
   * @param mixed $s
   * @return mixed
   */
  protected function getOptionOrFalse($s)
  {
    $option = $this->getOption($s);
    if (is_null($option))
    {
      return false;
    }
    return $option;
  }
}
