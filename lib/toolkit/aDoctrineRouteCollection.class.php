<?php
/**
 * 
 * aDoctrineRouteCollection represents a collection of routes bound to Doctrine objects via aDoctrineRoute
 * for use in an Apostrophe engine module.
 * 
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aDoctrineRouteCollection extends sfObjectRouteCollection
{
  protected $routeClass = 'aDoctrineRoute';

  /**
   * DOCUMENT ME
   * @param array $options
   */
  public function __construct(array $options)
  {
    // Prefix path is always empty since the engine page already brought us here
    $options['prefix_path'] = '';
    parent::__construct($options);
  }

  /**
   * Special case: the root route has to be /, even though we actually don't have a leading / on the index action of a home page
   * @return mixed
   */
  protected function getRouteForList()
  {
    return new $this->routeClass(
      '/.:sf_format',
      array_merge(array('module' => $this->options['module'], 'action' => $this->getActionMethod('list'), 'sf_format' => 'html'), $this->options['default_params']),
      array_merge($this->options['requirements'], array('sf_method' => 'get')),
      array('model' => $this->options['model'], 'type' => 'list', 'method' => $this->options['model_methods']['list'])
    );
  }
}