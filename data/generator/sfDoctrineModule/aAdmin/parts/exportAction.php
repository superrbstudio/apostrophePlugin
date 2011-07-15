  public function executeExport(sfWebRequest $request)
  {
    $results = $this->buildQuery()->execute();
    
    $manager = $this->configuration->getExportManager($this->getResponse());
    
    $manager->export($results, $this->configuration->getExportDisplay(), $this->configuration->getExportTitle());
    
    return sfView::NONE;
  }