<?php 

/**
 * Cache class that stores cached content in MongoDB.
 *
  * Other advantages: this class cleans only keys matching the prefix when clean()
 * is called, essential when a single backend stores several caches. removePrefix()
 * and clean() are both efficiently implemented. And it works for multiple sites on
 * a single server, which is difficult with sfMemcacheCache and sfAPCCache in that
 * any clearing of the cache clears everybody's cache with those implementations.
 *
 * Consider aMysqlCache if you are not ready for MongoDB. 
 *
 * @package apostrophe
 * @subpackage cache
 * @author P'unk Avenue apostrophe@punkave.com
 */
 
class aMongoDBCache extends sfCache
{
  protected $mongo = null;
  protected $db = null;
  protected $collection = null;
  
  /**
   * Initializes this aMongoDBCache instance.
   *
   * Available options:
   *
   * 'connection': an existing mongodb connection, OR
   * 'uri', a valid mongodb URI, OR
   * 'host' and 'port': mongodb server to connect to (defaults to 'localhost' and 27017)
   *
   * 'database': the name of the mongodb database (defaults to 'aMongoDBCache')
   *
   * 'collection': the name of the mongodb collection (defaults to 'aMongoDBCache')
   *
   * Also respects the 'prefix' option defined by sfCache, which is prepended 
   * to the key as a namespace for all operations, including clean(). 
   *
   * 'prefix' is critical for storing unrelated caches in the same backend. The 
   * default prefix is a little weird, as you'll notice if you look in the db, 
   * but that was the choice of the sfCache authors (:
   *
   * Also implements automatic_cleaning_factor and lifetime, per sfCache
   *
   * @see sfCache
   */
  public function initialize($options = array())
  {
    $this->options = $options;
    $database = isset($options['database']) ? $options['database'] : 'aMongoDBCache';
    if (isset($options['connection']))
    {
      $this->mongo = $options['connection'];
    }
    else
    {
      $uri = isset($options['uri']) ? $options['uri'] : null;
      if (is_null($uri))
      {
        $host = isset($options['host']) ? $options['host'] : 'localhost';
        $port = isset($options['port']) ? $options['port'] : 27017;
        $uri = "mongodb://$host:$port";
      }
      $this->mongo = new Mongo($uri);
    }
    $this->db = $this->mongo->{$database};
    $this->createCollection();
    parent::initialize($options);
  }

  protected function createCollection()
  {
    $collection = isset($this->options['collection']) ? $this->options['collection'] : 'aMongoDBCache';
    $this->collection = $this->db->{$collection};
    $this->collection->ensureIndex(array('key' => 1, 'unique' => true));
    $this->collection->ensureIndex(array('timeout' => 1));
  }

  /**
   * @see sfCache
   */
  public function getBackend()
  {
    return $this->mongo;
  }

  /**
   * @see sfCache
   */
  public function get($key, $default = null)
  {
    $document = $this->getDocument($key);
    return is_null($document) ? $default : $document['value'];
  }

  protected function getDocument($key)
  {
    $key = $this->getOption('prefix') . $key;
    return $this->collection->findOne(array('key' => $key, 'timeout' => array('$gt' => time())));
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    $key = $this->getOption('prefix') . $key;
    return !!$this->collection->findOne(array('key' => $key, 'timeout' => array('$gt' => time())), array('_id'));
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
    // I considered not using 'safe', but while an occasional failure is no big deal in a cache, it would not be great to be
    // unaware of continuously ongoing failures. Removing 'safe' might make this a bit faster, in which case you would need
    // to always return true. Use an 'upsert' operation which can insert if the record does not already exist:
    // http://www.mongodb.org/display/DOCS/Updating
    // Whoops, the 'upsert' was missing, a do-nothing cache isn't very useful!
    $result = $this->collection->update(array('key' => $key), 
      array('key' => $key, 'value' => $value, 'timeout' => time() + $this->getLifetime($lifetime), 'last_mod' => time()), 
      array('safe' => true, 'upsert' => true));
    return !!$result['ok'];
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    $key = $this->getOption('prefix') . $key;
    
    $result = $this->collection->remove(array('key' => $key), array('safe' => true));
    return !!$result['ok'];
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
    $regex = str_replace(
      array('\\*\\*', '\\*'),
      array('.+?',    '[^'.preg_quote(sfCache::SEPARATOR, '#').']+'),
      preg_quote($pattern)
    );

    $regex = '^' . $regex . '$';
    // Remove trailing .*$ from the regex as Mongo processes it more slowly and it is redundant
    // http://www.mongodb.org/display/DOCS/Advanced+Queries#AdvancedQueries-RegularExpressions
    $regex = preg_replace('/\.\*\$$/', '', $regex);
    return $regex;
  }

  /**
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    $pattern = $this->patternToRegexp($this->getOption('prefix') . $pattern);
    $result = $this->collection->remove(array('key' => array('$regex' => $pattern)), array('safe' => true));
    return !!$result['ok'];
  }

  /**
   * @see sfCache. Respects prefix, allowing it to function properly as a way of permitting
   * many caches to share a collection
   */
  public function clean($mode = sfCache::ALL)
  {
    $this->purge($mode, true);
  }

  /**
   * Calling this directly is handy when you want to explicitly specify false for the second parameter
   * in order to clear *everything* on purpose
   */
  public function purge($mode = sfCache::ALL, $respectPrefix = true)
  {
    $criteria = array();
    if ($respectPrefix)
    {
      // Don't forget ^
      // Careful, no delimiter with mongo's $regex
      // Don't add gratuitous trailing .*$, it just makes it slower according to the mongo docs
      $criteria['key'] = array('$regex' => '^' . preg_quote($this->getOption('prefix')));
    }
    if ($mode === sfCache::OLD)
    {
      $criteria['timeout'] = array('$lt', time());
    }
    if ((!isset($criteria['key'])) && (!isset($criteria['timeout'])))
    {
      error_log("Dropping and recreating since we are respecting neither a prefix nor a timeout");
      $result = $this->collection->drop();
      $this->createCollection();
    }
    else
    {
      $result = $this->collection->remove($criteria, array('safe' => true));
    }
    return !!$result['ok'];
  }

  /**
   * @see sfCache
   */
  public function getTimeout($key)
  {
    $key = $this->getOption('prefix') . $key;
    $document = $this->getDocument($key);
    if (!$document)
    {
      return null;
    }
    return $document['timeout'];
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    $key = $this->getOption('prefix') . $key;
    $document = $this->getDocument($key);
    if (!$document)
    {
      return null;
    }
    return $document['last_mod'];
  }

  /**
   * Callback used when deleting keys from cache. Not sure we need this since we have written a smarter removePattern method
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

    $documents = $this->collection->find(array('key' => array('$in' => $prefixedKeys), 'timeout' => array('$gt' => time())));

    $raw = $this->sql->queryScalar("SELECT value FROM a_cache_item WHERE k IN :keys AND timeout > :time", array("keys" => $prefixedKeys, "time" => time()));
    $values = array();
    foreach ($documents as $document)
    {
      $values[$keysByPrefixedKey[$row['k']]] = $document['value'];
    }
    return $values;
  }
}
