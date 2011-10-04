<?php 

/**
 * Cache class that stores cached content in the main MySQL database
 * associated with this Apostrophe site (or another doctrine or PDO connection). 
 * For performance PDO calls are used via the aMysql class.
 * 
 * Although Doctrine is not used for queries there is a suitable table in the 
 * schema.yml file of this plugin so any new Apostrophe site is automatically
 * ready to cache.
 *
 * @package apostrophe
 * @subpackage cache
 * @author P'unk Avenue apostrophe@punkave.com
 */
 
class aMysqlCache extends sfCache
{
  protected $sql = null;
  
  /**
   * Initializes this aMysqlCache instance.
   *
   * Available options:
   *
   * doctrineConnection: a Doctrine connection object, OR
   * pdoConnection: a pdo connection object.
   *
   * Also respects the 'prefix' option defined by sfCache, which is prepended 
   * to the key as a namespace for all operations,
   * including clean(). This is critical for storing unrelated 
   * caches in the same database table. The default prefix is a little weird,
   * as you'll notice if you look in the db, but that was the choice of the
   * sfCache authors (:
   *
   * Also implements automatic_cleaning_factor and lifetime, per sfCache
   *
   * @see sfCache
   */
  public function initialize($options = array())
  {
    if (isset($options['doctrineConnection']))
    {
      $this->sql = new aMysql($options['doctrineConnection']->getDbh());
    }
    elseif (isset($options['pdoConnection']))
    {
      $this->sql = new aMysql($options['pdoConnection']);
    }
    else
    {
      $this->sql = new aMysql();
    }

    parent::initialize($options);
  }

  /**
   * @see sfCache
   */
  public function getBackend()
  {
    return $this->sql;
  }

  /**
   * @see sfCache
   */
  public function get($key, $default = null)
  {
    $key = $this->getOption('prefix') . $key;
    $value = $this->sql->queryOneScalar("SELECT value FROM a_cache_item WHERE k = :key AND timeout > :time", array("key" => $key, "time" => time()));
    return null === $value ? $default : $value;
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    $key = $this->getOption('prefix') . $key;
    return !!$this->sql->queryOneScalar("SELECT COUNT(*) FROM a_cache_item WHERE k = :key AND timeout > :time", array("key" => $key, "time" => time()));
  }

  /**
   * @see sfCache
   */
  public function set($key, $value, $lifetime = null)
  {
    $key = $this->getOption('prefix') . $key;
    
    if ($this->getOption('automatic_cleaning_factor') > 0 && rand(1, $this->getOption('automatic_cleaning_factor')) == 1)
    {
      $this->clean(sfCache::OLD);
    }

    $this->sql->query('INSERT INTO a_cache_item (k, value, timeout, last_mod) VALUES (:key, :value, :timeout, :last_mod) ON DUPLICATE KEY UPDATE k = :key, value = :value, timeout = :timeout, last_mod = :last_mod', array('key' => $key, 'value' => $value, 'timeout' => time() + $this->getLifetime($lifetime), 'last_mod' => time()));
    return !!$this->sql->getRowsAffected();
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    $key = $this->getOption('prefix') . $key;
    
    $this->sql->query('DELETE FROM a_cache_item WHERE k = :key', array('key' => $key));
    return !!$this->sql->getRowsAffected();
  }
  
  /**
   * Converts a pattern to a regular expression.
   *
   * A pattern can use some special characters:
   *
   *  - * Matches a namespace (foo:*:bar)
   *  - ** Matches one or more namespaces (foo:**:bar)
   *
   * @param string $pattern A pattern
   *
   * @return string A regular expression
   *
   * Borrowed from sfCache with a slight modification because
   * MySQL doesn't want delimiters on the regexp. Henry Spencer
   * regexps are more basic than PCRE but they are equivalent
   * for this case
   */
  protected function patternToRegexp($pattern)
  {
    $regexp = str_replace(
      array('\\*\\*', '\\*'),
      array('.+?',    '[^'.preg_quote(sfCache::SEPARATOR, '#').']+'),
      preg_quote($pattern)
    );

    return '^'.$regexp.'$';
  }

  /**
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    $pattern = $this->getOption('prefix') . $pattern;
    $this->sql->query('DELETE FROM a_cache_item WHERE k REGEXP :pattern', array('pattern' => self::patternToRegexp($pattern)));
    return !!$this->sql->getRowsAffected();
  }

  /**
   * @see sfCache
   */
  public function clean($mode = sfCache::ALL)
  {
    // Even clean() should respect the prefix
    $this->sql->query('DELETE FROM a_cache_item WHERE k REGEXP :pattern' . (sfCache::OLD == $mode ? ' AND timeout < :time ' : ''), array('pattern' => preg_quote($this->getOption('prefix'), '/') . '.*$', 'time' => time()));
    return !!$this->sql->getRowsAffected();
  }

  /**
   * @see sfCache
   */
  public function getTimeout($key)
  {
    $key = $this->getOption('prefix') . $key;
    return $this->dbh->queryOneScalar('SELECT timeout FROM a_cache_item WHERE k = :key AND timeout > :time', array('key' => $key, 'time' => time()));
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    $key = $this->getOption('prefix') . $key;
    return $this->dbh->queryOneScalar('SELECT last_mod FROM a_cache_item WHERE k = :key AND timeout > :time', array('key' => $key, 'time' => time()));
  }

  /**
   * Callback used when deleting keys from cache.
   */
  public function removePatternRegexpCallback($regexp, $key)
  {
    return preg_match($regexp, $key);
  }

  /**
   * @see sfCache
   */
  public function getMany($keys)
  {
    $keysByPrefixedKey = array();
    foreach ($keys as $key)
    {
      $keysByPrefixedKey[$this->getOption('prefix') . $key] = $key;
    }
    $prefixedKeys = array_keys($keysByPrefixedKey);
    $raw = $this->sql->queryScalar("SELECT value FROM a_cache_item WHERE k IN :keys AND timeout > :time", array("keys" => $prefixedKeys, "time" => time()));
    $values = array();
    foreach ($raw as $row)
    {
      $values[$keysByPrefixedKey[$row['k']]] = $row['value'];
    }
    return $values;
  }
}
