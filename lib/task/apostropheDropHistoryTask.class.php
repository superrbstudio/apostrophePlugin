<?php

/**
 * Task to purge old versions of content from the history. Thanks to Dennis Verspuij
 */

class apostropheDropHistoryTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'drop-history';
    $this->briefDescription = 'Drop all editing history';
    $this->detailedDescription = <<<EOF
The [apostrophe:drop-history|INFO] task deletes all prior versions
of content from the database, keeping only the latest version.
In production it is usually a bad idea to delete history as you
may wish to roll back later.

Note to users of apostropheWorkflowPlugin: this task does NOT give 
any protection to unpublished draft content. Only approved content 
is considered "current."

Call it with:

  [php symfony apostrophe:drop-history --env=as-appropriate|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $sql = new aMysql();
    try
    {
      $sql->beginTransaction();
      $sql->query(
        'DELETE A_V '.
        'FROM a_area AS A '.
        'INNER JOIN a_area_version AS A_V ON A_V.area_id = A.id AND A_V.version <> A.latest_version'
      );
      $iDelAreaVersions = $sql->getRowsAffected();
      $sql->query(
        'UPDATE a_area AS A '.
        'INNER JOIN a_area_version AS A_V '.
        'SET A.latest_version = 1, A_V.version = 1'
      );
      $iAreas = $sql->getRowsAffected();
      $sql->query(
        'DELETE FROM a_slot '.
        'WHERE id NOT IN (SELECT slot_id FROM a_area_version_slot)'
      );
      $iSlots = $sql->getRowsAffected();

      $this->log(sprintf('Deleted %d slots and %d historical versions of %d areas.', $iSlots, $iDelAreaVersions, $iAreas));      
      $sql->commit();
    }
    catch(Exception $oE)
    {
       $sql->rollback();
       echo "Operation failed due to the following database error:\n".
         $oE->getDescription()."\n";
    }
  }
}
