<?php

class aEngineTools
{
  // Poor man's multiple inheritance. This allows us to subclass an existing
  // actions class in order to create an engine version of it. See aEngineActions
  // for the call to add to your own preExecute method
  
  static public function preExecute($actions)
  {
    $request = $actions->getRequest();
    // Figure out where we are all over again, because there seems to be no clean way
    // to get the same controller-free URL that the routing engine gets. TODO:
    // ask Fabien how we can do that.
    $uri = urldecode($actions->getRequest()->getUri());
    $uriPrefix = $actions->getRequest()->getUriPrefix();
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
    aTools::validatePageAccess($actions, $page);
    aTools::setPageEnvironment($actions, $page);
    // Convenient access to the current page for the subclass
    $actions->page = $page;
  }  
}
