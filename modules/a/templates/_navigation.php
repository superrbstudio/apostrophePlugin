<?php use_helper('I18N') ?>
<ul class="nav-level-depth-<?php echo $nest?>" id="a-tab-navigation-<?php echo $name ?>-<?php echo $nest ?>">
<?php foreach ($navigation as $id => $item): ?>
<li class="a-tab-nav-item <?php
  echo ($item->isFirst()) ? 'first ' : '';
  echo ($item->isLast()) ? 'last ' : '';
  echo ($item->isCurrent()) ? 'a-current-page ' : '';
  echo ($item->ancestorOfCurrentPage) ? 'ancestor-page ' : '';
  echo ($item->peerOfAncestorOfCurrentPage) ? 'ancestor-peer-page ' : '';
  echo ($item->peerOfCurrentPage) ? 'peer-page ' : '';
?>" id="a-tab-nav-item-<?php echo $name ?>-<?php echo $item->id ?>">
<?php echo link_to(__($item->getName(), null, 'apostrophe'), $item->getUrl()) ?> 
<?php if ($item->hasChildren()): ?>
<?php echo include_partial('a/navigation', array('page' => $page, 'name' => $name, 'draggable' => $draggable, 'navigation' => $item->getChildren(), 'classes' => $classes, 'pID' => $item->id, 'nest' => $nest + 1)); ?>
<?php endif ?>
</li>
<?php endforeach ?>
</ul>

<?php if ($draggable): ?>


<script type="text/javascript" charset="utf-8">
  //<![CDATA[
  $(document).ready(function(){
      $("#a-tab-navigation-<?php echo $name ?>-<?php echo $nest ?>").sortable(
      { 
        delay: 100,
        update: function(e, ui) 
        { 
          var serial = jQuery("#a-tab-navigation-<?php echo $name ?>-<?php echo $nest ?>").sortable('serialize', {key:'a-tab-nav-item[]'});
          var options = {"url":<?php echo json_encode(url_for('a/sortNav').'?page=' . $item->id); ?>,"type":"POST"};
          options['data'] = serial;
          $.ajax(options);

          // This makes the tab borders display properly after re-positioning
          $('.a-tab-nav-item').removeClass('last');
          $('.a-tab-nav-item').removeClass('first');
          $('.a-tab-nav-item:first').addClass('first');
          $('.a-tab-nav-item:last').addClass('last');          
        }
      });
    });
  //]]>
  </script>
<?php endif ?>