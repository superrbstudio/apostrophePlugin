<?php

// A simple way to plug in different search engines in Apostrophe:
// implement subclasses of aSearchService and set app_a_search_service_class
// and, optionally, app_a_search_service_options which is passed to
// the constructor

abstract class aSearchService
{
  /**
   * update(array('item' => $item, 'text' => 'this is my text', 'info' => array(some-serializable-stuff), 'culture' => 'en')) 
   *
   * You may pass item_id and item_model options instead of item if you don't have a hydrated object.
   * item_model is the Doctrine model class name, not the MySQL table name.
   *
   * If you don't specify a culture option, a search document without a culture is added. This is returned only in
   * search queries that also have no culture specified.
   *
   * If you have different texts in the same object (title, keywords, body...) and want to weight
   * them differently, use 'texts' instead of 'text', like this:
   *
   * 'texts' => array(array('name' => 'title', 'weight' => 1.0, 'text' => 'body text'), array('weight' => 2.0, 'text' => 'title text'))
   *
   * Search services MAY do something interesting with the name of each text, like allowing searches
   * specifically on the title, but this is not mandatory.
   */

  abstract public function update($options);

  /**
   * delete(array('item' => $item)), or item_id and item_model if you don't want to hydrate objects
   * If you don't specify a culture option, all search engine contents relating to the item are removed
   * regardless of culture
   */
  abstract public function delete($options);

  /**
   * Add search to a Doctrine query. This is the only way to search. Unification of search
   * with Doctrine queries is the point here (:
   *
   * $q should be a Doctrine query object, in which the root model class you're querying
   * is what you want to filter by search.
   *
   * $search is the user's search text. The exact search query language depends on the
   * subclass you're using, but reasonable results should always be returned to the user who
   * just types a few likely words, even if some are not present.
   *
   * $options['culture'] limits results to a particular culture. If absent you get results
   * for all cultures and results for which no culture was specified when calling update().
   *
   * YOUR QUERY MUST HAVE EXPLICIT addSelect CALLS, OTHERWISE YOU WILL NOT RELIABLY GET RESULTS!
   * Do not draw any conclusions from success without addSelect() with any particular
   * subclass of aSearchService.
   *
   * IF YOU WANT THE 'info' option that was stored when 'update' was called, for instance
   * to store a search summary without expensive hydration of related objects, 
   * you MUST call getInfoForResult($result) for each item returned. This is guaranteed
   * to work whether the back end is able to return the info as part of the query directly
   * or not. It also unserializes it for you. 
   *
   * NOTES TO THOSE IMPLEMENTING NEW SERVICES (those simply using them may ignore this):
   *
   * The exact search query language depends on the subclass you're using, but these minimum requirements
   * are imposed:
   *
   * * Reasonable results MUST always be returned to the user who just types a few likely words. 
   * * A wildcard ("*") at the end of a word MUST match all words with that prefix. Support for
   *   wildcards elsewhere in a word is optional. This is necessary for blog post slot title typeahead.
   * * Unrecognized punctuation SHOULD be ignored in such a way that reasonable results are still
   *   provided based on the words typed.
   * 
   * The default behavior should be "the more words matched the better." If your search engine can favor 
   * words in correct order, even better. Search engines should ignore punctuation marks they do not understand. 
   * If your search engine can handle searches for punctuation marks themselves, you should make sure 
   * they are in quotation marks. These rules allow for a variety of search syntaxes with reasonably 
   * intuitive default behaviors when the search engine doesn't know about them.
   *
   * Search services MAY do something interesting with the name of each text, like allowing searches
   * specifically on the title as part of a query language, but this is not mandatory and the
   * Apostrophe core no longer depends on the availability of such features (that's what joins are for).
   *
   * Be aware that Apostrophe sites can and do contain UTF8 characters not in the ASCII
   * character set. You MUST support UTF8 fully.
   *
   * You must support the 'culture' option and filter results accordingly.
   *
   * You may implement options specific to a particular subclass only if you prefix them, 
   * for instance: 'mySearchService-foo'
   *
   * Results should be returned in descending order of quality.
   *
   * Results should include the alias columns a_search_score (the relative score used to
   * sort the results in descending order) and a_search_info (the extra info that was
   * stored in the update() call).
   * 
   * Search services that do not use the same database as Apostrophe can implement
   * this method by searching their separate backend first, obtaining a list of 
   * object IDs and then adding an addWhereIn() clause to $q. However, remember that 
   * this does NOT return results in the order of your arguments to addWhereIn()! 
   * See Doctrine's FIELD() function for a solution to that problem. 
   *
   * Search services MUST add either a_search_info or a_search_document_id columns
   * into the results via select aliases, so that getInfoForResult can be used. 
   * if you add a_search_document_id then you must override getInfoBySearchDocumentId to
   * look up and unserialize the info stored with update() based on that value.
   * Hint to those merging results from some other search backend: you will already 
   * be using MySQL's FIELD() function to get the offset of a particular search 
   * result for sorting purposes. You can use that value as your a_search_document_id, 
   * order by it to keep your results in order, and also support looking up the 
   * stored info via that value in your override of getInfoBySearchDocumentId().
   * 
   * Search services MUST also add a_search_score, however it is acceptable to
   * return 1.0 for every row if it is not practical to merge this information
   * into the query, as displaying search scores is not really a recommended practice 
   * (although the occasional client insists). 
   */
  abstract public function addSearchToQuery($q, $search, $options = array());

  /**
   * $result is a single item from the array or collection returned when
   * the query built with addSearchToQuery was executed. We look for
   * appropriate alias columns
   */
  public function getInfoForResult($result)
  {
    if (isset($result['a_search_info']))
    {
      return unserialize($result['a_search_info']);
    }
    elseif (isset($result['a_search_document_id']))
    {
      $this->getInfoBySearchDocumentId($result['a_search_document_id']);
    }
    else
    {
      throw new sfException('Search service result rows must contain either a_search_info or a_search_document_id');
    }
  }
  
  public function getInfoBySearchDocumentId()
  {
    throw new sfException('Search services that use a_search_document_id must implement getInfoBySearchDocumentId');
  }

  /**
   * If your search engine requires a nightly optimize call from the
   * apostrophe:optimize-search-index task for best performance,
   * override this. The default assumption is that you don't need it.
   * (Most do have some profitable nightly optimization that can be done, 
   * like purging unreferenced words in aMysqlSearch.)
   */
  public function optimize()
  {
  }

  /**
   * $options['item_model'] can be set to delete search engine index entries
   * for a specific model class name only. $options['culture'] can also be set to 
   * delete search engine index entries for a specific culture only. Otherwise,
   * purge everything
   */
  abstract public function deleteAll($options);
}
