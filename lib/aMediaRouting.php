<?php

class aMediaRouting
{
  static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    $r = $event->getSubject();
    
    if (aMediaTools::getOption("routes_register") && in_array('aMedia', sfConfig::get('sf_enabled_modules')))
    {
      // NEW IN 0.5: media_items is now a subfolder of /uploads by default
      // because /uploads is world-writable by default in Symfony projects,
      // which removes configuration steps

      $r->prependRoute('a_media_image_show', new sfRoute('/media/view/:slug', array(
        'module' => 'aMedia',
        'action' => 'show'
      ), array('slug' => '^[\w\-]+$')));
      
      // Allow permalinks for PDF originals
      $r->prependRoute('a_media_image_original', new sfRoute('/uploads/media_items/:slug.original.:format', array(
        'module' => 'aMedia',
        'action' => 'original'
      ), array('slug' => '^[\w\-]+$', 'format' => '^(jpg|png|gif|pdf)$')));
      
      $r->prependRoute('a_media_image', new sfRoute('/uploads/media_items/:slug.:width.:height.:resizeType.:format', array(
        'module' => 'aMedia',
        'action' => 'image'
      ), array(
        'slug' => '^[\w\-]+$',
        'width' => '^\d+$',
        'height' => '^\d+$',
        'resizeType' => '^\w$',
        'format' => '^(jpg|png|gif)$'
      )));
      
      // What we want:
      // /media   <-- everything
      // /media/image   <-- media of type image
      // /media/video   <-- media of type video
      // /tag/tagname <-- media with this tag
      // /media/image/tag/tagname <-- images with this tag 
      // /media/video/tag/tagname <-- video with this tag
      // /media?search=blah blah blah  <-- searches are full of
      //                                   dirty URL-unfriendly characters and
      //                                   are traditionally query strings.
      
      $r->prependRoute('a_media_index', new sfRoute('/media', array(
        'module' => 'aMedia', 
        'action' => 'index'
      )));
      
      $r->prependRoute('a_media_index_type', new sfRoute('/media/:type', array(
        'module' => 'aMedia',
        'action' => 'index'
      ), array('type' => '(image|video)')));
      
      $r->prependRoute('a_media_index_tag', new sfRoute('/media/tag/:tag', array(
        'module' => 'aMedia',
        'action' => 'index'
      ), array('tag' => '.*')));
            
      $r->prependRoute('a_media_index_type_tag', new sfRoute('/media/:type/tag/:tag', array(
        'module' => 'aMedia',
        'action' => 'index'
      ), array('type' => '(image|video)', 'tag' => '.*')));
      
      // APIs
      $r->prependRoute('a_media_select', new sfRoute('/media/select', array(
        'module' => 'aMedia',
        'action' => 'select'
      )));
      
      $r->prependRoute('a_media_info', new sfRoute('/media/info', array(
        'module' => 'aMedia',
        'action' => 'info'
      )));
      
      $r->prependRoute('a_media_tags', new sfRoute('/media/tags', array(
        'module' => 'aMedia',
        'action' => 'tags'
      )));
      
      $r->prependRoute('a_media_upload_images', new sfRoute('/media/uploadImages', array(
        'module' => 'aMedia',
        'action' => 'uploadImages'
      )));
      
      $r->prependRoute('a_media_edit_images', new sfRoute('/media/editImages', array(
        'module' => 'aMedia',
        'action' => 'editImages'
      )));
      
      $r->prependRoute('a_media_new_video', new sfRoute('/media/newVideo', array(
        'module' => 'aMedia',
        'action' => 'newVideo'
      )));
      
      $r->prependRoute('a_media_edit_video', new sfRoute('/media/editVideo', array(
        'module' => 'aMedia',
        'action' => 'editVideo'
      )));
    }
  }
}
