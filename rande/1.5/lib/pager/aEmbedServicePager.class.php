<?php 

// Bridges the gap between sfPager and API service classes  like aYoutube and aVimeo 

class aEmbedServicePager extends sfPager 
{
  protected $service = null;
  protected $queryMethod = null;
  protected $query = null;
  protected $results = array();
  protected $offset = null;
  
  public function __construct()
  {
    // Dummy values
    parent::__construct('dummy', 5);
  }
  
  // The page # and # per page are arguments up front so that we can avoid
  // making double queries all the time - one just to get the total #,
  // the other to get the information we really want. APIs limit queries
  // so this is necessary
  public function setQuery($service, $queryMethod, $query, $page, $perPage)
  {
    $this->service = $service;
    $this->queryMethod = $queryMethod;
    $this->query = $query;
    $this->setPage($page);
    $this->setMaxPerPage($perPage);
    $this->offset = $page * $perPage;
  }
  
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
  
  public function getResults()
  {
    return $this->results['results'];
  }
  
  protected function retrieveObject($offset)
  {
    $offset -= $this->offset;
    if (($offset > 0) && ($offset < count($this->results['results'])))
    {
      return $this->results['results'][$offset];
    }
  }
}