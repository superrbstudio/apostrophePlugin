<?php /**
 * Bridges the gap between sfPager and API service classes  like aYoutube and aVimeo
 * @package    apostrophePlugin
 * @subpackage    pager
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aEmbedServicePager extends sfPager 
{
  protected $service = null;
  protected $queryMethod = null;
  protected $query = null;
  protected $results = array();
  protected $offset = null;

  /**
   * DOCUMENT ME
   */
  public function __construct()
  {
    // Dummy values
    parent::__construct('dummy', 5);
  }

  /**
   * The page # and # per page are arguments up front so that we can avoid
   * making double queries all the time - one just to get the total #,
   * the other to get the information we really want. APIs limit queries
   * so this is necessary
   * @param mixed $service
   * @param mixed $queryMethod
   * @param mixed $query
   * @param mixed $page
   * @param mixed $perPage
   */
  public function setQuery($service, $queryMethod, $query, $page, $perPage)
  {
    $this->service = $service;
    $this->queryMethod = $queryMethod;
    $this->query = $query;
    $this->setPage($page);
    $this->setMaxPerPage($perPage);
    $this->offset = $page * $perPage;
  }

  /**
   * DOCUMENT ME
   */
  public function init()
  {
    $queryMethod = $this->queryMethod;
    $this->results = $this->service->$queryMethod($this->query, $this->getPage(), $this->getMaxPerPage());
    $this->setNbResults($this->results['total']);
    
    // Borrowed from sfArrayPager
    if (($this->getPage() == 0 || $this->getMaxPerPage() == 0))
    {
     $this->setLastPage(0);
    }
    else
    {
     $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));
    }
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getResults()
  {
    return $this->results['results'];
  }

  /**
   * DOCUMENT ME
   * @param mixed $offset
   * @return mixed
   */
  protected function retrieveObject($offset)
  {
    $offset -= $this->offset;
    if (($offset > 0) && ($offset < count($this->results['results'])))
    {
      return $this->results['results'][$offset];
    }
  }
}