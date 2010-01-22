<?php

class apostropheMigratefrompkcontextcmsTask extends sfBaseTask
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
    $this->name             = 'migrate-from-pkcontextcms';
    $this->briefDescription = 'Migrate old pkContextCMS project to Apostrophe';
    $this->detailedDescription = <<<EOF
The [apostrophe:migrate-from-pkcontextcms|INFO] task migrates pkContextCMS projects to Apostrophe. Call it with:

  [php symfony apostrophe:migrate-from-pkcontextcms|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    // add your code here
    
    // TODO: prompt the user first to confirm that they want to do this drastic thing

    // pkContextCMS-to-Apostrophe project upgrade script

    // BACK UP YOUR SITE FIRST! 

    // This script is svn aware - it will use svn commands to rename files if
    // it detects that your site is checked out from svn. If not, it will use
    // the regular PHP rename() call.

    // If you are in the habit of using 'pk' as a prefix for variables not related to our
    // plugins, you can expect to have some difficulties with this script. You'll want to
    // revisit those areas of your code after running this script and before committing
    // your project back to svn.

    // After renaming files, this script does the following:
    //
    // ./symfony cc
    // ./symfony doctrine:build --all-classes
    // ./symfony cc
    // ./symfony apostrophe:rebuild-search-indexes
    // ./symfony apostrophe:pkcontextcms-migrate-slots

    $contentRules = array(
      '/pkContextCMSPlugin/' => 'apostrophePlugin',
      '/pk(\w+)Plugin/' => 'apostrophe$1Plugin',
      // Case varies
      '/pkContextCMS/i' => 'a',
      '/pk_context_cms/' => 'a',
      '/pk-context-cms/' => 'a',
      '/Basepk/' => 'Basea',
      '/Pluginpk/' => 'Plugina',
      '/getPk/' => 'getA',
      '/setPk/' => 'setA',
      '/_pk/' => '_a',
      '/\bpk/' => 'a',
      '/PkAdmin/' => 'AAdmin'
    );

    // I was generating the below from the above but it got too weird
    $pathRules = array(
      '/\/([^\/]*?)pkContextCMSPlugin([^\/]*)$/' => '/$1apostrophePlugin$2',
      '/\/([^\/]*?)pk(\w+)Plugin([^\/]*)$/' => '/$1apostrophe$2Plugin$3',
      // OMG succinct!
      '/\/([^\/]*?)pkContextCMS([^\/]*)$/' => '/$1a$2',
      '/\/([^\/]*?)Basepk([^\/]*)$/' => '/$1Basea$2',
      '/\/([^\/]*?)Pluginpk([^\/]*)$/' => '/$1Plugina$2',
      '/\/([^\/]*?)pk([^\/]*)$/' => '/$1a$2',
      '/\/([^\/]*?)PkAdmin([^\/]*)$/' => '/$1AAdmin$2',
      '/\/([^\/]*?)BasePk([^\/]*)$/' => '/$1BaseA$2',
      '/\/([^\/]*?)autoPk([^\/]*)$/' => '/$1autoA$2'
    );

    // Leave the vendor folder alone, it's not ours. Also,
    // leave the plugins folder alone, as it is unlikely to contain
    // anything we should be modifying - if they are following our
    // instructions they have already installed apostrophePlugin and
    // jettisoned the old pk plugins. If they have their own plugins that
    // use the CMS, they can run this script again within those folders

    $ignored = array(
      '/^\/lib\/vendor\/',
      '/^\/plugins\/',
      '/^\/web\/uploads\/'
    );

    // Don't modify inappropriate files
    $extensions = array(
      'php', 'ini', 'js', 'yml', 'txt', 'html', 'css'
    );

    $after = array(
      './symfony cc',
      './symfony doctrine:build --all-classes',
      './symfony cc',
      './symfony publish:assets',
      './symfony apostrophe:rebuild-search-indexes'
    );

    $tables = array(
      'pk_context_cms_slot' => 'a_slot',
      'pk_context_cms_area_version_slot' => 'a_area_version_slot',
      'pk_context_cms_area_version' => 'a_area_version',
      'pk_context_cms_area' => 'a_area',
      'pk_context_cms_page' => 'a_page',
      'pk_blog_category' => 'a_blog_category',
      'pk_blog_event_version' => 'a_blog_event_version',
      'pk_blog_item' => 'a_blog_item',
      'pk_blog_item_version' => 'a_blog_item_version',
      'pk_blog_post_version' => 'a_blog_post_version',
      'pk_context_cms_access' => 'a_access',
      'pk_context_cms_lucene_update' => 'a_lucene_update',
      'pk_media_item' => 'a_media_item'
    );

    // First we have to find slot implementation modules and rename them to follow
    // the new convention: the component class name ends in 'Slot'. If we do this first
    // the simpler regexps that follow can take care of the rest

    $normalViews = get_files('-type f | grep modules.*normalView');

    foreach ($normalViews as $normalView)
    {
      if (preg_match('/(\w+)\/templates/', $normalView, $matches))
      {
        $module = $matches[1];
        $new = $module . 'Slot';
        // These rules must run first to avoid chicken and egg renaming problems
        $modulePathRules['/\/([^\/]*?)' . $module . '([^\/]*)$/'] = '/$1' . $new . '$2';
        $moduleContentRules["/$module/"] = $new;

        echo("Created rules to rename slot module $module\n");
      }
    }

    // Without this pkContextCMSBlog gets renamed before pkContextCMSBlogEvent
    // with disastrous consequences

    uksort($modulePathRules, 'apostropheMigratefrompkcontextcmsTask::longestFirst');
    uksort($moduleContentRules, 'apostropheMigratefrompkcontextcmsTask::longestFirst');

    // Now we can use isset() to check whether something is on the list in an efficient manner
    $extensions = array_flip($extensions);

    $files = $this->getFiles($type);
    $total = count($files);
    foreach ($files as $file)
    {
      $sofar++;
      $ignore = false;
      foreach ($ignored as $ignore)
      {
        if (preg_match($ignore, $file))
        {
          // Leave vendor, plugins, etc. alone
          $ignore = true;
          break;
        }
      }
      if ($ignore)
      {
        continue;
      }
      // Leave inappropriate file extensions alone, in particular leave binary files etc. alone.
      // But do rename directories
      $ext = pathinfo($file, PATHINFO_EXTENSION);
      if (strlen($ext) && (!isset($extensions[$ext])))
      {
        continue;
      }
      $file = trim($file);
      if (!strlen($file))
      {
        continue;
      }
      echo($file . ' (' . $sofar . ' of ' . $total . ")\n");
      if (!is_dir($file))
      {
        $content = file_get_contents($file);
        $content = preg_replace(array_keys($moduleContentRules), array_values($moduleContentRules), $content);
        $content = preg_replace(array_keys($contentRules), array_values($contentRules), $content);
        file_put_contents($file, $content);
      }
      $name = $file;
      $name = preg_replace(array_keys($modulePathRules), array_values($modulePathRules), $name);
      $name = preg_replace(array_keys($pathRules), array_values($pathRules), $name);
      if ($name !== $file)
      {
        echo("Renaming $file to $name\n");
        $this->rename($file, $name);
      }
    }
    
    foreach ($after as $cmd)
    {
      echo("Running command $cmd\n");
      system($cmd, $result);
      if ($result != 0)
      {
        die("Command $cmd failed with result $result\n");
      }
    }

    // We need to use PDO here because Doctrine is more than a little confused when
    // we've renamed the codebase but not the tables
    
    echo("Renaming slots in database\n");
    $conn = Doctrine_Manager::connect()->getDbh();
    $conn->query('UPDATE pk_context_cms_slot SET type = REPLACE(type, "pkContextCMS", "a")');
    
    echo("Renaming tables in database\n");
    foreach ($tables as $old => $new)
    {
      $conn->query("RENAME TABLE $old TO $new");
    }
  }
  
  public function getFiles($type)
  {
    $pipe = "find . -d $type | grep -v \\\\.svn";
    echo("$pipe\n");
    $in = popen($pipe, 'r');
    $result = stream_get_contents($in);
    $files = preg_split('/\n/', $result, null, PREG_SPLIT_NO_EMPTY);
    pclose($in);
    return $files; 
  }

  static public function longestFirst($k1, $k2)
  {
    $l1 = strlen($k1);
    $l2 = strlen($k2);
    if ($l1 > $l2)
    {
      return -1;
    }
    elseif ($l1 == $l2)
    {
      return 0;
    }
    else
    {
      return 1;
    }
  }
  
  static public function rename($from, $to)
  {
    if (file_exists('.svn'))
    {
      system('svn mv ' . escapeshellarg($from) . ' ' . escapeshellarg($to), $result);
      if ($result != 0)
      {
        die("Unable to rename $from to $to via svn mv, even though you have a .svn file in your project's root dir. Is this an unhappy svn checkout?\n");
      }
    }
    else
    {
      if (!rename($from, $to))
      {
        die("Unable to rename $from to $to\n");
      }
    }
  }
}
