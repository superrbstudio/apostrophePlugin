<?php

class aZendSearch
{
  // Returns just the IDs. See addSearchQuery for a better method to use if you're
  // pulling the actual objects from Doctrine. See searchLuceneWithScores if you
  // need the actual scores so that you can merge results from searches of
  // multiple tables
  
  static public function searchLucene(Doctrine_Table $table, $luceneQuery, $culture = null)
  {
    $raw = self::searchLuceneWithScores($table, $luceneQuery, $culture);
    return array_keys($raw);
  }

  static public function searchLuceneWithScores(Doctrine_Table $table, $luceneQueryString, $culture = null)
  {
    $results = self::searchLuceneWithValues($table, $luceneQueryString, $culture);
    $nresults = array();
    foreach ($results as $a => $result)
    {
      $nresults[$a] = $result->score;
    }
    return $nresults;
  }
  
  static public function searchLuceneWithValues(Doctrine_Table $table, $luceneQueryString, $culture = null, $andLuceneQuery = null)
   {
     // Ugh: UTF8 Lucene is case sensitive work around this
     if (function_exists('mb_strtolower'))
     {
       $luceneQueryString = mb_strtolower($luceneQueryString);
     }
     else
     {
       $luceneQueryString = strtolower($luceneQueryString);
     }
     
     // We have to register the autoloader before we can use these classes
     self::registerZend();

     // Specify character set. Apostrophe is always UTF-8
     $luceneQuery = Zend_Search_Lucene_Search_QueryParser::parse($luceneQueryString, 'utf-8');
     $query = new Zend_Search_Lucene_Search_Query_Boolean();
     $query->addSubquery($luceneQuery, true);
     if (!is_null($culture))
     {
       $culture = self::normalizeCulture($culture);
       $cultureTerm = new Zend_Search_Lucene_Index_Term($culture, 'culture'); 
       // Oops, this said $aTerm before. Thanks to Quentin Dugauthier
       $cultureQuery = new Zend_Search_Lucene_Search_Query_Term($cultureTerm);
       $query->addSubquery($cultureQuery, true);
     }
     
     if (!is_null($andLuceneQuery))
     {
      $query->addSubquery($andLuceneQuery, true); 
     }
     
     $index = $table->getLuceneIndex();

     $hits = $index->find($query);

     // Never look at more than 1000 results, no matter what. This is necessary
     // to avoid out of memory errors on large sites. Note that if 1,000 locked
     // pages precede the first unlocked page and you are logged out, you could
     // theoretically not get your result. In practice unlocked pages tend to be
     // prominent and come up early. A deeper fix for this would be problematic
     // since Zend won't let you unset the document for a query hit, permanently
     // attaching lots of memory to a hit once you peek at it to determine things
     // like the user's eligibility to see it based on other information in
     // Doctrine tables

     if (sfConfig::get('app_a_search_hard_limit', false))
     {
       $hits = array_splice($hits, 0, sfConfig::get('app_a_search_hard_limit'));
     }

     $ids = array();

     foreach ($hits as $hit)
     {
       $ids[$hit->primarykey] = $hit;
     }
     return $ids;
   }
  
  static public function addSearchQuery(Doctrine_Table $table, Doctrine_Query $q = null, $luceneQuery, $culture = null)
  {
    $name = $table->getOption('name');

    if (is_null($q))
    {
      $q = Doctrine_Query::create()
        ->from($name);
    }
    
    $results = $table->searchLucene($luceneQuery, $culture);
    
    if (count($results))
    {
      
      $alias = $q->getRootAlias();
      // Call addSelect so that we don't trash existing queries.
      $q->addSelect($alias.'.*');
      aDoctrine::orderByList($q, $results);
      $q->whereIn($alias.'.id', $results);
      return $q;
    }
    else
    {
      // Don't just let everything through when there are no hits!
      // Careful, be cross-database compatible
      $q->andWhere('0 = 1');
    }
    
    return $q;
  }

  // $scores becomes (assignment by reference) an associative array in which
  // the keys are your object IDs and the values are scores from Lucene. This is
  // useful in rare situations where you need to merge results from multiple
  // Lucene searches and preserve their relative scores. It's also useful if you
  // just want to display the scores.
  //
  // THIS ARRAY WILL CONTAIN EVERYTHING RETURNED BY LUCENE, which may include
  // object IDs that are excluded by other parameters of your Doctrine search. Refer
  // to your Doctrine results to determine which objects are relevant. Use 
  // $resultsWithScores to look up the scores of those objects.
  //
  // If you specify null for $q, a doctrine query will be created for you.
  // If you specify null for $culture, no culture will be specified in the
  // Lucene query.
  
  static public function addSearchQueryWithScores(Doctrine_Table $table, Doctrine_Query $q = null, $luceneQuery, $culture, &$scores)
  {
    $name = $table->getOption('name');

    if (is_null($q))
    {
      $q = Doctrine_Query::create()
        ->from($name);
    }
    
    $scores = $table->searchLuceneWithScores($luceneQuery, $culture);
    
    $results = array_keys($scores);
    if (count($results))
    {
      $alias = $q->getRootAlias();
      // Contrary to Jobeet the above is NOT enough, the results will
      // not be in Lucene result order. Use aDoctrine::orderByList to fix
      // that up in a portable way with a SQL92-compatible CASE statement.

      // Call addSelect so that we don't trash existing queries.
      $q->addSelect($alias.'.*');
      aDoctrine::orderByList($q, $results);
      $q->whereIn($alias.'.id', $results);
    }
    else
    {
      // Don't just let everything through when there are no hits!
      // Don't use just 'false', that is not guaranteed to be cross-database compatible.
      $q->andWhere('0 = 1');
    }
        
    return $q;
  }

  static public function purgeLuceneIndex(Doctrine_Table $table)
  {
    $file = $table->getLuceneIndexFile();

    if (file_exists($file))
    {
      sfToolkit::clearDirectory($file);
      rmdir($file);
    }
  }

  static public function rebuildLuceneIndex(Doctrine_Table $table)
  {
    self::purgeLuceneIndex($table);
    $index = $table->getLuceneIndex();
    
    // TODO: hydrate these one at a time once Doctrine supports
    // doing that efficiently
    $all = $table->findAll();
    foreach ($all as $item)
    {
      $item->updateLuceneIndex();
    }

    return $table->optimizeLuceneIndex();
  }
  
  static public function optimizeLuceneIndex(Doctrine_Table $table)
  {
    $index = $table->getLuceneIndex();

    return $index->optimize();
  }

  // If you're storing different search text for different cultures, but
  // at delete time you want to trash ALL the cultures for this object,
  // that's fine: just don't pass a culture to delete. That's appropriate
  // if, for instance, you are deleting a page from a CMS entirely, all
  // localizations included.

  // If you do pass a culture this method will remove the object from the
  // potential search results for that particular culture.

  static public function deleteFromLuceneIndex(Doctrine_Record $object, $culture = null)
  {
    $index = $object->getTable()->getLuceneIndex();
   
    // remove an existing entry
    $id = $object->getId();
    // 20090506: we can't use a regular query string here because
    // numbers (such as IDs) will get stripped from it. So we have
    // to build a query using the Zend Search API. Note that this means
    // the Jobeet sample code is incorrect.
    // http://framework.zend.com/manual/en/zend.search.lucene.searching.html#zend.search.lucene.searching.query_building

    $aTerm = new Zend_Search_Lucene_Index_Term($id, 'primarykey'); 
    $aQuery = new Zend_Search_Lucene_Search_Query_Term($aTerm);
    $query = new Zend_Search_Lucene_Search_Query_Boolean();
    $query->addSubquery($aQuery, true);
    if (!is_null($culture))
    {
      $culture = self::normalizeCulture($culture);
      $cultureTerm = new Zend_Search_Lucene_Index_Term($culture, 'culture'); 
      // Oops, this said $aTerm before. Thanks to Quentin Dugauthier
      $cultureQuery = new Zend_Search_Lucene_Search_Query_Term($cultureTerm);
      $query->addSubquery($cultureQuery, true);
    }
    if ($hits = $index->find($query))
    {
      // id is correct. This is the internal Zend search index id which is
      // not the same thing as the id of our object.

      // There should actually be only one hit for a specific id and culture
      foreach ($hits as $hit)
      {
        $index->delete($hit->id);
      }
    }
  }

  // You can use this directly, but also see below for a wrapper that 
  // saves in both doctrine and Zend, wrapping the whole thing
  // in a Doctrine transaction and rolling back on any Lucene exceptions.

  // The arguments are a bit messy for historical reasons (TODO: fix this in 2.0 with a nice options array).
  // Note that Lucene is not your database.
  
  // For things that must be searchable, use $fields. For things that must be stored for display as part of the
  // presentation of the search result, use $storedFields. Note that a searchable field is not stored for retrieval.
  // IF YOU WISH TO HAVE IT BOTH WAYS, you must store the field under a DIFFERENT NAME than that used to
  // index it, otherwise the storage overrides the indexing. Drove me nuts trying to figure this one out

  static public function updateLuceneIndex($options)
  {
    // NEW WAY: options as a single array
    if (is_array($options))
    {
      $object = $options['object'];
      $culture = isset($options['culture']) ? $options['culture'] : null;
      $fields = isset($options['indexed']) ? $options['indexed'] : array();
      $storedFields = isset($options['stored']) ? $options['stored'] : array();
      $keywords = isset($options['keywords']) ? $options['keywords'] : array();
      $boostsByField = isset($options['boosts']) ? $options['boosts'] : array();
    }
    else
    {
      throw new sfException("updateLuceneIndex now expects a single array of options, see aZendSearch::updateLuceneIndex");
    }
    self::deleteFromLuceneIndex($object, $culture);
    $index = self::getLuceneIndex($object->getTable());
    $doc = new Zend_Search_Lucene_Document();
   
    // store item id so we can retrieve the corresponding object
    $doc->addField(Zend_Search_Lucene_Field::Keyword('primarykey', $object->getId(), 'UTF-8'));
    if (!is_null($culture))
    {
      $doc->addField(Zend_Search_Lucene_Field::Keyword('culture', $culture, 'UTF-8'));
    }

    // Index the search fields
    foreach ($fields as $key => $value)
    {
      // Ugh: UTF8 Lucene is case sensitive work around this
      if (function_exists('mb_strtolower'))
      {
        $value = mb_strtolower($value);
      }
      else
      {
        $value = strtolower($value);
      }
      $field = Zend_Search_Lucene_Field::UnStored($key, $value, 'UTF-8');
      if (isset($boostsByField[$key]))
      {
      	$field->boost = $boostsByField[$key];
      }
      $doc->addField($field);
    }
    
    // Index the keyword fields
    foreach ($keywords as $key => $value)
    {
      // Ugh: UTF8 Lucene is case sensitive work around this
      if (function_exists('mb_strtolower'))
      {
        $value = mb_strtolower($value);
      }
      else
      {
        $value = strtolower($value);
      }
      $field = Zend_Search_Lucene_Field::Keyword($key, $value, 'UTF-8');
      if (isset($boostsByField[$key]))
      {
      	$field->boost = $boostsByField[$key];
      }
      $doc->addField($field);
    }
    
    // store the data fields (a big performance win over hydrating things with Doctrine)
    foreach ($storedFields as $key => $value)
    {
      $doc->addField(Zend_Search_Lucene_Field::UnIndexed($key, $value, 'UTF-8'));
    }
   
    // add item to the index
    $index->addDocument($doc);
    $index->commit();
  }
  
  // This does a clean job of saving the object in both doctrine and zend
  // without a lot of duplicated code, reducing the potential for
  // bugs. However if you use it your class must implement 
  // doctrineSave($conn), which is usually just a trivial wrapper around
  // a call to parent::save($conn). 

  // "What if I need to save additional related objects to some other
  // table as part of the save() operation for this object, and I want
  // that to be part of the transaction?" Do those things in 
  // your doctrineSave() method.

  static public function saveInDoctrineAndLucene($object, $culture = null, Doctrine_Connection $conn = null)
  {
    $conn = $conn ? $conn : $object->getTable()->getConnection();
    $conn->beginTransaction();
    try
    {
      $ret = $object->doctrineSave($conn);
      $object->updateLuceneIndex($culture);
      $conn->commit();
      return $ret;
    }
    catch (Exception $e)
    {
      $conn->rollBack();
      throw $e;
    }
  }

  // This does a clean job of deleting the object from both doctrine and 
  // zend without a lot of duplicated code, reducing the potential for
  // bugs. However if you use it your class must implement 
  // doctrineDelete($conn), which is a trivial wrapper around
  // a call to parent::delete($conn) (unless you need to delete
  // additional related objects from some other table perhaps, in
  // which case you should do that work in doctrineDelete too).

  static public function deleteFromDoctrineAndLucene($object, $culture = null, Doctrine_Connection $conn = null)
  {
    $conn = $conn ? $conn : $object->getTable()->getConnection();
    $conn->beginTransaction();
    try
    {
      $ret = $object->doctrineDelete($conn);
      aZendSearch::deleteFromLuceneIndex($object, $culture); 
      $conn->commit();
      return $ret;
    } 
    catch (Exception $e)
    {
      $conn->rollBack();
      throw $e;
    }
  }

  // Implementation details

  static protected $zendLoaded = false;
  static public function registerZend()
  {
    if (self::$zendLoaded)
    {
      return;
    }
    
    // Zend 1.8.0 and thereafter
    include_once('Zend/Loader/Autoloader.php');
    $loader = Zend_Loader_Autoloader::getInstance();
    // NOT the default autoloader, Symfony's is the default.
    // Thanks to Guglielmo Celata
    // $loader->setFallbackAutoloader(true);
    $loader->suppressNotFoundWarnings(false);
    
    // Before Zend 1.8.0
    // require_once 'Zend/Loader.php';
    // Zend_Loader::registerAutoload();
    
    self::$zendLoaded = true;
    
    // UTF8 tokenizer can be turned off if you don't have now off by default because it is really, really ignorant of English,
    // it can't even cope with plural vs singular, much less stemming
    
    // Thanks to Fotis. Also thanks to the Zend Lucene source 
    // for the second bit. iconv doesn't mean that PCRE was compiled
    // with support for Unicode character classes, which the Lucene
    // cross-language tokenizer requires to work. Lovely
    if (function_exists('iconv') && (@preg_match('/\pL/u', 'a') == 1))
    {
      Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8());
    }
  }

  static public function getLuceneIndex(Doctrine_Table $table)
  {
    self::registerZend();
   
    if (file_exists($index = $table->getLuceneIndexFile()))
    {
      return Zend_Search_Lucene::open($index);
    }
    else
    {
      // We don't have to worry about creating the parent anymore because
      // we're using aFiles::getWritableDataFolder()
      
      return Zend_Search_Lucene::create($index);
    }
  }
   
  static public function getLuceneIndexFile(Doctrine_Table $table)
  {
    return aFiles::getWritableDataFolder(array('zend_indexes')) .
      DIRECTORY_SEPARATOR . 
      $table->getOption('name').'.'.sfConfig::get('sf_environment').'.index';
  }

  static public function normalizeCulture($culture)
  {
    if (!strlen($culture))
    {
      $culture = sfConfig::get('sf_default_culture', 'en');
    }
    return $culture;
  }
}

?>
