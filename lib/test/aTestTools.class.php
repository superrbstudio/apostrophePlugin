<?php
/**
 * 
 * A collection of static methods useful for making tests easier to write.
 * @package    apostrophePlugin
 * @subpackage    test
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aTestTools
{

  /**
   * DOCUMENT ME
   * @param mixed $length
   */
  public static function randomString($length)
  {
    $string = '';
    for ($i=0;$i<$length;$i++)
    {
      $string = chr(rand(97,122));
    }
  }
  
  static protected $test;
  static protected $configuration;

  /**
   * DOCUMENT ME
   * @param mixed $test
   * @param mixed $configuration
   * @return mixed
   */
  static public function loadData($test = null, $configuration = null)
  {
    self::$test = $test;
    self::$configuration = $configuration;
    
    // Load ALL the fixtures, including plugin fixtures, not just the app level fixtures!
  
    // ALSO: we only load the fixtures on the first functional test in a set. The rest of the time we
    // cheat and use a mysqldump file. There are various reasons why we cannot assume it is safe to 
    // do this in the general case, including: modified fixtures, modified schemas, modified behaviors,
    // and modified Doctrine. But for consecutive runs in the same PHP invocation, we definitely
    // don't need to start from scratch!
    
    $root = sfConfig::get('sf_root_dir');
    $writable = aFiles::getWritableDataFolder();
    $cache = "$writable/test-fixtures-cache.sql";

    $reload = false;
  
    // TODO: I should check to see if a PID still exists somewhere to figure out if this is stale
    if (file_exists("$writable/test_set_pid"))
    {
      $processes = self::getProcesses();
      $pid = trim(file_get_contents("$writable/test_set_pid"));
      if (!isset($processes[$pid]))
      {
        // Stale
        unlink("$writable/test_set_pid");
        if (file_exists("$writable/test_set_first"))
        {
          unlink("$writable/test_set_first");
        }
        self::info("$writable/test_set_pid is stale, reloading fixtures");
        $reload = true;
      }
      else
      {
        if (file_exists("$writable/test_set_first"))
        {
          $reload = true;
          self::info("first test in set, reloading fixtures");
          unlink("$writable/test_set_first");
        }
        else
        {
          self::info("later test in set, will not reload fixtures");
        }
      }
    }
    else
    {
      self::info("Not part of a test set, will reload fixtures");
      $reload = true;
    }

    if (!$reload)
    {
      $reload = (!file_exists($cache)) || (filesize($cache) == 0);
    }
  
    $params = self::getDatabaseParams();

    // Attempt at autodetect that a reload is needed. This doesn't work well enough
    // because of modified schemas, modified behaviors, and modified Doctrine
  
    // if (!$reload)
    // {
    //   $fixtures = glob("$root/data/fixtures/*.yml");
    //   $plugins = sfContext::getInstance()->getConfiguration()->getPlugins();
    //   foreach ($plugins as $plugin)
    //   {
    //     $pluginFixtures = glob("$root/plugins/$plugin/data/fixtures/*.yml");
    //     $fixtures = array_merge($fixtures, $pluginFixtures);
    //   }
    //   foreach ($fixtures as $fixture)
    //   {
    //     if (filemtime($cache) < filemtime($fixture))
    //     {
    //       $reload = true;
    //       break;
    //     }
    //   }
    // }

    if ($reload)
    {
      self::info("Reloading fixtures and rebuilding model, filters and forms");
      self::info("(We would rather just reload fixtures but data-load does not clean up tables if there are are no entries for those tables in the fixtures, and that leaves traces of things created in those tables by previous tests. A doctrine:data-reload task is needed.)");
      system(escapeshellarg(sfConfig::get('sf_root_dir') . '/symfony') . ' doctrine:build --all --db --and-load --env=test --no-confirmation', $result);
      self::info("Reloaded fixtures");
      if ($result !== 0)
      {
        throw new sfException("Error loading data");
      }
      // Cache for next time
      $sh = 'mysqldump ' . self::shellDBParams($params) . ' > ' . escapeshellarg($cache);
      system($sh, $result);
      self::info("Cached fixtures with $sh");
      if ($result != 0)
      {
        throw new sfException("Error dumping fixtures to cache");
      }
    }
    else
    {
      self::info("Reloading fixtures data from $cache");
      system('mysql ' . self::shellDBParams($params) . ' < ' . escapeshellarg($cache), $result);
      if ($result != 0)
      {
        throw new sfException("Error reloading fixtures from cache");
      }
    }
    return;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  static public function getDatabaseParams()
  {
    echo("appConfig\n");
    $appConfig = self::$configuration ? self::$configuration : sfContext::getInstance()->getConfiguration();
    echo("appManager\n");
    $dbManager = new sfDatabaseManager($appConfig);
    echo("after appManager\n");
    $names = $dbManager->getNames();
    $db = $dbManager->getDatabase($names[0]);
    $username = $db->getParameter('username');  //root
    $dsn = $db->getParameter('dsn');  //mysql:dbname=mydbtest;host=localhost because it's test config
    $password = $db->getParameter('password');  //password
    if (!preg_match('/^mysql:(.*)\s*$/', $dsn, $matches))
    {
      throw new sfException("I don't understand the DSN $dsn, sorry");
    }
    $pairs = explode(';', $matches[1]);
    $data = array();
    foreach ($pairs as $pair)
    {
      list($key, $val) = explode('=', $pair);
      $data[$key] = $val;
    }
    $data['username'] = $username;
    $data['password'] = $password;
    return $data;
  }

  /**
   * DOCUMENT ME
   * @param mixed $params
   * @return mixed
   */
  static public function shellDBParams($params)
  {
    return '-u ' . escapeshellarg($params['username']) . ' -p' . escapeshellarg($params['password']) . ' -h ' . escapeshellarg($params['host']) . ' ' . escapeshellarg($params['dbname']);
  }

  /**
   * Yes, this is a hack
   * @param mixed $m
   */
  static public function info($m)
  {
    if (isset(self::$test))
    {
      self::$test->info($m);
    }
    else
    {
      // Unit test, we have no context to call anything else
      echo("$m\n");
    }
  }

  /**
   * Bound to be useful somewhere else
   * @return mixed
   */
  static public function getProcesses()
  {
    $processes = array();
    $in = popen("ps -eo pid,command", "r");
    $data = stream_get_contents($in);
    pclose($in);
    $data = preg_split("/\n/", $data);
    foreach ($data as $line)
    {
      if (preg_match("/^\s*(\d+)\s+(.*?)\s*$/", $line, $matches))
      {
        // Works across Linux and MacOS X mileage elsewhere may vary
        $processes[$matches[1]] = $matches[2];        
      }
    }
    return $processes;
  }
}