<?php
/**
 */
class PluginaMediaCategoryTable extends Doctrine_Table
{
  public function findAllAlphaInfo($includeUnused = false)
  {
    $query = Doctrine::getTable('aMediaCategory')->createQuery('mc')->select('mc.name, mc.slug, mc.id, COUNT(mi.id) as mc_count');
    if ($includeUnused)
    {
      $query->leftJoin('mc.MediaItems mi');
    }
    else
    {
      $query->innerJoin('mc.MediaItems mi');
    }
    $qresults = $query->groupBy('mc.id')->orderBy('mc.name asc')->fetchArray();
    $info = array();
    foreach ($qresults as $qresult)
    {
      $info[] = array('name' => $qresult['name'], 'slug' => $qresult['slug'], 'count' => $qresult['mc_count']);
    }
    return $info;
  }
}