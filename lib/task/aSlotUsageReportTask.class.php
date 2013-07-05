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
    $this->addArguments(array(
      new sfCommandArgument('type', sfCommandArgument::OPTIONAL, 'Specific slot type; results in report that lists each page featuring the slot type'),
    ));

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

    if ($arguments['type'])
    {
      $data = $sql->query('SELECT p.slug AS slug, COUNT(s.id) AS slot_count FROM a_page p INNER JOIN a_area a ON a.page_id = p.id INNER JOIN a_area_version av ON a.id = av.area_id AND a.latest_version = av.version INNER JOIN a_area_version_slot avs ON avs.area_version_id = av.id INNER JOIN a_slot s ON avs.slot_id = s.id WHERE s.type = :type GROUP BY p.id ORDER BY p.slug ASC;', array('type' => $arguments['type']));
      foreach ($data as $row)
      {
        echo($row['slug'] . ',' . $row['slot_count'] . "\n");
      }
      echo("\n(The number of pages will not match the number of instances\n");
      echo("because a page may contain more than one.)\n");
      return;
    }

    $data = $sql->query('SELECT s.type AS type, COUNT(s.type) AS type_count FROM a_area a INNER JOIN a_area_version av ON a.id = av.area_id AND a.latest_version = av.version INNER JOIN a_area_version_slot avs ON avs.area_version_id = av.id INNER JOIN a_slot s ON avs.slot_id = s.id GROUP BY s.type ORDER BY COUNT(s.type) DESC;');
    foreach ($data as $row)
    {
      echo($row['type'] . ',' . $row['type_count'] . "\n");
    }
  }
}
