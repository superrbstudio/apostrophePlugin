<?php
/**
 * A simple, safe, awesome wrapper for MySQL, used where Doctrine isn't
 * fast enough or doesn't allow you to write the SQL you really need (an issue
 * even with Doctrine_RawSql). Offers useful tools to check for existing columns
 * and tables as well as a simple and clean way to make queries, insert rows,
 * delete rows, etc. without trying to mash objects into MySQL.
 * Borrowed from Tom's Plog project, which in turn borrowed the beginnings of
 * it from Apostrophe's aMigrate class.
 *
 * This is now the parent class of the aSql class, which layers Apostrophe-specific
 * functionality (like importing pages and slots in a hurry without Doctrine overhead)
 * on top of the core features here.
 *
 * -Tom
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aMysql
{
  protected $conn;
  protected $commandsRun;
  protected $rowsAffected;
  
  /**
   * Constructs a new aMysql object, connected to the specified PDO handle if
   * any, otherwise to the default PDO connection of Doctrine (although we're not
   * using Doctrine at all here)
   */
  public function __construct($dbh = null)
  {
    if (!$dbh)
    {
      $connection = Doctrine_Manager::connection();
      $this->conn = $connection->getDbh();
    }
    else
    {
      $this->conn = $dbh;
    }
  }

  /**
   * DOCUMENT ME
   * @return mixed
   */
  protected function getPDO()
  {
    return $this->pdo;
  }

  /**
   * Transaction support (just pass through to PDO)
   */
  public function beginTransaction()
  {
    $this->conn->beginTransaction();
  }

  public function commit()
  {
    $this->conn->commit();
  }

  public function rollback()
  {
    $this->conn->rollback();
  }
    
  /**
   * Used to run a series of queries where you don't need parameters or results
   * but would like to keep a count of those executed (usually migration stuff)
   * @param mixed $commands
   */
  public function sql($commands)
  {
    foreach ($commands as $command)
    {
      $this->conn->query($command);
      $this->commandsRun++;
    }
  }

  /**
   * Runs a single query, with parameters. If :foo appears in the query it gets
   * substituted correctly (via PDO) with $params['foo']. Extra stuff in
   * $params is allowed. The return value, as is standard with PDO, is an associative array
   * by column name as well as being a numerically indexed array in column order.
   * Note that not requiring a : in front of everything in the params array allows us to use a
   * previous result as an argument.
   * If $params['foo'] is an array, then :foo is replaced by a correctly parenthesized and quoted
   * array for use in a WHERE foo IN (a, b, c) clause. Do not supply the parentheses, they will
   * be supplied for you.
   * @param mixed $s
   * @param mixed $params
   * @return mixed
   */
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
      $this->rowsAffected = $statement->rowCount();
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

  /**
   * Why are you using this? Go read about the params argument to query() again
   * @param mixed $item
   * @return mixed
   */
  public function quote($item)
  {
    return $this->conn->quote($item);
  }

  /**
   * Returns just the first row of results. Add your own LIMIT clause to help MySQL
   * deliver that efficiently. But also see find()
   * @param mixed $query
   * @param mixed $params
   * @return mixed
   */
  public function queryOne($query, $params = array())
  {
    $results = $this->query($query, $params);
    if (count($results))
    {
      return $results[0];
    }
    return null;
  }

  /**
   * Handy for getting just the ids, just the names, etc. Returns an array
   * containing only the first column of each row
   * @param mixed $query
   * @param mixed $params
   * @return mixed
   */
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

  /**
   * Returns *one* scalar, useful when fetching just one thing.
   * Note: returns null if there are no results, or no columns in the results (is that possible?)
   * @param mixed $query
   * @param mixed $params
   * @return mixed
   */
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

  /**
   * After an insert you'll need to know what the id of the new thing is.
   * But also see insert()
   * @return mixed
   */
  public function lastInsertId()
  {
    return $this->conn->lastInsertId();
  }

  /**
   * Trivial, but handy in array_map calls
   * @param mixed $s
   * @return mixed
   */
  public function colonPrefix($s)
  {
    return ':' . $s;
  }

  /**
   * Useful for simple inserts. The id of the last added row is returned
   * (just ignore the return value if the table does not have an autoincrementing id column).
   * @param mixed $table
   * @param mixed $params
   * @return mixed
   */
  public function insert($table, $params = array())
  {
    $columns = array_keys($params);
    $this->query('INSERT INTO ' . $table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', array_map(array($this, 'colonPrefix'), $columns)) . ')', $params);
    return $this->lastInsertId();
  }

  /**
   * Insert a new row, or update instead if a duplicate key error occurs. If a new row is added,
   * returns the id of the new row. If an old row is updated, returns $params['id'] or null if
   * it is not present. Just ignore the return value if the table does not have an autoincrementing 
   * id column.
   * @param mixed $table
   * @param mixed $params
   * @return mixed
   */
  public function insertOrUpdate($table, $params = array())
  {
    $columns = array_keys($params);
    $q = 'INSERT INTO ' . $table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', array_map(array($this, 'colonPrefix'), $columns)) . ') ' . 'ON DUPLICATE KEY UPDATE ' . $this->buildSetClauses($params, false);
    $last = $this->lastInsertId();
    $this->query($q, $params);
    $newLast = $this->lastInsertId();
    if ($last !== $newLast)
    {
      // A new row, in a table with autoincrementing ids. Return the new id
      return $newLast;
    }
    // Not a new row (or not autoincrementing). Return the id that was passed in as
    // a convenience to those who are relying on the return value to do more work 
    // with this row, like adding the id of the new/updated object to a row in another
    // table
    return isset($params['id']) ? $params['id'] : null;
  }

  /**
   * Useful for simple inserts where you'd like the resulting row returned to you.
   * Not for use with tables that don't have an autoincrementing integer id
   * named 'id', so just use query or plain insert() as you see fit. Makes an extra query to get what
   * was really inserted since otherwise you won't get back values for the defaulted fields.
   * This is just a timesaver, use it where apropos
   * @param mixed $table
   * @param mixed $params
   * @return mixed
   */
  public function insertAndSelect($table, $params = array())
  {
    $columns = array_keys($params);
    $this->query('INSERT INTO ' . $table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', array_map(array($this, 'colonPrefix'), $columns)) . ')', $params);
    $id = $this->lastInsertId();
    return $this->query('select * from ' . $table . ' where id = ?', array('id' => $id));
  }

  /**
   * Handy for simple deletes where there is an 'id' column
   * @param mixed $table
   * @param mixed $id
   */
  public function delete($table, $id)
  {
    $this->query('DELETE FROM ' . $table . ' WHERE id = :id', array('id' => $id));
  }

  /**
   * Good for fetching a row when there is an 'id' column.
   * @param mixed $table
   * @param mixed $id
   * @return mixed
   */
  public function find($table, $id)
  {
    return $this->queryOne('SELECT * from ' . $table . ' WHERE id = :id', array('id' => $id));
  }

  /**
   * Good for fetching a row when there is 'type' column.
   * @param mixed $table
   * @param mixed $column_name
   * @param mixed $column_val
   * @return mixed
   */
  public function findAllBy($table, $column_name, $column_val)
  {
    return $this->query('SELECT * from ' . $table . ' WHERE :column_name = :column_val', array('column_name' => $column_name, 'column_val' => $column_val));
  }
  
  /**
   * Good for fetching a row when there is unique column.
   * @param mixed $table
   * @param mixed $column_name
   * @param mixed $column_val
   * @return mixed
   */
  public function findOneBy($table, $column_name, $column_val)
  {
    return $this->queryOne('SELECT * from ' . $table . ' WHERE :column_name = :column_val', array('column_name' => $column_name, 'column_val' => $column_val));
  }
  
  /**
   * DOCUMENT ME
   * @param mixed $table
   * @param mixed $id
   * @return mixed
   */
  public function exists($table, $id)
  {
    return !!$this->find($table, $id);
  }

  /**
   * Writing SET clauses can be a pain. This method saves you the trouble for records
   * with id columns
   * @param mixed $table
   * @param mixed $id
   * @param mixed $params
   * @return mixed
   */
  public function update($table, $id, $params = array())
  {
    $q = 'UPDATE ' . $table . ' ' . $this->buildSetClauses($params);
    $params['id'] = $id;
    $q .= 'WHERE id = :id';
    return $this->query($q, $params);
  }

  /**
   * Builds a list of SET clauses to update the specified columns
   * (don't use me directly, see the update and insertOrUpdate methods).
   * In the ON DUPLICATE KEY UPDATE case we don't need the SET keyword,
   * so provide a flag for that 
   */
    
  public function buildSetClauses($params, $useSetKeyword = true)
  {
    $first = true;
    $q = '';
    foreach ($params as $k => $v)
    {
      if ($first)
      {
        if ($useSetKeyword)
        {
          $q .= 'SET ';
        }
        $first = false;
      }
      else
      {
        $q .= ', ';
      }
      $q .= $k . ' = :' . $k . ' ';
    }
    return $q;
  }

  /**
   * Useful when you need the current MySQL date. $relative can be -30 minutes, +30 days, etc.
   * @param mixed $relative
   * @return mixed
   */
  public function now($relative = '+0 seconds')
  {
    return date('Y-m-d H:i:s', strtotime($relative, time()));
  }

  /**
   * Return the count of sql() calls
   * @return mixed
   */
  public function getCommandsRun()
  {
    return $this->commandsRun;
  }
  
  /**
   * Does this database exist?
   * @param string $databaseName
   * @return boolean
   */
   public function databaseExists($databaseName)
   {
     $data = $this->query('SHOW DATABASES');
     foreach ($data as $row)
     {
       if ($row['Database'] === $databaseName)
       {
         return true;
       }
     }
     return false;
   }

   /**
    * Returns a list of all database names in the system.
    * May include "databases" internal to mysql, like
    * information_schema. Naive misuse of this function's
    * return value can be dangerous. Filter carefully before you
    * drop databases.
    * @return array
    */
    public function getDatabases()
    {
      $data = $this->query('SHOW DATABASES');
      $names = array();
      foreach ($data as $row)
      {
        $names[] = $row['Database'];
      }
      return $names;
    }

  /**
   * Does this table already exist?
   * @param mixed $tableName
   * @return mixed
   */
  public function tableExists($tableName)
  {
    if (!preg_match('/^\w+$/', $tableName))
    {
      throw new Exception("Bad table name in tableExists: $tableName\n");
    }
    $data = array();
    try
    {
      $query = $this->conn->query("SHOW CREATE TABLE $tableName");
      if (!$query)
      {
        throw new Exception('query is false, PDO sometimes does this for nonexistent tables, other times you get a different PDO exception from fetchAll, don\'t ask me why');
      }
      $data = $query->fetchAll();
    } catch (Exception $e)
    {
    }
    return (isset($data[0]['Create Table']));    
  }

  /**
   * Does this column already exist?
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
   * Return a value that will be unique for the column (assuming no race condition of course;
   * you should still use UNIQUE INDEX) by modifying $value until it doesn't already exist.
   * Trusts table and column (you would never let users enter metadata like that, right?)
   * @param mixed $table
   * @param mixed $column
   * @param mixed $value
   * @param mixed $exceptId
   * @return mixed
   */
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

  /**
   * Just check for uniqueness. When you are updating an existing row it is
   * convenient to pass the id of the existing row so keeping the value the same
   * is not considered a conflict
   * @param mixed $table
   * @param mixed $column
   * @param mixed $value
   * @param mixed $exceptId
   * @return mixed
   */
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

  /**
   * Grab just the ids from an array of results
   * @param mixed $results
   * @return mixed
   */
  public function getIds($results)
  {
    $ids = array();
    foreach ($results as $result)
    {
      $ids[] = $result['id'];
    }
    return $ids;
  }

  /**
   * Returns the # of rows affected by the most recent statement. Results are
   * undefined for SELECT statements, you should use a COUNT statement and
   * queryOneScalar for that
   */
  public function getRowsAffected()
  {
    return $this->rowsAffected;
  }
}
