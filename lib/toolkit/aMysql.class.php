<?php

// A simple, safe, awesome wrapper for MySQL, used where Doctrine isn't
// fast enough or doesn't allow you to write the SQL you really need (an issue
// even with Doctrine_RawSql). Offers useful tools to check for existing columns 
// and tables as well as a simple and clean way to make queries, insert rows,
// delete rows, etc. without trying to mash objects into MySQL.

// Borrowed from Tom's Plog project, which in turn borrowed the beginnings of
// it from Apostrophe's aMigrate class.

// -Tom

class aMysql
{
  protected $conn;
  protected $commandsRun;
  
  public function __construct()
  {
    // Raw PDO for performance
    $connection = Doctrine_Manager::connection();
    $this->conn = $connection->getDbh();
  }

  // Used to run a series of queries where you don't need parameters or results
  // but would like to keep a count of those executed (usually migration stuff)
  public function sql($commands)
  {
    foreach ($commands as $command)
    {
      $this->conn->query($command);
      $this->commandsRun++;
    }
  }
  
  // Runs a single query, with parameters. If :foo appears in the query it gets
  // substituted correctly (via PDO) with $params['foo']. Extra stuff in
  // $params is allowed. The return value, as is standard with PDO, is an associative array 
  // by column name as well as being a numerically indexed array in column order.
  
  // Note that not requiring a : in front of everything in the params array allows us to use a
  // previous result as an argument.
  
  // If $params['foo'] is an array, then :foo is replaced by a correctly parenthesized and quoted
  // array for use in a WHERE foo IN (a, b, c) clause. 
  
  public function query($s, $params = array())
  {
    $pdo = $this->conn;
    $nparams = array();
    foreach ($params as $key => $value)
    {
      // Tolerate numeric keys, which allows us to use the results of a 
      // previous PDO query
      if (is_numeric($key))
      {
        continue;
      }
      $regexp = '/:' . preg_quote($key, '/') . '\b/';
      if (preg_match($regexp, $s) > 0)
      {
        // Arrays are turned into IN clauses (comma separated lists enclosed in parens)
        if (is_array($value))
        {
          $s = preg_replace($regexp, '(' . implode(',', array_map(array($this, 'quote'), $value)) . ')', $s); 
        }
        else
        {
          $nparams[":$key"] = $value;
        }
      }
    }
    
    $statement = $pdo->prepare($s);

    // PDO has brain damage and can't figure out when to bind things as literals with
    // PDO::PARAM_INT. This breaks offset and limit queries if you just bind naively with
    // an array argument to execute(). Don't get greedy, only do this to definite integers

    foreach ($nparams as $key => $value)
    {
      if (is_int($value) || preg_match('/^-?\d+$/', $value))
      {
        $statement->bindValue($key, $value, PDO::PARAM_INT);
      }
      else
      {
        $statement->bindValue($key, $value, PDO::PARAM_STR);
      }
    }
    
    try
    {
      $statement->execute();
    }
    catch (Exception $e)
    {
      throw new Exception("PDO exception on query: " . $s . " arguments: " . json_encode($params) . " bound arguments: " . json_encode($nparams) . "\n\n" . $e);
    }
    $result = true;
    try
    {
      $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e)
    {
      // Oh no, we tried to fetchAll on a DELETE statement, everybody panic!
      // Seriously PDO, you need to relax
    }
    return $result;
  }

  // Why are you using this? Go read about the params argument to query() again
  public function quote($item)
  {
    return $this->conn->quote($item);
  }

  // Returns just the first row of results. Add your own LIMIT clause to help MySQL
  // deliver that efficiently. But also see find()
  public function queryOne($query, $params = array())
  {
    $results = $this->query($query, $params);
    if (count($results))
    {
      return $results[0];
    }
    return null;
  }

  // Handy for getting just the ids, just the names, etc. Returns an array 
  // containing only the first column of each row 
  public function queryScalar($query, $params = array())
  {
    $results = $this->query($query, $params);
    $nresults = array();
    foreach ($results as $result)
    {
      $nresults[] = reset($result);
    }
    return $nresults;
  }

  // Returns *one* scalar, useful when fetching just one thing.
  // Note: returns null if there are no results, or no columns in the results (is that possible?)
  public function queryOneScalar($query, $params = array())
  {
    $results = $this->query($query, $params);
    if (!count($results))
    {
      return null;
    }
    $result = $results[0];
    if (!count($result))
    {
      return null;
    }
    return reset($result);
  }

  // After an insert you'll need to know what the id of the new thing is.
  // But also see insert()
  public function lastInsertId()
  {
    return $this->conn->lastInsertId();
  }
  
  // Trivial, but handy in array_map calls
  public function colonPrefix($s)
  {
    return ':' . $s;
  }
  
  // Useful for simple inserts. The id of the last added row is returned
  // (just ignore the return value if the table does not have an autoincrementing id column).
  public function insert($table, $params = array())
  {
    $columns = array_keys($params);
    $this->query('INSERT INTO ' . $table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', array_map(array($this, 'colonPrefix'), $columns)) . ')', $params);
    return $this->lastInsertId();
  }
  
  // Useful for simple inserts where you'd like the resulting row returned to you.
  // Not for use with tables that don't have an autoincrementing integer id
  // named 'id', so just use query or plain insert() as you see fit. Makes an extra query to get what
  // was really inserted since otherwise you won't get back values for the defaulted fields. 
  // This is just a timesaver, use it where apropos
  public function insertAndSelect($table, $params = array())
  {
    $columns = array_keys($params);
    $this->query('INSERT INTO ' . $table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', array_map(array($this, 'colonPrefix'), $columns)) . ')', $params);
    $id = $this->lastInsertId();
    return $this->query('select * from ' . $table . ' where id = ?', array('id' => $id));
  }
  
  // Handy for simple deletes where there is an 'id' column
  public function delete($table, $id)
  {
    $this->query('DELETE FROM ' . $table . ' WHERE id = :id', array('id' => $id));
  }

  // Good for fetching a row when there is an 'id' column. 
  public function find($table, $id)
  {
    return $this->queryOne('SELECT * from ' . $table . ' WHERE id = :id', array('id' => $id));
  }
  
  public function exists($table, $id)
  {
    return !!$this->find($table, $id);
  }
  
  // Writing SET clauses can be a pain. This method saves you the trouble for records
  // with id columns
  public function update($table, $id, $params = array())
  {
    $q = 'UPDATE ' . $table . ' ';
    $first = true;
    $params['id'] = $id;
    foreach ($params as $k => $v)
    {
      if ($first)
      {
        $q .= 'SET ';
        $first = false;
      }
      else
      {
        $q .= ', ';
      }
      $q .= $k . ' = :' . $k . ' ';
    }
    $q .= 'WHERE id = :id';
    return $this->query($q, $params);
  }

  // Useful when you need the current MySQL date. $relative can be -30 minutes, +30 days, etc.
  public function now($relative = '+0 seconds')
  {
    return date('Y-m-d H:i:s', strtotime($relative, time()));
  }
  
  // Return the count of sql() calls 
  public function getCommandsRun()
  {
    return $this->commandsRun;
  }
  
  // Does this table already exist?
  public function tableExists($tableName)
  {
    if (!preg_match('/^\w+$/', $tableName))
    {
      throw new Exception("Bad table name in tableExists: $tableName\n");
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
  
  // Does this column already exist?
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
  
  // Return a value that will be unique for the column (assuming no race condition of course;
  // you should still use UNIQUE INDEX) by modifying $value until it doesn't already exist.
  // Trusts table and column (you would never let users enter metadata like that, right?)
  
  public function uniqueify($table, $column, $value, $exceptId = null)
  {
    $cvalue = $value;
    $n = 1;
    while (!$this->unique($table, $column, $cvalue, $exceptId))
    {
      $n++;
      // Compatible with slugify
      $cvalue = $value . '-' . $n;
    }
    return $cvalue;
  }

  // Just check for uniqueness. When you are updating an existing row it is
  // convenient to pass the id of the existing row so keeping the value the same
  // is not considered a conflict
  public function unique($table, $column, $value, $exceptId = null)
  {
    $q = 'select * from ' . $table . ' where ' . $column . ' = :value ';
    if (!is_null($exceptId))
    {
      $q .= 'AND id <> :except_id';
    }
    if (count($this->query($q, array('value' => $value, 'except_id' => $exceptId))))
    {
      return false;
    }
    return true;
  }
  
  // Grab just the ids from an array of results
  public function getIds($results)
  {
    $ids = array();
    foreach ($results as $result)
    {
      $ids[] = $result['id'];
    }
    return $ids;
  }
}
