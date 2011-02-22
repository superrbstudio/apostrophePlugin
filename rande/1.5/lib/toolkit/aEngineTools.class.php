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
    $rr = preg_quote(sfContext::getInstance()->getRequest()->getRelativeUrlRoot(), '/');
    if (preg_match("/^(?:https?:\/\/[^\/]+)?$rr(?:\/[^\/]+\.php)?(.*)$/", $uri, $matches))
    {
      $uri = $matches[1];
    }
    else
    {
      throw new sfException("Unable to parse engine URL $uri");
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
    
    // If your engine supports allowing the user to choose from several page types
    // to distinguish different ways of using your engine, then you'll need to
    // return the template name from your show and index actions (and perhaps
    // others as appropriate). You can pull that information straight from
    // $this->page->template, or you can take advantage of $this->pageTemplate which
    // is ready to return as the result of an action (default has been changed
    // to Success, other values have their first letter capitalized)
    
    $templates = aTools::getTemplates();
    
    // originalTemplate is what's in the template field of the page, except that
    // nulls and empty strings from pre-1.5 Apostrophe have been converted to 'default'
    // for consistency
    $actions->originalTemplate = $page->template;
    if (!strlen($actions->originalTemplate))
    {
      // Compatibility with 1.4 templates and reasonable Symfony expectations
      $actions->originalTemplate = 'default';
    }
    
    // pageTemplate is suitable to return from an action. 'default' becomes 'Success'
    // (the Symfony standard for a "normal" template's suffix) and other values have
    // their first letter capitalized
    
    if ($actions->originalTemplate === 'default')
    {
      $actions->pageTemplate = 'Success';
    }
    else
    {
      $actions->pageTemplate = ucfirst($actions->originalTemplate);
    }
  }  
}
