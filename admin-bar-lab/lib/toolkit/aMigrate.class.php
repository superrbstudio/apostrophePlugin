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
  
  public function sql($commands)
  {
    foreach ($commands as $command)
    {
      echo("SQL statement:\n\n$command\n\n");
      $this->conn->query($command);
      $this->commandsRun++;
    }
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

}
