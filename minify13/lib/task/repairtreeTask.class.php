<?php

class repairtreeTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'repair-tree';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [apostrohe:repair-tree|INFO] task rebuilds the Doctrine nested set tree of your site
based on the slugs of your pages. This will always work even if the nested set has
somehow become corrupted. The order of pages at the same level will NOT be
preserved, however parent-child relationships will be preserved, and you can
then clean up the mess with the reorganize tool.

Call it with:

  [php symfony a:repair-tree|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $pages = Doctrine::getTable('aPage')->createQuery('p')->orderBy('p.slug ASC')->execute();
    // Rebuild the nested set via direct access to lft, rgt and level based on what
    // we find in the slugs
    
    $pagesBySlug = array();
    foreach ($pages as $page)
    {
      $pagesBySlug[$page->slug] = $page;
    }
    $root = $pagesBySlug['/'];
    $tree = $this->buildSubtree($pages, $root);
    $lft = 1;
    $rgt = 1;
    $this->rebuildAdjacencyList($pagesBySlug, $tree, $rgt);
    $root->lft = $lft;
    $rgt++;
    $root->rgt = $rgt;
    $root->level = $this->getSlugLevel($root->slug);
    $root->save();
  }
  
  function buildSubtree($pages, $parent)
  {
    $tree = array();
    $slug = $parent->slug;
    if (substr($slug, -1, 1) !== '/')
    {
      $slug .= '/';
    }
    $level = $this->getSlugLevel($slug);
    // Find kids by slug. TODO: this is inefficient, we're making a lot of passes
    // over the full list, clever use of a simple alpha sort would reduce that
    
    // Careful: there's only one iterator, don't recurse inside here
    $kids = array();
    foreach ($pages as $page)
    {
      $pslug = $page->slug; 
      if (strpos($pslug, '/') === false)
      {
        // Leave the global page alone
        continue;
      }
      if (substr($pslug, 0, strlen($slug)) !== $slug)
      {
        continue;
      }
      if (($level + 1) !== $this->getSlugLevel($pslug))
      {
        continue;
      }
      
      $kids[] = $page;
    }
    foreach ($kids as $page)
    {
      $tree[$page->slug] = $this->buildSubtree($pages, $page);
    }
    return $tree;
  }  
  
  protected function rebuildAdjacencyList($pagesBySlug, $tree, &$rgt)
  {
    foreach ($tree as $slug => $subtree)
    {
      $rgt++;
      $lft = $rgt;
      $this->rebuildAdjacencyList($pagesBySlug, $subtree, $rgt);
      $page = $pagesBySlug[$slug];
      $page->lft = $lft;
      $rgt++;
      $page->rgt = $rgt;
      $page->level = $this->getSlugLevel($slug);
      $page->save();
    }
  }
    
  protected function getSlugLevel($slug)
  {
    if ($slug === '/')
    {
      return 0;
    }
    if (substr($slug, -1, 1) !== '/')
    {
      $slug .= '/';
    }
    return substr_count($slug, '/') - 1;
  }
}
