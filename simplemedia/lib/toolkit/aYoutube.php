<?php

class aYoutube
{
  static public function search($q)
  {
    $feed = 'http://gdata.youtube.com/feeds/api/videos?' . 
      http_build_query(array('q' => $q));
    $document = simplexml_load_file($feed);
    $entries = $document->entry;
    $results = array();
    foreach ($entries as $entry)
    {
      $id = $entry->id;
      $id = strrchr($id, '/');
      if ($id === false)
      {
        continue;
      }
      $id = substr($id, 1);
      $results[] = array(
        'title' => (string) $entry->title,
        'tags' => (string) $entry->tags,
        'id' => $id);
    }
    return $results;
  }
  static public function embed($id, $width, $height)
  {
    $url = "http://www.youtube.com/v/$id&fs=1";
    return <<<EOM
<object width="$width" height="$height"><param name="movie" value="$url"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="$url" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="$width" height="$height"></embed></object>
EOM;
  }
}

# A simple test 
#$results = aYoutube::search("monkey cat");
#var_dump($results);
