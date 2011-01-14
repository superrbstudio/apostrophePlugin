<?php

class aImporter
{
  
  protected $connection;
  /**
   *
   * @var aSql
   */
  protected $sql;
  protected $pageFiles = array();
  protected $pagesDir;
  protected $imagesDir;
  protected $pseudoSlotTypes = array('foreignHtml' => 'aRichText');
  protected $failedMedia = array();

  public function __construct(Doctrine_Connection $connection, $params = array())
  {
    $this->connection = $connection;
    $this->sql = new aSql($connection->getDbh());
    $this->initialize($params);
  }

  public function initialize($params)
  {
    $this->root = simplexml_load_file($params['xmlFile']);
    $this->pagesDir = $params['pagesDir'];
    $this->imagesDir = $params['imagesDir'];
  }

  public function import()
  {
    $this->sql->query('DELETE FROM a_page where slug <> "global"');
    $this->sql->query('DELETE FROM a_media_item');
    foreach ($this->root->Page as $page)
    {
      $this->parsePage($page);
    }
    //Add admin pages
    $root = current($this->sql->query('SELECT * FROM a_page where slug = :slug', array('slug' => '/')));
    $admin = array('slug' => '/admin', 'admin' => '1');
    $this->sql->insertPage($admin, 'Admin', $root['id']);
    $adminMedia = array('slug' => '/admin/media', 'admin' => '1', 'engine' => 'aMedia');
    $this->sql->insertPage($adminMedia, 'Media', $admin['id']);

    foreach ($this->pageFiles as $id => $info)
    {
      $file = $this->pagesDir . "/$id.xml";
      if(file_exists($file))
      {
        $root = simplexml_load_file($file);
        if ($root)
        {
          $this->parseAreas($root, $info['id']);
        }
      }
    }
  }

  public function parsePage(SimpleXMLElement $root, $parentId = null)
  {
    $info = array();
    $info['slug'] = $root['slug']->__toString();
    $info['template'] = isset($root['template']) ? $root['template']->__toString() : 'default';
    $title = $root['title']->__toString();  

    $this->sql->insertPage($info, $title, $parentId);

    if (isset($root['file-id']))
    {
      $this->pageFiles[$root['file-id']->__toString()] = $info;
    } else
    {
      $this->parseAreas($root, $info['id']);
    }

    foreach ($root->Page as $page)
    {
      $this->parsePage($page, $info['id']);
    }

    return $info;
  }

  public function parseAreas($root, $pageId)
  {
    foreach ($root->Area as $area)
    {
      $name = $area['name'];
      if (count($area->AreaVersion))
      {
        //We are importing history also
      } else
      {
        $slotInfos = array();
        foreach ($area->Slot as $slot)
        {
          $type = $slot['type']->__toString();
          $method = 'parseSlot' . $type;
          if (method_exists($this, $method))
          {
            $slotImport = $this->$method($slot);
            if($slotImport)
            {
              $slotInfos = array_merge($slotInfos, $slotImport);
            }
          }
        }
        if($slotInfos)
          $this->sql->insertArea($pageId, $name, $slotInfos);
      }
    }
  }

  protected function getSlotType($type)
  {
    if (isset($this->pseudoSlotTypes[$type]))
      return $this->pseudoSlotTypes[$type];

    return $type;
  }

  protected function parseSlotARichText(SimpleXMLElement $slot)
  {
    $info = array();
    $info['type'] = 'aRichText';
    $info['value'] = aHtml::simplify($slot->value->__toString());

    return array($info);
  }

  protected function parseSlotAText(SimpleXMLElement $slot)
  {
    $info = array();
    $info['type'] = 'aText';
    $info['value'] = aHtml::simplify($slot->value->__toString());

    return array($info);
  }

  protected function parseSlotAButton(SimpleXMLElement $slot)
  {
    $info = array();
    $info['type'] = 'aButton';
    $value = array();
    $value['title'] = (string) $slot->title;
    $value['url'] = (string) $slot->url;

    $ids = $this->getMediaItems($slot);
    
    if(count($ids))
    {
      $info['mediaId'] = $ids[0];
    }
    $info['value'] = $value;
    return array($info);
  }

  protected function parseSlotAImage(SimpleXMLElement $slot)
  {
    $info = array();
    foreach($this->getMediaItems($slot) as $id)
    {
      $info[] = array('type' => 'aImage', 'mediaId' => $id);
    }

    if(count($info))
      return $info;

    return false;
  }

  protected function parseSlotASlideshow(SimpleXMLElement $slot)
  {
    $info = array();
    $value = array();
    $order = array();
    foreach($this->getMediaItems($slot) as $id)
    {
      $order[] = $id;
    }
    $value['order'] = $order;
    $info = array('type' => 'aSlideshow', 'value' => $value);
    return array($info);
  }

  public function getMediaItems(SimpleXMLElement $slot)
  {
    $ids = array();
    foreach($slot->MediaItem as $item)
    {
      $id = $this->findOrAddMediaItem($item['src']);
      if($id) 
      {
        $ids[] = $id;
      }
    }

    return $ids;
  }

  protected function parseSlotForeignHtml(SimpleXMLElement $slot)
  {
    $html = $slot->value->__toString();
    $segments = preg_split('/((?:<a href=".*?".*?>\s*)?(?:<br \/>|&nbsp;|\s)*<img.*?src=".*?".*?>(?:<br \/>|&nbsp;|\s)*(?:\s*<\/a>)?)/i', $html, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    foreach ($segments as $segment)
    {
      $mediaItem = null;
      if (preg_match('/<img.*?src="(.*?)".*?>/i', $segment, $matches))
      {
        $src = $matches[1];
        // &amp; won't work if we don't decode it to & before passing it to the server
        $src = html_entity_decode($src);
        $mediaId = $this->findOrAddMediaItem($src, 'id');
        if (preg_match('/href="(.*?)"/', $segment, $matches))
        {
          $url = $matches[1];
        }
        // $mediaItem->save();
        if (!is_null($mediaId))
        {
          $slotInfo = array('type' => 'aImage', 'mediaId' => $mediaId, 'value' => array());
          if (isset($url))
          {
            $slotInfo = array('type' => 'aButton', 'value' => array('url' => $url, 'title' => ''), 'mediaId' => $mediaId);
          }
          $slotInfos[] = $slotInfo;
        }
      } else
      {
        $slotInfos[] = array('type' => 'aRichText', 'value' => aHtml::simplify($segment));
      }
    }
    return $slotInfos;
  }

  protected function findOrAddMediaItem($src, $returnType = 'id', $tag = true)
  {
    $mediaId = null;
    $slug = null;
    $info = pathinfo($src);
    $path = $info['dirname'] . '/' . $info['filename'];

    $dirname = $info['dirname'];
    // Remove any hostname before splitting for tags, also dump case differences
    $dirname = strtolower(preg_replace('|^\w+://.*?/|', '', $dirname));
    $tags = preg_split('#/#', $dirname);

    $newTags = array();
    foreach ($tags as $tag)
    {
      if (strlen($tag) > 1)
      {
        $newTags[] = $tag;
      }
    }
    $tags = $newTags;

    $extension = $info['extension'];
    $slug = aTools::slugify($path) . "-$extension";

    $filename = "web/uploads/media_items/$slug.original.$extension";
    // We need to encode spaces but not slashes...
    $src = str_replace(' ', '%20', $src);

    if (substr($src, 0, 5) !== 'http:')
    {
      $src = $this->imagesDir . '/' . $src;
    }

    $result = $this->sql->query('SELECT id FROM a_media_item WHERE slug = :slug', array('slug' => $slug));
    if (isset($result[0]['id']))
    {
      $mediaId = $result[0]['id'];
    } else
    {
      $mediaItem = new aMediaItem();
      $mediaItem->setTitle($slug);
      $mediaItem->setSlug($slug);
      if ($extension === 'pdf')
      {
        $mediaItem->setType('pdf');
      } else
      {
        $mediaItem->setType('image');
      }
      if (file_exists($filename))
      {
        $mediaItem->preSaveFile($filename);
      } else
      {
        $bad = isset($this->failedMedia[$src]);
        if (!$bad)
        {
          $tmpFile = aFiles::getTemporaryFilename();
          try
          {
            if (!copy($src, $tmpFile))
            {
              throw new sfException(sprintf('Could not copy file: %s', $src));
            }
            if (!$mediaItem->saveFile($tmpFile))
            {
              throw new sfException(sprintf('Could not save file: %s', $src));
            }
          } catch (Exception $e)
          {
            $this->failedMedia[$src] = true;
          }
          unlink($tmpFile);
        }
      }
      if (!isset($this->failedMedia[$src]))
      {
        $this->sql->fastSaveMediaItem($mediaItem);
        if ($tag)
        {
          $this->sql->fastSaveTags('aMediaItem', $mediaItem->id, $tags);
        }
        $mediaId = $mediaItem->id;
        // getOriginalPath needs a context, ugh
        $path = '/uploads/media_items/' . $mediaItem->slug . '.original.pdf';
        $mediaItem->free(true);
      }
    }
    if ($returnType === 'path')
    {
      return $path;
    } else
    {
      return $mediaId;
    }

    return false;
  }

}