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
   * renderPartial or renderComponent, or calling renderText.
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
   * IMPORTANT: for this to work you must have a renderTemplateViaPartial template and a
   * renderTemplateViaComponent template in your module. These should
   * execute:
   *
   * <?php include_partial($partial, $args) ?>
   *
   * And:
   *
   * <?php include_component($component[0], $component[1], $args) ?>
   *
   * Respectively.
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
    $key = $template . '_' . $this->lcfirst($suffix) . '_partial';
    if (isset($overrides[$key]))
    {
      $this->args = $this->getVarHolder()->getAll();
      $this->partial = $overrides[$key];
    }
    if (isset($partial))
    {
      $this->args = $this->getVarHolder()->getAll();
      return $this->renderPartial($partial, $this->getVarHolder()->getAll());
    }
    $key = $template . '_' . $this->lcfirst($suffix) . '_component';
    if (isset($overrides[$key]))
    {
      $this->component = $overrides[$key];
    }
    if (isset($this->partial) || isset($this->component))
    {
      $this->setTemplate('renderTemplateVia');
      if (isset($this->partial))
      {
        return 'Partial';
      }
      else
      {
        return 'Component';
      }
    }
    // Not overridden
    return $suffix;
  }

  /**
   * Careful, 5.2.x does not have a native lcfirst, we have to supply one sigh
   */
  protected function lcfirst($s)
  {
    return strtolower(substr($s, 0, 1)) . substr($s, 1);
  }
}