<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aUpdateLinkedAccountsTask extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine')
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'update-linked-accounts';
    $this->briefDescription = 'download new items in linked media accounts';
    $this->detailedDescription = <<<EOF
The [apostrophe:update-linked-accounts|INFO] task retrieves new media items from
linked YouTube, Vimeo, etc. accounts and adds them to the Apostrophe media
repository. You should call it from cron or another scheduled task manager on a 
regular basis (for instance, every 20 minutes).

New items are copied complete with their metadata. Later changes on the account, such
as deletion of editing of metadata on the original YouTube account, are not downloaded.

Call it like this:

  [php /path/to/your/project/symfony apostrophe:update-linked-accounts|INFO]
EOF;
  }

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
  protected function execute($arguments = array(), $options = array())
  {
    // We need a basic context so we can call helpers to format text
    $context = sfContext::createInstance($this->configuration);
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();
    // PDO connection not so useful, get the doctrine one
    $conn = Doctrine_Manager::connection();
    
    $accounts = Doctrine::getTable('aEmbedMediaAccount')->findAll();
    foreach ($accounts as $a)
    {
      $perPage = 50;
      $service = aMediaTools::getEmbedService($a->service);
      if (!$service)
      {
        // An account for a service that has been deconfigured
        continue;
      }
      $total = null;
      $page = 1;
      $serviceUrls = array();
      while (true)
      {
        $results = $service->browseUser($a->username, $page, $perPage);
        if ($results === false)
        {
          // We hit the rate limit, the account is bad, etc. Just 
          // be tolerant and retry later. Would be nice to distinguish
          // these cases but it's not that hard to figure out an
          // account is gone
          break;
        }
        foreach ($results['results'] as $result)
        {
          $serviceUrls[] = $result['url'];
        }
        // We hit the end of the results for this user
        if (!count($results['results']))
        {
          break;
        }
        $page++;
      }
      if (count($serviceUrls))
      {
        $existingServiceUrls = Doctrine::getTable('aMediaItem')->createQuery('m')->select('m.service_url')->andWhereIn('m.service_url', $serviceUrls)->execute(array(), Doctrine::HYDRATE_SINGLE_SCALAR);
      }
      else
      {
        $existingServiceUrls = array();
      }
      $existingServiceUrls = array_flip($existingServiceUrls);
      foreach ($serviceUrls as $serviceUrl)
      {
        if (!isset($existingServiceUrls[$serviceUrl]))
        {
          // If Doctrine becomes a performance problem I could use PDO
          // and set lucene_dirty to let that clean itself up later
          $id = $service->getIdFromUrl($serviceUrl);
          $info = $service->getInfo($id);
          if (!$info)
          {
            // We are not actually allowed meaningful access to this video. Password protected for example
            continue;
          }
          $item = new aMediaItem();
          $item->setTitle($info['title']);
          // We want tags to be lower case, and slashes break routes in most server configs. 
          $info['tags'] = str_replace('/', '-', aString::strtolower($info['tags']));
          $item->setTags($info['tags']);
          $item->setDescription(aHtml::textToHtml($info['description']));
          $item->setCredit($info['credit']);
          $item->setServiceUrl($info['url']);
          
          $item->setType($service->getType());

          // The dance is this: get the thumbnail if there is one;
          // call preSaveFile to learn the width, height and format
          // before saving; save; and then saveFile to copy it to a
          // filename based on the slug, which is unknown until after save
          
          $thumbnail = $service->getThumbnail($id);
          
          if ($thumbnail)
          {
            // Grab a local copy of the thumbnail, and get the pain
            // over with all at once in a predictable way if 
            // the service provider fails to give it to us.
       
            $thumbnailCopy = aFiles::getTemporaryFilename();
            if (copy($thumbnail, $thumbnailCopy))
            {
              $item->preSaveFile($thumbnailCopy);
            }
          }
          
          $item->save();
          
          if ($thumbnail)
          {
            $item->saveFile($thumbnailCopy);
          }

          $item->free();
        }
      }
    }
  }
}
