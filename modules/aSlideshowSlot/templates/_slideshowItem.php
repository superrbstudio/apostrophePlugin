<ul>
  <li class="a-slideshow-image" style="height:<?php echo $options['height'] ?>;<?php echo ($n==0)? 'display:block':'' ?>"><?php echo $embed ?></li>
  <?php if ($options['title']): ?>
    <li class="a-slideshow-meta a-slideshow-title"><?php echo $item->title ?></li>
  <?php endif ?>
  <?php if ($options['description']): ?>
    <li class="a-slideshow-meta a-slideshow-description"><?php echo $item->description ?></li>
  <?php endif ?>
  <?php if ($options['credit'] && $item->credit): ?>
    <li class="a-slideshow-meta a-slideshow-credit">Photo Credit: <?php echo $item->credit ?></li>
  <?php endif ?>
</ul>