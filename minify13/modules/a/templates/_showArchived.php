<?php $page = aTools::getCurrentPage(); ?>
<?php if ($page): ?>
    <?php if ($sf_user->getAttribute("show-archived", 
      false, "a")): ?>
      <?php echo link_to("Hide \"Off\" Pages", "a/showArchived?state=0&id=" . aTools::getCurrentPage()->getId()) ?>
    <?php else: ?>      
      <?php echo link_to("Show \"Off\" Pages", "a/showArchived?state=1&id=" . aTools::getCurrentPage()->getId()) ?>
    <?php endif ?>
<?php endif ?>
