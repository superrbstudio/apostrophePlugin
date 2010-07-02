<?php

class aToolkitEvents
{
  // command.post_command
  static public function listenToCommandPostCommandEvent(sfEvent $event)
  {
    $task = $event->getSubject();
    if ($task->getFullName() === 'project:permissions')
    {
      $writable = aFiles::getWritableDataFolder();
      $task->getFilesystem()->chmod($writable, 0777);
      $dirFinder = sfFinder::type('dir');
      $fileFinder = sfFinder::type('file');
      $task->getFilesystem()->chmod($dirFinder->in($writable), 0777);
      $task->getFilesystem()->chmod($fileFinder->in($writable), 0666);
    }
  }
}

