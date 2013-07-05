<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aSlotUsageReportTask extends sfBaseTask
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
    $this->name             = 'slot-usage-report';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [apostrophe:slot-usage-report|INFO] task reports how many instances of each slot type actually exist in the current version of the site. Instances that are no longer active are ignored.

Call it with:

  [php symfony apostrophe:slot-usage-report|INFO]
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
    $data = $sql->query('SELECT s.type AS type, COUNT(s.type) AS type_count FROM a_area a INNER JOIN a_area_version av ON a.id = av.area_id AND a.latest_version = av.version INNER JOIN a_area_version_slot avs ON avs.area_version_id = av.id INNER JOIN a_slot s ON avs.slot_id = s.id GROUP BY s.type ORDER BY COUNT(s.type) DESC;');
    foreach ($data as $row)
    {
      echo($row['type'] . ',' . $row['type_count'] . "\n");
    }
  }
}
