<?php
/**
 * @package    apostrophePlugin
 * @subpackage    toolkit
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
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

  /**
   * DOCUMENT ME
   * @param mixed $options
   */
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

  /**
   * DOCUMENT ME
   */
  public function go()
  {
    $dir_iterator = new RecursiveDirectoryIterator($this->dir);
    $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
    $count = 0;
    $mimeTypes = aMediaTools::getOption('mime_types');
    // It comes back as a mapping of extensions to types, get the types
    $extensions = array_keys($mimeTypes);
    $mimeTypes = array_values($mimeTypes);
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
        // basename and filename seem backwards to me, but that's how it is in the PHP docs and
        // sure enough that's how it behaves
        if ($pathinfo['basename'] === 'Thumbs.db')
        {
          continue;
        }
        $vfp = new aValidatorFilePersistent(
          array('mime_types' => $mimeTypes,
            'validated_file_class' => 'aValidatedFile',
            'required' => false),
          array('mime_types' => 'The following file types are accepted: ' . implode(', ', $extensions)));
        $guid = aGuid::generate();
        try
        {
          $vf = $vfp->clean(
           array(
             'newfile' => 
               array('tmp_name' => $file, 'name' => $pathinfo['basename']), 
             'persistid' => $guid)); 
        } catch (Exception $e)
        {
          $this->giveFeedback("warning", "Not supported or corrupt", $file);
          continue;
        }
        
        $item = new aMediaItem();
        
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
        $item->title = aMediaTools::filenameToTitle($pathinfo['basename']);
        $item->setTags($tags);
        if (!strlen($item->title))
        {
          $this->giveFeedback("error", "Files must have a basename", $file);
          continue;
        }
        // The preSaveImage / save / saveImage dance is necessary because
        // the sluggable behavior doesn't kick in until save and the image file
        // needs a slug based filename.
        if (!$item->preSaveFile($vf))
        {
          $this->giveFeedback("error", "Save failed", $file);
          continue;
        }
        $item->save();
        if (!$item->saveFile($vf))
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

  /**
   * DOCUMENT ME
   * @param mixed $category
   * @param mixed $message
   * @param mixed $file
   */
  public function giveFeedback($category, $message, $file = null)
  {
    // Yes it IS silly that 'callable' arrays don't work as variable functions grr
    call_user_func($this->feedback, $category, $message, $file);
  }
}
