<?php

class aLockFlock implements aLock
{
  protected $dir;

  protected $locks = array();

  /**
   * Options array can contain 'dir' which is a folder where lock files are
   * created. Otherwise data/a_writable/a/locks is used (see the code)
   */
  public function __construct($options)
  {
    if (isset($options['dir']))
    {
      $this->dir = $options['dir'];
    }
    else
    {
      $this->dir = aFiles::getWritableDataFolder(array('a', 'locks'));
    }
  }

  public function lock($name, $wait = true)
  {
    if (!preg_match('/^\w+$/', $name))
    {
      throw new sfException("Lock name is empty or contains non-word characters");
    }
    $dir = $this->dir;
    $file = "$dir/$name.lck";
    $tries = 0;
    while (true)
    {
      @$fp = fopen($file, 'a');
      if ($fp)
      {
        $flags = LOCK_EX;
        if (!$wait)
        {
          $flags |= LOCK_NB;
        }
        if (flock($fp, $flags))
        {
          break;
        }
      }
      if (!$wait)
      {
        return false;
      }
      $tries++;
      if ($tries == 30)
      {
        throw new sfException("Unable to obtain a lock after 30 seconds. Make sure $dir is on a filesystem that supports flock() calls or configure another locking class.");
      }
      sleep(1);
    } 
    $this->locks[] = $fp;
    return true;
  }
  
  /**
   * Release the most recent lock, if any. It is not an error to call with no current locks
   */
  public function unlock()
  {
    if (count($this->locks))
    {
      $fp = array_pop($this->locks);
      fclose($fp);
    }
  }
}