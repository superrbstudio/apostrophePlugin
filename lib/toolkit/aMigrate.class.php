<?php
/**
 * A wrapper for simple MySQL-based schema updates. See the apostrophe:migrate task for
 * an example of usage
 *
 * TODO: merge this functionality into aMysql & make this an empty subclass
 *
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aMigrate
{
  protected $conn;
  protected $commandsRun;

  /**
   * DOCUMENT ME
   * @param mixed $conn
   */
  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  /**
   * Used to run a series of queries where you don't need parameters or results
   * @param mixed $commands
   */
  public function sql($commands)
  {
    foreach ($commands as $command)
    {
      echo("SQL statement:\n\n$command\n\n");
      $this->conn->query($command);
      $this->commandsRun++;
    }
  }

  /**
   * Runs a single query, with parameters. If :foo appears in the query it gets
   * substituted correctly (via PDO) with $params['foo']. Extra stuff in
   * $params is allowed, which is very helpful with toArray().
   * @param mixed $s
   * @param mixed $params
   * @return mixed
   */
  public function query($s, $params = array())
  {
    $pdo = $this->conn;
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
    echo("SQL query:\n\n$s\n\n");
    
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
    $this->commandsRun++;
    return $result;
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function lastInsertId()
  {
    return $this->conn->lastInsertId();
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getCommandsRun()
  {
    return $this->commandsRun;
  }

  /**
   * DOCUMENT ME
   * @param mixed $tableName
   * @return mixed
   */
  public function tableExists($tableName)
  {
    if (!preg_match('/^\w+$/', $tableName))
    {
      die("Bad table name in tableExists: $tableName\n");
    }
    $data = array();
    try
    {
      $data = $this->conn->query("SHOW CREATE TABLE $tableName")->fetchAll();
    } catch (Exception $e)
    {
    }
    return (isset($data[0]['Create Table']));    
  }

  /**
   * DOCUMENT ME
   * @param mixed $tableName
   * @param mixed $constraintName
   * @return mixed
   */
  public function constraintExists($tableName, $constraintName)
  {
    if (!preg_match('/^\w+$/', $tableName))
    {
      die("Bad table name in tableExists: $tableName\n");
    }
    $data = array();
    try
    {
      $data = $this->conn->query("SHOW CREATE TABLE $tableName")->fetchAll();
    } catch (Exception $e)
    {
    }
    if (!isset($data[0]['Create Table'])) 
    {
      return false;
    }    
    return (strpos($data[0]['Create Table'], 'CONSTRAINT `' . $constraintName . '`') !== false);
  }

  /**
   * DOCUMENT ME
   * @param mixed $tableName
   * @param mixed $columnName
   * @return mixed
   */
  public function columnExists($tableName, $columnName)
  {
    if (!preg_match('/^\w+$/', $tableName))
    {
      die("Bad table name in columnExists: $tableName\n");
    }
    if (!preg_match('/^\w+$/', $columnName))
    {
      die("Bad table name in columnExists: $columnName\n");
    }
    $data = array();
    try
    {
      $data = $this->conn->query("SHOW COLUMNS FROM $tableName LIKE '$columnName'")->fetchAll();
    } catch (Exception $e)
    {
    }
    return (isset($data[0]['Field']));
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  public function getTables()
  {
    return array_map(array($this, 'takeFirst'), $this->query('SHOW TABLES'));
  }

  /**
   * DOCUMENT ME
   * @param mixed $val
   * @return mixed
   */
  public function takeFirst($val)
  {
    return $val[0];
  }

  /**
   * DOCUMENT ME
   */
  public function upgradeCharsets()
  {
    $tables = $this->getTables();
    foreach ($tables as $table)
    {
      $r = $this->query('SHOW CREATE TABLE ' . $table);
      $c = $r[0]['Create Table'];
      if (strpos($c, 'DEFAULT CHARSET=utf8') === false)
      {
        $this->query("alter table `$table` convert to character set utf8 collate utf8_general_ci");
      }
    }
  }

  /**
   * Drop all integer foreign key constraints, turn both columns involved into BIGINTs,
   * and reestablish the constraints
   */
  public function upgradeIds()
  {
    $tables = $this->getTables();
    $constraints = array();
    $locals = array();
    $foreigns = array();
    foreach ($tables as $table)
    {
      $r = $this->query('SHOW CREATE TABLE ' . $table);
      $c = $r[0]['Create Table'];
      if (preg_match_all('/\sCONSTRAINT `(\w+)` FOREIGN KEY \(`(\w+)`\) REFERENCES `(\w+)` \(`(\w+)`\).*?\n/s', $c, $matches, PREG_SET_ORDER))
      {
        for ($i = 0; ($i < count($matches)); $i++)
        {
          list($constraint, $name, $local, $foreignTable, $foreign) = $matches[$i];
          $constraint = preg_replace('/,\s*$/', '', $constraint);
          
          // If it isn't an old fashioned 4 byte int, it's none of our business
          if (!preg_match("/`$local` int\(11\)/", $c))
          {
            echo("Skipping $local\n");
            continue;
          }
          else
          {
            echo("NOT skipping $local\n");
          }
          
          $constraints[$table][$name] = $constraint;
          $locals[$table][] = $local;
          $foreigns[$foreignTable][$foreign] = true;
        }
      }
    }
    
    foreach ($constraints as $table => $tableConstraints)
    {
      foreach ($tableConstraints as $name => $constraint)
      {
        // There is no DROP CONSTRAINT for some strange reason
        $this->query("ALTER TABLE $table DROP FOREIGN KEY `$name`");
      }
    }
    
    foreach ($locals as $table => $locals)
    {
      foreach ($locals as $foreignId)
      {
        $this->query("ALTER TABLE $table CHANGE $foreignId $foreignId BIGINT");
      }
    }
    foreach ($foreigns as $table => $names)
    {
      foreach ($names as $id => $dummy)
      {
        // By default MySQL will toss out AUTO_INCREMENT if you change the type
        $this->query("ALTER TABLE $table CHANGE $id $id BIGINT AUTO_INCREMENT");
      }
    }
    foreach ($constraints as $table => $tableConstraints)
    {
      foreach ($tableConstraints as $name => $constraint)
      {
        $this->query("ALTER TABLE $table ADD $constraint");
      }
    }
  }
}
