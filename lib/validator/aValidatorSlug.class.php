<?php
/**
 * 
 * aValidatorSlug validates a user-supplied slug in a UTF-8 aware fashion.
 * It allows slashes if the 'allow-slashes' option is set to true. Otherwise it allows
 * only letters and digits. It does not enforce a leading slash or look for
 * duplication, use other validators for those things if they apply to your
 * application
 * @package    symfony
 * @subpackage validator
 * @author     Tom Boutell <tom@punkave.com>
 * @version    SVN: $Id: sfValidatorHtml.class.php 12641 2008-11-04 18:22:00Z fabien $
 */
class aValidatorSlug extends sfValidatorString
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
    // Typically you'll set both of these if you're using it for page slugs,
    // and set neither for media item or blog post slugs
    $this->addOption('allow_slashes', false);
    $this->addOption('require_leading_slash', false);
    // If strict is false, doClean will just clean the slug (potentially changing it).
    // If strict is true, it will reject slugs that are not already clean.
    // The latter is probably best when users are explicitly editing slugs
    $this->addOption('strict', true);
    
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
    $clean = (string) parent::doClean($value);
    $clean = aString::strtolower($clean);
    $slugified = aTools::slugify($clean, $this->getOption('allow_slashes'));
    if ($this->getOption('strict'))
    {
      if ($slugified !== $clean)
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
    }
    else
    {
      $clean = $slugified;
    }
    if ($this->getOption('require_leading_slash') && (substr($value, 0, 1) !== '/'))
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }
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
