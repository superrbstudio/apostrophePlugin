<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
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

  /**
   * DOCUMENT ME
   * @param Doctrine_Connection $connection
   * @param mixed $params
   */
  public function __construct(Doctrine_Connection $connection, $params = array())
  {
    $this->connection = $connection;
    $this->sql = new aSql($connection->getDbh());
    $this->initialize($params);
  }

  /**
   * DOCUMENT ME
   * @param mixed $params
   */
  public function initialize($params)
  {
    $this->root = simplexml_load_file($params['xmlFile']);
    $this->pagesDir = $params['pagesDir'];
    $this->imagesDir = $params['imagesDir'];
  }

  /**
   * DOCUMENT ME
   */
  public function import()
  {
    $name = $this->root->getName();
    if (!in_array($name, array('site', 'subset')))
    {
      throw new Exception("Top level element must be site (replaces entire site) or subset (hint: use the parent attribute to specify existing parent page slugs)");
    }
    if ($name === 'site')
    {
      $this->sql->query('DELETE FROM a_page where slug <> "global"');
      $this->sql->query('DELETE FROM a_media_item');
    }
    foreach ($this->root->Page as $page)
    {
      $this->parsePage($page);
    }
    if ($name === 'site')
    {
      //Add admin pages
      $root = current($this->sql->query('SELECT * FROM a_page where slug = :slug', array('slug' => '/')));
      $admin = array('slug' => '/admin', 'admin' => '1', 'engine' => 'aAdmin');
      $this->sql->insertPage($admin, 'Admin', $root['id']);
      $adminMedia = array('slug' => '/admin/media', 'admin' => '1', 'engine' => 'aMedia');
      $this->sql->insertPage($adminMedia, 'Media', $admin['id']);
    }

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

  /**
   * DOCUMENT ME
   * @param SimpleXMLElement $root
   * @param mixed $parentId
   * @return mixed
   */
  public function parsePage(SimpleXMLElement $root, $parentId = null, $infoOverrides = array())
  {
    $info = array();
    $info['slug'] = $root['slug']->__toString();
    if (isset($root['engine']))
    {
      $info['engine'] = $root['engine']->__toString();
    }
    if (isset($root['parent']))
    {
      $info['parent'] = $root['parent']->__toString();
    }
    $info['template'] = isset($root['template']) ? $root['template']->__toString() : 'default';
    $title = $root['title']->__toString();  

    $info = array_merge($info, $infoOverrides);
    $this->sql->insertPage($info, $title, $parentId);
    if (isset($root['file-id']))
    {
      $this->pageFiles[$root['file-id']->__toString()] = $info;
    } else
    {
      $this->parseAreas($root, $info['id'], $title);
    }

    foreach ($root->Page as $page)
    {
      $this->parsePage($page, $info['id']);
    }

    return $info;
  }

  /**
   * DOCUMENT ME
   * @param mixed $root
   * @param mixed $pageId
   */
  public function parseAreas($root, $pageId, $title = null)
  {
    $counters = array();
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
            $slotImport = $this->$method($slot, $title, $counters);
            if($slotImport)
            {
              $slotInfos = array_merge($slotInfos, $slotImport);
            }
          }
          else
          {
            echo("I don't recognize $type, there is no $method\n");
          }
        }
        if($slotInfos)
        {
          $this->sql->insertArea($pageId, $name, $slotInfos);
        }
      }
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $type
   * @return mixed
   */
  protected function getSlotType($type)
  {
    if (isset($this->pseudoSlotTypes[$type]))
      return $this->pseudoSlotTypes[$type];

    return $type;
  }

  /**
   * DOCUMENT ME
   * @param SimpleXMLElement $slot
   * @return mixed
   */
  protected function parseSlotARichText(SimpleXMLElement $slot, $title = null, &$counters = null)
  {
    $info = array();
    $info['type'] = 'aRichText';
    $info['value'] = aHtml::simplify($slot->value->__toString());

    return array($info);
  }

  /**
   * DOCUMENT ME
   * @param SimpleXMLElement $slot
   * @return mixed
   */
  protected function parseSlotAText(SimpleXMLElement $slot, $title = null, &$counters = null)
  {
    $info = array();
    $info['type'] = 'aText';
    $info['value'] = aHtml::simplify($slot->value->__toString());

    return array($info);
  }

  /**
   * DOCUMENT ME
   * @param SimpleXMLElement $slot
   * @return mixed
   */
  protected function parseSlotAButton(SimpleXMLElement $slot, $title = null, &$counters = null)
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

  /**
   * DOCUMENT ME
   * @param SimpleXMLElement $slot
   * @return mixed
   */
  protected function parseSlotAImage(SimpleXMLElement $slot, $title = null, &$counters = null)
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

  /**
   * DOCUMENT ME
   * @param SimpleXMLElement $slot
   * @return mixed
   */
  protected function parseSlotASlideshow(SimpleXMLElement $slot, $title = null, &$counters = null)
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

  /**
   * DOCUMENT ME
   * @param SimpleXMLElement $slot
   * @return mixed
   */
  protected function parseSlotAFile(SimpleXMLElement $slot, $title = null, &$counters = null)
  {
    $ids = $this->getMediaItems($slot);
    $info = array('type' => 'aFile');
    if(count($ids))
    {
      $info['mediaId'] = $ids[0];
    }
    return array($info);
  }

  /**
   * DOCUMENT ME
   * @param SimpleXMLElement $slot
   * @return mixed
   */
  protected function parseSlotAVideo(SimpleXMLElement $slot, $title = null, &$counters = null)
  {
    $results = array();
    if (!empty($slot->embed))
    {
      $form = new aMediaVideoForm();
      $result = $form->classifyEmbed((string) $slot->embed);
      if ($result['ok'])
      {
        $n = isset($counters['aVideo']['n']) ? $counters['aVideo']['n'] : 1;
        $fakeTitle = $title . ' video ' . $n;
        $info = array('title' => isset($result['serviceInfo']['title']) ? $result['serviceInfo']['title'] : $title . ' video ' . $n,
          'embed' => $result['embed'],
          'width' => isset($result['width']) ? $result['width'] : null,
          'height' => isset($result['height']) ? $result['height'] : null,
          'format' => isset($result['format']) ? $result['format'] : null,
          'type' => 'video',
          'tags' => isset($result['serviceInfo']['tags']) ? preg_split('/\s*,\s*/', $result['serviceInfo']['tags']) : array(),
          'service_url' => isset($result['serviceInfo']['url']) ? $result['serviceInfo']['url'] : null);
          $mediaId = $this->findOrAddVideo($info);
          if ($mediaId)
          {
            $results[] = array('type' => 'aVideo', 'mediaId' => $mediaId);
          }
        $n++;
        $counters['aVideo']['n'] = $n;
      }
    }
    if (count($results))
    {
      return $results;
    }
    return false;
  }

  /**
   * DOCUMENT ME
   * @param SimpleXMLElement $slot
   * @return mixed
   */
  public function getMediaItems(SimpleXMLElement $slot)
  {
    $ids = array();
    foreach($slot->MediaItem as $item)
    {
      // TODO: refactor this to be more dynamic
      $options = array();
      if (isset($item['title']))
      {
        $options['title'] = $item['title'];
      }
      if (isset($item['description']))
      {
        $options['description'] = $item['description'];
      }
      if (isset($item['credit']))
      {
        $options['credit'] = $item['credit'];
      }
      
      $id = $this->findOrAddMediaItem($item['src'], 'id', true, $options);
      if ($id) 
      {
        $ids[] = $id;
      }
    }

    return $ids;
  }

  /**
   * DOCUMENT ME
   * @param SimpleXMLElement $slot
   * @return mixed
   */
  protected function parseSlotForeignHtml(SimpleXMLElement $slot, $title = null, &$counters = null)
  {
    $n = 1;
    $html = $slot->value->__toString();
    // Cases: wordpress 'caption' element (typically enclosing an image),
    // link to an image, plain ol' image, and object and iframe embeds
    $segments = aString::splitAndCaptureAtEarliestMatch($html, array('/\[caption.*?\].*?\[\/caption\]/is', '/\<a href=\"[^\"]+\"[^\>]*>\s*(?:\<br \/\>|&nbsp;|\s)*\<img.*?src="[^\"]+[^\>]*\>(?:\<br \/\>|&nbsp;|\s)*\<\/a\>/is', '/\<img.*?src="[^\"]+".*?\>/is', '/\<object.*?\>.*?\<\/object\>/is', '/\<iframe.*?\>.*?\<\/iframe\>/is'));
    // Empty bodies happen
    $slotInfos = array();
    foreach ($segments as $segment)
    {
      $mediaItem = null;
      $caption = false;
      $creditUrl = false;
      if (preg_match('/caption="(.*?)"/', $segment, $matches))
      {
        $caption = aHtml::toPlaintext($matches[1]);
        if (preg_match('/href="(.*?)"/', $segment, $matches))
        {
          $creditUrl = aHtml::toPlaintext($matches[1]);
        }
      }
      if (preg_match('/\<audio.*?\>.*?\<\/audio\>|\<video.*?\>.*?\<\/video\>|\<script.*?\>.*?\<\/script\>|\<object.*?\>.*?\<\/object\>|\<iframe.*?\>.*?\<\/iframe\>/is', $segment))
      {
        $form = new aMediaVideoForm();
        $result = $form->classifyEmbed($segment);
        if ($result['ok'])
        {
          $info = array('title' => $caption ? $caption : (isset($result['serviceInfo']['title']) ? $result['serviceInfo']['title'] : $title . ' video ' . $n),
            'embed' => $result['embed'],
            'width' => isset($result['width']) ? $result['width'] : null,
            'height' => isset($result['height']) ? $result['height'] : null,
            'format' => isset($result['format']) ? $result['format'] : null,
            'type' => 'video',
            'tags' => isset($result['serviceInfo']['tags']) ? preg_split('/\s*,\s*/', $result['serviceInfo']['tags']) : array(),
            'service_url' => isset($result['serviceInfo']['url']) ? $result['serviceInfo']['url'] : null);
            $mediaId = $this->findOrAddVideo($info);
            if ($mediaId)
            {
              $slotInfos[] = array('type' => 'aVideo', 'mediaId' => $mediaId);
            }
          $n++;
        }
      } elseif (preg_match('/<img.*?src="(.*?)".*?>/is', $segment, $matches))
      {
        $src = $matches[1];
        // &amp; won't work if we don't decode it to & before passing it to the server
        $src = html_entity_decode($src);
        // A link means we should make a button slot - unless the link
        // is to an image (typically a higher quality version of the
        // same thing). If the link is to an image, use it as the
        // src and don't make a button slot. Also, do not treat it as a
        // credit_url on sites that have those
        if (preg_match('/href="(.*?)"/', $segment, $matches))
        {
          $url = $matches[1];
          if (preg_match('/\.(jpeg|jpg|gif|png)$/i', $url))
          {
            $src = $url;
            $url = null;
            $creditUrl = null;
          }
        }
        // It's ambiguous whether the caption is a title or a credit. Some of
        // our clients have contractual obligations to show a credit, so when
        // importing their legacy blogs, we can't be too careful. Import it as
        // both title and credit. -Tom
        $mediaId = $this->findOrAddMediaItem($src, 'id', true, array('title' => isset($caption) ? $caption : null, 'credit' => $caption ? $caption : '', 'credit_url' => $creditUrl ? $creditUrl : null));
        if ($mediaId)
        {
          $slotInfo = array('type' => 'aImage', 'mediaId' => $mediaId, 'value' => array());
          if (isset($url))
          {
            $slotInfo = array('type' => 'aButton', 'value' => array('url' => $url, 'title' => $caption ? $caption : ''), 'mediaId' => $mediaId);
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

  /**
   * Adds an image or PDF (todo: should scan file extensions properly & import word docs etc).
   * @param mixed $src
   * @param mixed $returnType
   * @param mixed $tag
   * @param array $options (todo: list available options)
   * @return mixed
   */
  protected function findOrAddMediaItem($src, $returnType = 'id', $tag = true, $options = array())
  {
    $mediaId = null;
    $slug = null;
    $info = pathinfo($src);
    $path = $info['dirname'] . '/' . $info['filename'];

    $dirname = $info['dirname'];
    // Move any query string or hash string into the filename and out of the "extension"
    if (isset($info['extension']))
    {
      $qat = strpos($info['extension'], '?');
      if ($qat !== false)
      {
        $path .= substr($info['extension'], $qat);
        $info['extension'] = substr($info['extension'], 0, $qat);
      }
      $hashat = strpos($info['extension'], '#');
      if ($hashat !== false)
      {
        $path .= substr($info['extension'], $hashat);
        $info['extension'] = substr($info['extension'], 0, $hashat);
      }
      // Extension should be a clean Unix path component
      $info['extension'] = preg_replace('/[^\w]/', '', $info['extension']);
    }
    
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

    $extension = isset($info['extension']) ? $info['extension'] : 'unknown';
    $slug = aTools::slugify($path . "-$extension");
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
      $mediaItem->setSlug($slug);

      // Locate the appropriate type for this file extension.
      // This is much more thorough than it was, although ideally
      // we'd demand the mime type from the source server or
      // look at an apache mime.types file here
      $types = aMediaTools::getOption('types');
      $found = false;
      $testExtension = strtolower($extension);
      // In principle there could be a lot of formats that have two
      // extensions, but this is the only one we tend to care about
      if ($testExtension === 'jpeg')
      {
        $testExtension = 'jpg';
      }
      foreach ($types as $type => $info)
      {
        if (in_array($testExtension, $info['extensions']))
        {
          $mediaItem->setType($type);
          $mediaItem->setFormat($testExtension);
          $found = true;
          break;
        }
      }
      if (!$found)
      {
        echo("WARNING: unrecognized extension for $src cannot import\n");
        return false;
      }
      
      // handles options
      if (isset($options['title']))
      {
        $mediaItem->setTitle($options['title']);
      } else {
        $mediaItem->setTitle($slug);
      }

      if (isset($options['credit']))
      {
        $mediaItem->setCredit($options['credit']);
      }

      // WARNING: this field does not exist in the default plugin's schema.
      // If you need it, make sure you add it to the schema at project level.
      // Otherwise just don't use one
      if (isset($options['credit_url']))
      {
        $mediaItem->setCreditUrl($options['credit_url']);
      }
      
      if (isset($options['description']))
      {
        $mediaItem->setDescription($options['description']);
      }
      
      if (isset($options['credit']))
      {
        $mediaItem->setCredit($options['credit']);
      }
      
      $filename = $mediaItem->getOriginalPath($extension);
      
      if (file_exists($filename))
      {
        // Avoids costly double imports of media
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
          if (file_exists($tmpFile))
          {
            aFiles::unlink($tmpFile);
          }
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
    } 
    else
    {
      return $mediaId;
    }

    return false;
  }
  
  /**
   * Finds or adds a video without the overhead of a proper Doctrine save.
   * @param array $info
   * @return mixed
   */
  protected function findOrAddVideo($info)
  {
    $mediaId = null;
    $slug = null;

    if (!isset($info['title']))
    {
      $info['title'] = 'Imported video';
    }
    $slug = aTools::slugify((!empty($info['title'])) ? $info['title'] : ((!empty($info['service_url'])) ? $info['service_url'] : md5($info['embed'])));

    $result = $this->sql->query('SELECT id FROM a_media_item WHERE slug = :slug', array('slug' => $slug));
    if (isset($result[0]['id']))
    {
      $mediaId = $result[0]['id'];
    } else
    {
      $mediaItem = new aMediaItem();
      foreach ($info as $key => $value)
      {
        if ($key !== 'tags')
        {
          $mediaItem[$key] = $value;
        }
      }
      if (empty($mediaItem['title']))
      {
        $mediaItem->setTitle($slug);
      }
      else
      {
        $mediaItem->setTitle($info['title']);
      }
      $mediaItem->setSlug($slug);
      $mediaItem->setType('video');
      $service = null;
      if ($mediaItem->service_url)
      {
        $service = aMediaTools::getEmbedService($mediaItem->service_url);
      }
      if ($service)
      {
        $id = $service->getIdFromUrl($mediaItem->service_url);
        if ($service->supports('thumbnail'))
        {
          $filename = $service->getThumbnail($id);
          if ($filename)
          {
            // saveFile can't handle a nonlocal file directly, so
            // copy to a temporary file first
            $bad = isset($this->failedMedia[$filename]);
            if (!$bad)
            {
              $tmpFile = aFiles::getTemporaryFilename();
              try
              {
                if (!copy($filename, $tmpFile))
                {
                  throw new sfException(sprintf('Could not copy file: %s', $src));
                }
                if (!$mediaItem->saveFile($tmpFile))
                {
                  throw new sfException(sprintf('Could not save file: %s', $src));
                }
              } catch (Exception $e)
              {
                $this->failedMedia[$filename] = true;
              }
              aFiles::unlink($tmpFile);
            }
          }
        }
      }
      $this->sql->fastSaveMediaItem($mediaItem);
      if (count($info['tags']))
      {
        $this->sql->fastSaveTags('aMediaItem', $mediaItem->id, $info['tags']);
      }
      $mediaId = $mediaItem->id;
      $mediaItem->free(true);
    }
    return $mediaId;
  }
}
