<?php

// Conveniences for cross-database-compatible Doctrine programming 

class aDoctrine
{
  // Used to order the results of a query according to a specific list of IDs. 
  // If we used FIELD we would be limited to MySQL. So we use CASE instead (SQL92 standard).
  
  // Note that you are still responsible for adding a whereIn clause, if you
  // want to limit the results to this list of ids. If you don't, any extra objects
  // will be returned at the end
  
  static public function orderByList($query, $ids)
  {
    $col = $query->getRootAlias() . '.id';
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
  }
}