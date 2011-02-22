<?php use_helper('I18N') ?>
<?php $page = aTools::getCurrentPage(); ?>
<?php if ($page): ?>
    <?php if ($sf_user->getAttribute("show-archived", 
      false, "apostrophe")): ?>
      <?php echo link_to(__("Hide \"Off\" Pages", null, 'apostrophe'), "a/showArchived?state=0&id=" . aTools::getCurrentPage()->getId()) ?>
    <?php else: ?>      
      <?php echo link_to(__("Show \"Off\" Pages", null, 'apostrophe'), "a/showArchived?state=1&id=" . aTools::getCurrentPage()->getId()) ?>
    <?php endif ?>
<?php endif ?>
