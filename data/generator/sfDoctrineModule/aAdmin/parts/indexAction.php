  public function executeIndex(sfWebRequest $request)
  {
    // sorting
    if ($request->getParameter('sort'))
    {
      $this->setSort(array($request->getParameter('sort'), $request->getParameter('sort_type')));
    }

    // pager
    if ($request->getParameter('page'))
    {
      $this->setPage($request->getParameter('page'));
    }

    $this->pager = $this->getPager();
    $this->sort = $this->getSort();

    aTools::setAllowSlotEditing(false);

    $defaults = $this->configuration->getFilterDefaults();
    $filters = $this->getFilters();
    // There is no really great way to determine whether the filters differ from the defaults
    $this->filtersActive = false;
    foreach ($filters as $key => $val)
    {
      if (isset($defaults[$key]))
      {
        $this->filtersActive = true;
      }
      else
      {
        if (!$this->isEmptyFilter($val))
        {
          $this->filtersActive = true;
        }
      }
    }
  }
  
  protected function isEmptyFilter($val)
  {
    if (!$val)
    {
      return true;
    }
    if (is_array($val))
    {
      foreach ($val as $v)
      {
        if ($v)
        {
          return false;
        }
      }
      return true;
    }
  }
