<?php

class aMediaImporter
{
  // dir option must be the path to the folder to be imported. Note that the contents
  // will be removed after import (folders and unsupported files will not be removed)
  
  // feedback option must be a callable. This callable will be invoked with three 
  // arguments: $category, $message, and sometimes $file. $category will be 
  // info, warning, error or completed. The first three receive a string as the $message
  // and sometimes a related filename as the $file argument. The fourth, completed,
  // receives a total number of files converted so far as the $message argument.
  
  public $feedback;
  public $dir;
  
  public function __construct($options = array())
  {
    if (!isset($options['feedback']))
    {
      throw new sfException("Feedback option should be a valid callable");
    }
    $this->feedback = $options['feedback'];
    if (!isset($options['dir']))
    {
      throw new sfException('dir option is mandatory');
    }
    $this->dir = $options['dir'];
  }
  
  public function go()
  {
    $dir_iterator = new RecursiveDirectoryIterator($this->dir);
    $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
    $count = 0;
    foreach ($iterator as $sfile)
    {
      if ($sfile->isFile())
      {
        $file = $sfile->getPathname();
        if (preg_match('/(^|\/)\./', $file))
        {
          # Silently ignore all dot folders to avoid trouble with svn and friends
          $this->giveFeedback("info", "Ignoring dotfile", $file);
          continue;
        }
        $pathinfo = pathinfo($file);
        if ($pathinfo['filename'] === 'Thumbs.db')
        {
          continue;
        }
        $info = aImageConverter::getInfo($file);
        if ($info === false)
        {
          $this->giveFeedback("warning", "Not supported or corrupt", $file);
          continue;
        }
        $item = new aMediaItem();
        if ($info['format'] === 'pdf')
        {
          $item->type = 'pdf';
        }
        else
        {
          $item->type = 'image';
        }
        // Split it up to make tags out of the portion of the path that isn't dir (i.e. the folder structure they used)
        $dir = $this->dir;
        $dir = preg_replace('/\/$/', '', $dir) . '/';
        $relevant = preg_replace('/^' . preg_quote($dir, '/') . '/', '', $file);
        // TODO: not Microsoft-friendly, might matter in some setting
        $components = preg_split('/\//', $relevant);
        $tags = array_slice($components, 0, count($components) - 1);
        foreach ($tags as &$tag)
        {
          // We don't strictly need to be this harsh, but it's safe and definitely
          // takes care of some things we definitely can't allow, like periods
          // (which cause mod_rewrite problems with pretty Symfony URLs).
          // TODO: clean it up in a nicer way without being UTF8-clueless
          // (aTools::slugify is UTF8-safe)
          $tag = aTools::slugify($tag);
        }
        $item->title = aTools::slugify($pathinfo['filename']);
        $item->setTags($tags);
        if (!strlen($item->title))
        {
          $this->giveFeedback("error", "Files must have a basename", $file);
          continue;
        }
        // The preSaveImage / save / saveImage dance is necessary because
        // the sluggable behavior doesn't kick in until save and the image file
        // needs a slug based filename.
        if (!$item->preSaveImage($file))
        {
          $this->giveFeedback("error", "Save failed", $file);
          continue;
        }
        $item->save();
        if (!$item->saveImage($file))
        {
          $this->giveFeedback("error", "Save failed", $file);
          $item->delete();
          continue;
        }
        unlink($file);
        $count++;
        $this->giveFeedback("completed", $count, $file);
      }
    }
    $this->giveFeedback("total", $count);
  }
  
  public function giveFeedback($category, $message, $file = null)
  {
    // Yes it IS silly that 'callable' arrays don't work as variable functions grr
    call_user_func($this->feedback, $category, $message, $file);
  }
}
