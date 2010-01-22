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

    echo("
pkContextCMS to Apostrophe Migration Task
    
This task will rename all references to the old pkContextCMS tables, classes, 
CSS classes and IDs, etc. throughout your project. The lib/vendor and plugins 
folders will not be touched. Tables in your database will be renamed and slot 
type names in the database will be changed. While the SQL for this has been
kept as simple as possible it has only been tested in MySQL.

This involves regular expressions that make some moderately big changes, 
including changing references to words beginning in 'pk' to begin with 'a'. If 
you are using the 'pk' prefix for things unrelated to our code you may have 
some cleanup to do after running this task.

If your project's root folder is an svn checkout, this task will automatically 
use 'svn mv' rather than PHP's 'rename' when renaming files and folders.

BACK UP YOUR PROJECT BEFORE YOU RUN THIS SCRIPT, INCLUDING YOUR DATABASE.

");
    if (!$this->askConfirmation(
"Are you sure you are ready to migrate your project? [y/N]",
      'QUESTION_LARGE',
      false))
    {
      die("Operation CANCELLED. No changes made.\n");
    }

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
      '/^\.\/lib\/vendor\//',
      '/^\.\/plugins\//',
      // Leave plugin symlink contents alone
      '/^\.\/web\/\w+Plugin\//',
      '/^\.\/web\/uploads\//',
      // We need to leave the contents of pk_writable/a_writable alone, but
      // the folder itself does need renaming
      '/^\.\/web\/pk_writable\/.+/',
      '/^\.\/web\/a_writable\/.+/',
    );

    // Don't modify inappropriate files
    $extensions = array(
      'php', 'ini', 'js', 'yml', 'txt', 'html', 'css'
    );

    $after = array(
      './symfony cc',
      './symfony doctrine:build --all-classes',
      './symfony cc',
      './symfony plugin:publish:assets',
      './symfony apostrophe:migrate-data-from-pkcontextcms'
    );

    if (!file_exists('config/ProjectConfiguration.class.php'))
    {
      die("You must cd to your project's root directory before running this task.\n");
    }

    // Rename the slot modules and certain references to them

    $slots = array('')

    $slotTypes = array_keys(sfConfig::get('app_pkContextCMS_slot_types', array('pkContextCMSText' => 'dummy', 'pkContextCMSRichText' => dummy)));
    
    foreach ($slotTypes as $type)
    {
      $modules = glob("apps/*/modules/$type");
      foreach ($modules as $module)
      {
        $this->rename($module, $module . 'Slot');
      }
      replaceInFiles("apps/*/actions/*.php", "/$type(?!Slot)/", $type . 'Slot');
      replaceInFiles("apps/*/config/settings.yml", "/$type(?!Slot)/", $type . 'Slot');
    }
    
    exit(0);
    
    // Now we can use isset() to check whether something is on the list in an efficient manner
    $extensions = array_flip($extensions);

    $files = $this->getFiles('');
    // Filter out files we shouldn't touch
    $nfiles = array();
    foreach ($files as $file)
    {
      $ignore = false;
      foreach ($ignored as $rule)
      {
        if (preg_match($rule, $file))
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
      $nfiles[] = $file;
    }
    $files = $nfiles;

    $total = count($files);
    foreach ($files as $file)
    {
      $sofar++;
      // Leave inappropriate file extensions alone, in particular leave binary files etc. alone.
      // But do rename directories
      $ext = pathinfo($file, PATHINFO_EXTENSION);
      if ((!is_dir($file)) && (!isset($extensions[$ext])))
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
        $content = preg_replace(array_keys($contentRules), array_values($contentRules), $content);
        file_put_contents($file, $content);
      }
      $name = $file;
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

    
    echo("Done!\n\n");
    echo("YOU SHOULD TEST THOROUGHLY before you deploy or commit as many changes have been made.\n");
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
    if (file_exists(dirname($from) . '/.svn'))
    {
      system('svn mv ' . escapeshellarg($from) . ' ' . escapeshellarg($to), $result);
      if ($result != 0)
      {
        die("Unable to rename $from to $to via svn mv, even though you have a .svn file in the parent directory of $from. Is this an unhappy svn checkout?\n");
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
