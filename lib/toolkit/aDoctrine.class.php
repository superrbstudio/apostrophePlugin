<?php
/**
 * Conveniences for cross-database-compatible Doctrine programming
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aDoctrine
{

  /**
   * Used to order the results of a query according to a specific list of IDs.
   * If we used FIELD we would be limited to MySQL. So we use CASE instead (SQL92 standard).
   * Note that you are still responsible for adding a whereIn clause, if you
   * want to limit the results to this list of ids. If you don't, any extra objects
   * will be returned at the end.
   * YOU NEED TO HAVE AN EXPLICIT SELECT CLAUSE, if you don't the select clause added by this
   * method will override the default 'select everything' behavior and you will
   * get back nothing! I get burned by this myself.
   * Example:
   * 
   * $q = Doctrine::getTable('aMediaItem')->createQuery('m')->select('m.*')->whereIn('m.id', $ids);
   * $mediaItems = aDoctrine::orderByList($q, $ids)->execute();
   * @param mixed $query
   * @param mixed $ids
   * @param mixed $modelName
   * @return mixed
   */
  static public function orderByList($query, $ids, $modelName = null)
  {
    // If there are no IDs, then we don't alter the query at all. Otherwise we wind up
    // with an ELSE clause alone, which is an error in SQL
    if (!count($ids))
    {
      return $query;
    }
    if (is_null($modelName))
    {
      $modelName = $query->getRootAlias();
    }
    $col = $modelName . '.id';
    $n = 1;
    $select = "(CASE $col";
    foreach ($ids as $id)
    {
      $id = (int) $id;
      $select .= " WHEN $id THEN $n";
      $n++;
    }
    $select .= " ELSE $n";
    $select .= " END) AS id_order";
    $query->addSelect($select);
    $query->orderBy("id_order ASC");
    // Now it's a little more chainable
    return $query;
  }
}