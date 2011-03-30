<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class apostropheMigratefrompkcontextcmsTask extends sfBaseTask
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
      // We merged most of the plugins, but watch out for the two we didn't merge
      '/pkBlogPlugin/' => 'apostropheBlogPlugin',
      '/pkFormBuilderPlugin/' => 'apostropheFormBuilderPlugin',
      '/pk(\w+)Plugin/' => 'apostrophePlugin',
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
      '/PkAdmin/' => 'AAdmin',
      '/getPk/' => 'getA',
      '/setPk/' => 'setA',
      '/getpk/' => 'geta',
      '/setpk/' => 'seta',
      '/executePk/' => 'executeA',
      '/aBaseActions/' => 'aSlotActions',
      '/aBaseComponents/' => 'aSlotComponents',
      // aTagahead has to go back to being pkTagahead, it is part of the taggable plugin and
      // will not be renamed
      '/aTagahead/' => 'pkTagahead'
    );

    $pathRules = array(
      // First rename the plugins we didn't merge
      '/\/([^\/]*?)pkBlogPlugin([^\/]*)$/' => '/$1apostropheBlogPlugin$2',
      '/\/([^\/]*?)pkFormBuilderPlugin([^\/]*)$/' => '/$1apostropheFormBuilderPlugin$2',
      // Now merge files in project /lib that were initially generated as
      // local overrides of the various pk plugins
      '/\.\/lib\/model\/doctrine\/pk\w+Plugin\/(.+)/' => './lib/model/doctrine/apostrophePlugin/$1',
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
      // Leave plugin symlinks and their "contents" alone
      // (we will do plugin:publish-assets later)
      '/^\.\/web\/\w+Plugin/',
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
      './symfony plugin:publish-assets'
    );

    if (!file_exists('config/ProjectConfiguration.class.php'))
    {
      die("You must cd to your project's root directory before running this task.\n");
    }

    // Remove 
    
    // Rename the slot modules and certain references to them
    // We want to look at all applications here not just frontend
    $appYamls = glob('apps/*/config/app.yml');
    $yaml = new sfYaml();
    $types = array(
      'pkContextCMSText' => 'Plain Text',
      'pkContextCMSRichText' => 'Rich Text');
    foreach ($appYamls as $appYaml)
    {
      $data = $yaml->load(file_get_contents($appYaml));
      foreach ($data as $heading)
      {
        if (isset($heading['pkContextCMS']['slot_types']))
        {
          $types = array_merge($types, $heading['pkContextCMS']['slot_types']);
        }
      }
    }
    $types = array_keys($types);
    foreach ($types as $type)
    {
      echo("Slot type under consideration is $type\n");
      // Rename within the implementation PHP files. That includes action classes,
      // component classes and templates (which frequently have include_component and
      // include_partial calls that will otherwise fail)
      $this->replaceInFiles("apps/*/modules/$type/*/*.php", "/$type(?!Slot)/", $type . 'Slot');
      // Rename any overrides or implementations of slot modules at the app level
      // Careful, use the ./ so we match the paths used below and can detect
      // double renames
      $modules = glob("./apps/*/modules/$type");
      foreach ($modules as $module)
      {
        echo("Renaming $module\n");
        $this->rename($module, $module . 'Slot');
      }
      // Rename within settings.yml to enable the module. We DON'T rename in
      // app.yml, where we are still using slot type names without a
      // Slot suffix, which would be superfluous there
      $this->replaceInFiles("apps/*/config/settings.yml", "/$type(?!Slot)/", $type . 'Slot');
    }
    
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
        // If it's a directory, we don't get upset if it already exists due to
        // a child file having already been moved with creation of parent dirs
        $this->rename($file, $name, is_dir($file));
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
    echo("NOW YOU MUST RUN:\n\n");
    echo("./symfony apostrophe:migrate-data-from-pkcontextcms --env=APPROPRIATE_ENVIRONMENT\n\n");
    echo("On your dev box that would most likely be dev.\n");
    echo("YOU SHOULD TEST THOROUGHLY before you deploy or commit as many changes have been made.\n");
  }

  /**
   * DOCUMENT ME
   * @param mixed $type
   * @return mixed
   */
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

  /**
   * DOCUMENT ME
   * @param mixed $k1
   * @param mixed $k2
   * @return mixed
   */
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
  
  protected $renamed;

  /**
   * DOCUMENT ME
   * @param mixed $from
   * @param mixed $to
   * @param mixed $canExist
   * @return mixed
   */
  public function rename($from, $to, $canExist = false)
  {
    if (is_dir($from) && isset($this->renamed[$from]))
    {
      // Without this we're allowed to svn mv the same directory twice (under the same
      // 'from' name, that is), breaking whatever
      // is in progress with the first move. The old directory name is still hanging around
      // only because svn never deletes directories except during svn update 
      echo("Already renamed $from\n");
      return;
    }
    $this->renamed[$from] = true;
    echo("Renaming $from to $to canExist is: $canExist ");
    if ($canExist && file_exists($to))
    {
      // Already created by the move of a child file, that's OK
      echo("Already exists\n");
      return;
    }
    if ((is_dir($from) && file_exists($from . '/.svn')) || ((!is_dir($from)) && file_exists(dirname($from) . '/.svn')))
    {
      $cmd = 'svn mv --parents ' . escapeshellarg($from) . ' ' . escapeshellarg($to);
      echo("$cmd\n");
      system($cmd, $result);
      if ($result != 0)
      {
        die("Unable to rename $from to $to via svn mv, even though you have a .svn file in that folder (or its parent folder, if this is not a directory). Is this an unhappy svn checkout?\n\nNOTE: you must have at least svn 1.5. If you get errors about\nthe --parents option, upgrade svn.\n");
      }
    }
    else
    {
      echo("Direct rename\n");
      $this->ensureDir($to);
      if (!rename($from, $to))
      {
        die("Unable to rename $from to $to\n");
      }
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $file
   * @return mixed
   */
  public function ensureDir($file)
  {
    while (true)
    {
      $file = dirname(__FILE__);
      if (file_exists($file))
      {
        return;
      }
      system("mkdir -p " . escapeshellarg($file));
    }
  }

  /**
   * search is a regexp
   * @param mixed $glob
   * @param mixed $search
   * @param mixed $replace
   */
  public function replaceInFiles($glob, $search, $replace)
  {
    $files = glob($glob);
    foreach ($files as $file)
    {
      $content = file_get_contents($file);
      echo("Searching for: $search replacing with: $replace\n");
      file_put_contents($file, preg_replace($search, $replace, $content));
    }
  }
}
