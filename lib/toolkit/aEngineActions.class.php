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

  /**
   * Return the result of calling this instead of just returning (including returning by default at the end) or
   * returning a template name. This allows the template to be overridden by a partial or
   * component anywhere via app.yml. Do NOT call this if you are redirecting, calling
   * renderPartial or renderComponen, or calling renderText.
   *
   * It's just for "normal" returns of a template, with or without an alternate suffix.
   * If setTemplate is used then the app.yml key is different accordingly.
   *
   * Configure like this:
   *
   * all:
   *   aMedia:
   *     templateOverrides:
   *       index_success_partial: myModule/myPartial
   *
   * OR like this:
   *
   * all:
   *   aMedia:
   *     templateOverrides:
   *       index_success_component: [ myModule, myComponent ]
   *
   * Please don't ask me why Symfony calls partials and components differently, it just does (:
   *
   * If you don't utilize this, nothing terrible will happen. You just won't be able to
   * override templates via app.yml.
   */

  public function renderTemplate($suffix = 'Success')
  {
    $module = $this->getModuleName();
    $template = $this->getTemplate();
    if (!$template)
    {
      $template = $this->getActionName();
    }
    $overrides = sfConfig::get('app_' . $module . '_templateOverrides', array());
    $key = $template . '_' . lcfirst($suffix) . '_partial';
    if (isset($overrides[$key]))
    {
      $partial = $overrides[$key];
    }
    if (isset($partial))
    {
      return $this->renderPartial($partial, $this->getVarHolder()->getAll());
    }
    $key = $template . '_' . lcfirst($suffix) . '_component';
    if (isset($overrides[$key]))
    {
      $component = $overrides[$key];
    }
    if (isset($component))
    {
      // Don't ask me why components don't have the same syntax, but they don't
      return $this->renderComponent($component[0], $component[1], $this->getVarHolder()->getAll());
    }
    // Not overridden
    return $suffix;
  }
}