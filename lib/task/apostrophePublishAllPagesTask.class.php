<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class apostrophePublishAllPagesTask extends sfBaseTask
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
    $this->name             = 'publish-all-pages';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [apostrophe:publish-all-pages|INFO] task sets the publication date of all regular Apostrophe
pages that are not explicitly unpublished (archived). It is primarily used to resolve issues with 
sites launched during the early development of Apostrophe 1.5 which sometimes have a null 
published_at setting for pages that are meant to be visible. Sites migrating from 1.4 should not
need this task because apostrophe:migrate takes care of it when adding the published_at column.
Sites launched more recently on 1.5 also should not need it because published_at is always updated
when creating a page or editing its page settings (if "publish" is selected).

Call it with:

  [php symfony apostrophe:publish-all-pages --env=as-appropriate|INFO]
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
    
    $sql = new aMysql();
    $sql->query('UPDATE a_page SET published_at = NOW() where (archived IS FALSE OR archived IS NULL) AND substr(slug, 1, 1) = "/"');
    echo("published_at has been updated.\n");
  }
}
