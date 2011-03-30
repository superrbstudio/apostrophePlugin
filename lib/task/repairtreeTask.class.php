<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class repairtreeTask extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
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
      new sfCommandOption('method', null, sfCommandOption::PARAMETER_OPTIONAL, 'The repair method', null),
      new sfCommandOption('csv', null, sfCommandOption::PARAMETER_OPTIONAL, 'A CSV file containing id, lft, rgt and level for each page', null),
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'repair-tree';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [apostrophe:repair-tree|INFO] task rebuilds the Doctrine nested set tree of your site.
This can be done in three ways: (1) based on the slugs of your pages, which will always work 
even if the nested set has somehow become corrupted but discards order of pages at the same 
level, (2) by renumbering the adjacency list, which will halt the inconsistencies but
not repair any strangeness you are already seeing in the page tree - it just puts the
database back in a consistent state so you can start making those repairs safely with the
reorganize feature, or (3) by loading a CSV file of id, lft, rgt and level values from a
known-good database, with any new pages becoming children of the homepage so you can
find and move them.

With option #3 this SQL is helpful to capture the values from the good database:

select id, lft, rgt, level into outfile '/tmp/order.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' ESCAPED BY '\\' LINES TERMINATED BY '\n' FROM a_page;

Note that if you have aggressively edited your page slugs to create landing page URLs without
additional slashes in them, the 'slug' option may give you a home page with many children.

Any way you do it, you'll be spending some quality time with the 'reorganize' feature.

--method=list is the default.

Call it with:

  [php symfony apostrophe:repair-tree --method=(slug|list) OR --csv=csvfile.csv|INFO]
EOF;
  }

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    // Doctrine can't save with array hydration, and we're sure to run out of memory with objects,
    // so we use PDO-based methods for db access

    if ($options['csv'])
    {
      if (!file_exists($options['csv']))
      {
        throw new sfException("Specified csv file not found");
      }
      $this->repairByCsv($options['csv']);
    }
    elseif ($options['method'] === 'slug')
    {
      $this->repairBySlug();
    }
    else
    {
      $this->repairByList();
    }
  }

  /**
   * DOCUMENT ME
   */
  protected function repairBySlug()
  {
    // TODO review this, make sure the new PDO version works
    echo("Fetching pages\n");
    $pages = $this->query('SELECT id, lft, rgt, level, slug FROM a_page');
    
    // Rebuild the nested set via direct access to lft, rgt and level based on what
    // we find in the slugs
    
    $pagesBySlug = array();
    $total = count($pages);
    echo("There are $total pages\n");
    foreach ($pages as $page)
    {
      $pagesBySlug[$page['slug']] = $page;
    }
    
    $root = &$pagesBySlug['/'];
    
    echo("Building subtree\n");
    $tree = $this->buildSubtree($pages, $root);
    $lft = 1;
    $rgt = 1;
    echo("Rebuilding adjacency list from slugs\n");
    $this->rebuildAdjacencyList($pagesBySlug, $tree, $rgt);
    $rgt++;
    $root['lft'] = $lft;
    $root['rgt'] = $rgt;
    $root['level'] = 1;
    $this->query('UPDATE a_page SET lft = :lft, rgt = :rgt, level = :level WHERE id = :id', $root);
  }

  /**
   * DOCUMENT ME
   * @param mixed $csv
   */
  protected function repairByCsv($csv)
  {
    $csvFile = fopen($csv, "r");
    while (true)
    {
      $info = fgetcsv($csvFile);
      if (!$info)
      {
        break;
      }
      $infos[] = $info;
    }
    fclose($csvFile);
    $this->query('UPDATE a_page SET lft = -1, rgt = -1 WHERE lft > 0');
    foreach ($infos as $info)
    {
      $update = array('id' => $info[0], 'lft' => $info[1], 'rgt' => $info[2], 'level' => $info[3]);
      $this->query('UPDATE a_page SET lft = :lft, rgt = :rgt, level = :level WHERE id = :id', $update);
    }
    $roots = $this->query('SELECT * FROM a_page WHERE slug = :slug', array('slug' => '/'));
    $root = $roots[0];
    // Now turn all the orphans into kids of the homepage.
    // The AND is unnecessary but I'm feeling gunshy
    $orphans = $this->query('SELECT * FROM a_page WHERE lft = -1');
    foreach ($orphans as $orphan)
    {
      if (substr($orphan['slug'], 0, 1) !== '/')
      {
        var_dump($orphan);
        exit(0);
      }
      $orphan['lft'] = $root['rgt'];
      $orphan['rgt'] = $root['rgt'] + 1;
      $orphan['level'] = 1;
      $orphan['archived'] = true;
      $this->query('UPDATE a_page SET lft = :lft, rgt = :rgt, level = :level WHERE id = :id', $orphan);
      $root['rgt'] += 2;
    }
    $this->query('UPDATE a_page SET lft = :lft, rgt = :rgt, level = :level WHERE id = :id', $root);
  }

  /**
   * DOCUMENT ME
   */
  protected function repairByList()
  {
    // TODO review this, make sure the new PDO version works
    echo("Fetching pages\n");
    // Leave virtual pages and anything else without a tree strictly alone
    $this->pages = $this->query('SELECT id, lft, rgt, level, slug FROM a_page WHERE lft IS NOT NULL ORDER BY lft');
    
    $this->repairSubtree(0, 1);
  }

  /**
   * DOCUMENT ME
   * @param mixed $i
   * @param mixed $lft
   * @return mixed
   */
  protected function repairSubtree($i, $lft)
  {
    echo("Looking at $i of " . count($this->pages) . "\n");
    $this->pages[$i]['lft'] = $lft;
    $rgt = $lft;
    $j = $i + 1;
    while ($j < count($this->pages))
    {
      if ($this->pages[$j]['level'] <= $this->pages[$i]['level'])
      {
        echo("Level of $j is lower or equal to level of $i\n");
        break;
      }
      $rgt++;
      list($j, $rgt) = $this->repairSubtree($j, $rgt);
    }
    $rgt++;
    $this->pages[$i]['rgt'] = $rgt;
    echo("For $i, " . $lft . "," . $rgt . "\n");
    $this->query('UPDATE a_page SET lft = :lft, rgt = :rgt, level = :level WHERE id = :id', $this->pages[$i]);
    return array($j, $rgt);
  }

  /**
   * DOCUMENT ME
   * @param mixed $pages
   * @param mixed $parent
   * @return mixed
   */
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
      $tree[$page['slug']] = $this->buildSubtree($pages, $page);
    }
    return $tree;
  }

  /**
   * DOCUMENT ME
   * @param mixed $pagesBySlug
   * @param mixed $tree
   * @param mixed $rgt
   */
  protected function rebuildAdjacencyList($pagesBySlug, $tree, &$rgt)
  {
    foreach ($tree as $slug => $subtree)
    {
      $rgt++;
      $lft = $rgt;
      $this->rebuildAdjacencyList($pagesBySlug, $subtree, $rgt);
      $page = &$pagesBySlug[$slug];
      $page['lft'] = $lft;
      $rgt++;
      $page['rgt'] = $rgt;
      $page['level'] = $this->getSlugLevel($slug);
      $this->query('UPDATE a_page SET lft = :lft, rgt = :rgt, level = :level WHERE id = :id', $page);
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $slug
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function getPDO()
  {
    $connection = Doctrine_Manager::connection();
    $pdo = $connection->getDbh();
    return $pdo;
  }

  /**
   * DOCUMENT ME
   * @param mixed $s
   * @param mixed $params
   * @return mixed
   */
  protected function query($s, $params = array())
  {
    $pdo = $this->getPDO();
    $nparams = array();
    // I like to use this with toArray() while not always setting everything,
    // so I tolerate extra stuff. Also I don't like having to put a : in front 
    // of everything
    foreach ($params as $key => $value)
    {
      if (strpos($s, ":$key") !== false)
      {
        $nparams[":$key"] = $value;
      }
    }
    $statement = $pdo->prepare($s);
    try
    {
      $statement->execute($nparams);
    }
    catch (Exception $e)
    {
      echo($e);
      echo("Statement: $s\n");
      echo("Parameters:\n");
      var_dump($params);
      exit(1);
    }
    $result = true;
    try
    {
      $result = $statement->fetchAll();
    } catch (Exception $e)
    {
      // Oh no, we tried to fetchAll on a DELETE statement, everybody panic!
      // Seriously PDO, you need to relax
    }
    return $result;
  }
}
