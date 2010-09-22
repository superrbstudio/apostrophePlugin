<?php

// A wrapper for simple MySQL-based schema updates. See the apostrophe:migrate task for 
// an example of usage

class aMigrate
{
  protected $conn;
  protected $commandsRun;
  
  public function __construct($conn)
  {
    $this->conn = $conn;
  }
  
  // Used to run a series of queries where you don't need parameters or results
  public function sql($commands)
  {
    foreach ($commands as $command)
    {
      echo("SQL statement:\n\n$command\n\n");
      $this->conn->query($command);
      $this->commandsRun++;
    }
  }
  
  // Runs a single query, with parameters. If :foo appears in the query it gets
  // substituted correctly (via PDO) with $params['foo']. Extra stuff in
  // $params is allowed, which is very helpful with toArray()
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
  
  public function getCommandsRun()
  {
    return $this->commandsRun;
  }
  
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
}
