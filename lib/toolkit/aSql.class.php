<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 *
 * The aSql class adds content to Apostrophe sites really, really fast, bypassing the
 * performance issues of Doctrine. It is intended primarily for use in import operations
 * where many pages must be created quickly without running out of memory or taking
 * all year to finish the operation. This code is written with deep knowledge of the
 * Apostrophe schema, so if you are tempted to bypass the aPage class, use this 
 * class rather than hacking data into our schema yourself. 
 *
 * aSql now subclasses aMysql, which concerns itself solely with providing convenient
 * ways to do MySQL queries from PHP without the overhead of a full ORM and does not
 * have Apostrophe-specific features.
 *
 * We should have named the classes a bit differently to avoid confusion, but for
 * historical reasons that is not an option right now. In 2.0 (:
 */
class aSql extends aMysql
{
  protected $pdo;

  /**
   * DOCUMENT ME
   */
  public function deleteNonAdminPages()
  {
    $sql = 'DELETE FROM a_page where admin IS FALSE AND slug <> :g';
    $this->query($sql, array('g' => 'global'));
  }

  /**
   * 
   * Inserts a page from info array and updates the array with new fields
   * @param Array $info
   * @param string $title
   * @param int $parentId
   * @return array
   */
  public function insertPage(&$info, $title, $parentId)
  {
    if (isset($info['id']))
    {
      throw new sfException("fastSavePage doesn't know how to handle an existing page");
    }
    if (substr($info['slug'], 0, 1) !== '/')
    {
      // Virtual pages must not be in the page tree!
      list($lft, $rgt, $level) = array(null, null, null);
    }
    else
    {
      // This page needs to be the last child of its parent
      if(is_null($parentId))
      {
        list($lft, $rgt, $level) = array(0,1,-1);
      }
      else
      {
        $result = $this->query('SELECT lft, rgt, level FROM a_page WHERE id = :id', array('id' => $parentId));
        list($lft, $rgt, $level) = array($result[0]['lft'], $result[0]['rgt'], $result[0]['level']);
      }
    }
    // Ancestors and pages appearing below this page in the reorganize tree need their lft pointer nudged over
    $this->query('UPDATE a_page SET rgt = rgt + 2 WHERE rgt >= :rgt', array('rgt' => $rgt));
    // Pages appearing below this page in the reorganize tree - NOT its ancestors nor just its descendants - need their lft pointer nudged over
    $this->query('UPDATE a_page SET lft = lft + 2 WHERE lft > :rgt', array('rgt' => $rgt));
    $info['lft'] = $rgt;
    $info['rgt'] = $rgt + 1;
    $info['level'] = $level + 1;
    if (!isset($info['view_is_secure']))
    {
      $info['view_is_secure'] = false;
    }
    if ((!isset($info['archived'])) || (!$info['archived']))
    {
      $info['archived'] = false;
      if (!isset($info['published_at']))
      {
        $info['published_at'] = aDate::mysql();
      }
    }
    else
    {
      // Can't be null
      $info['published_at'] = aDate::mysql();
    }
    if (!isset($info['engine']))
    {
      $info['engine'] = null;
    }
    if(!isset($info['admin']))
    {
      $info['admin'] = false;
    }
    if(!isset($info['template']))
    {
      $info['template'] = 'default';
    }
    $this->query('INSERT INTO a_page (created_at, updated_at, slug, template, view_is_secure, archived, published_at, lft, rgt, level, engine, admin) VALUES (NOW(), NOW(), :slug, :template, :view_is_secure, :archived, :published_at, :lft, :rgt, :level, :engine, :admin)', $info);
    $info['id'] = $this->lastInsertId();

    $this->insertArea($info['id'], 'title', array(array('type' => 'aText', 'value' => $title)));

    return $info;    
  }

  /**
   * 
   * Inserts an area with its slots
   * @param int $aPageId
   * @param string $name
   * @param Array $slotInfos
   * @return <type>
   */
  public function insertArea($aPageId, $name, $slotInfos)
  {
    $this->fastClearArea($aPageId, $name);
    $slotIds = array();
    if (!count($slotInfos))
    {
      // Nothing to do
      return;
    }
    $this->query('INSERT INTO a_area (page_id, name, culture, latest_version) VALUES (:page_id, :name, :culture, 1)', array('page_id' => $aPageId, 'name' => $name, 'culture' => 'en'));
    $areaId = $this->lastInsertId();
    foreach ($slotInfos as $slotInfo)
    {
      $this->query('INSERT INTO a_slot (type, value) VALUES (:type, :value)', array('type' => $slotInfo['type'], 'value' => (isset($slotInfo['value']) ? (is_array($slotInfo['value']) ? serialize($slotInfo['value']) : $slotInfo['value']) : null)));
      $slotId = $this->lastInsertId();
      $slotIds[] = $slotId;
      if ($slotInfo['type'] === 'aSlideshow')
      {
        foreach ($slotInfo['value']['order'] as $mediaId)
        {
          $this->query('INSERT INTO a_slot_media_item (media_item_id, slot_id) VALUES (:media_item_id, :slot_id)', array('media_item_id' => $mediaId, 'slot_id' => $slotId));
        }
      }
      if ((($slotInfo['type'] === 'aImage') || ($slotInfo['type'] === 'aButton') || ($slotInfo['type'] === 'aVideo')) && isset($slotInfo['mediaId']))
      {
        $this->query('INSERT INTO a_slot_media_item (media_item_id, slot_id) VALUES (:media_item_id, :slot_id)', array('media_item_id' => $slotInfo['mediaId'], 'slot_id' => $slotId));
      }
    }

    $this->query('INSERT INTO a_area_version (area_id, version) VALUES(:area_id, 1)', array('area_id' => $areaId));
    $areaVersionId = $this->lastInsertId();
    $this->query('UPDATE a_area SET latest_version = :latest_version WHERE id = :id', array('id' => $areaId, 'latest_version' => 1));

    $n = 1;
    foreach ($slotIds as $slotId)
    {
      $this->query('INSERT INTO a_area_version_slot (slot_id, area_version_id, permid, rank) VALUES (:slot_id, :area_version_id, :permid, :rank)', array('slot_id' => $slotId, 'area_version_id' => $areaVersionId, 'permid' => $n, 'rank' => $n));
      $n++;
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $a
   */
  public function fastSaveMediaItem($a)
  {
    $data = $a->toArray();
    $this->query('INSERT INTO a_media_item (created_at, updated_at, slug, type, format, width, height, embed, title, description, credit, view_is_secure, service_url) VALUES (NOW(), NOW(), :slug, :type, :format, :width, :height, :embed, :title, :description, :credit, :view_is_secure, :service_url)', $data);
    $a->id = $this->lastInsertId();
  }

  /**
   * DOCUMENT ME
   * @param mixed $taggable_model
   * @param mixed $taggable_id
   * @param mixed $tags
   */
  public function fastSaveTags($taggable_model, $taggable_id , $tags)
  {
    // It would be faster to do fewer queries by caching what we know so far about tags
    foreach ($tags as $tag)
    {
      $existing = Doctrine::getTable('Tag')->createQuery('t')->where('t.name = ?', $tag)->execute(array(), Doctrine::HYDRATE_ARRAY);
      if (!count($existing))
      {
        $this->query('INSERT INTO tag (name) VALUES (:name)', array('name' => $tag));
        $existing['id'] = $this->lastInsertId();
      }
      else
      {
        $existing = $existing[0];
      }
      $this->query('INSERT INTO tagging (tag_id, taggable_model, taggable_id) VALUES (:tag_id, :taggable_model, :taggable_id)', array('tag_id' => $existing['id'], 'taggable_model' => $taggable_model, 'taggable_id' => $taggable_id));
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $aPageId
   * @param mixed $name
   */
  public function fastClearArea($aPageId, $name)
  {
    $this->query('DELETE FROM a_area WHERE name = :name AND page_id = :id', array('name' => $name, 'id' => $aPageId));
  }
}