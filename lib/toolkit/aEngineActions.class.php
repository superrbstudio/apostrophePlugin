<?php

class aEngineActions extends sfActions
{
  protected $page = null;
  
  public function preExecute()
  {
    $request = $this->getRequest();
    // Figure out where we are all over again, because there seems to be no clean way
    // to get the same controller-free URL that the routing engine gets. TODO:
    // ask Fabien how we can do that.
    $uri = $this->getRequest()->getUri();
    $uriPrefix = $this->getRequest()->getUriPrefix();
    $uri = substr($uri, strlen($uriPrefix));
    if (preg_match("/^\/[^\/]+\.php(.*)$/", $uri, $matches))
    {
      $uri = $matches[1];
    }
    // This will quickly fetch a result that was already cached when we 
    // ran through the routing table (unless we hit the routing table cache,
    // in which case we're looking it up for the first time, also OK)
    $page = aPageTable::getMatchingEnginePage($uri, $remainder);
    if (!$page)
    {
      throw new sfException('Attempt to access engine action without a page');
    }
    $page = aPageTable::retrieveByIdWithSlots($page->id);
    // We want to do these things the same way executeShow would
    aTools::validatePageAccess($this, $page);
    aTools::setPageEnvironment($this, $page);
    // Convenient access to the current page for the subclass
    $this->page = $page;
  }
}