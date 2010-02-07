<?php include_partial('a/simpleEditButton', array('name' => $name, 'permid' => $permid)) ?>

<ul id="a-mediabrowser-<?php echo $id ?>" class="a-mediabrowser">
<?php $first = true; $n=0; foreach ($items as $item): ?>
  <?php $iwidth = $width ?>
  <?php $iheight = $flexHeight ? floor(($width / $item->width) * $item->height) : $height ?>
  <?php if (($iwidth > $item->width) || ($iheight > $item->height)): ?>
    <?php $iwidth = $item->width ?>
    <?php $iheight = $item->height ?>
  <?php endif ?>
  <?php $embed = str_replace(
    array("_WIDTH_", "_HEIGHT_", "_c-OR-s_", "_FORMAT_"),
    array($iwidth, 
      $iheight, 
      $resizeType,
      $item->format),
    $item->embed) ?>
  <li class="a-mediabrowser-item" id="a-mediabrowser-item-<?php echo $id ?>-<?php echo $n ?>">
    <ul>
      <li class="a-mediabrowser-image" style="height:<?php echo $height ?>;<?php echo ($n==0)? 'display:block':'' ?>"><?php echo $embed ?></li>
      <li class="a-slideshow-title"><?php echo $item->title ?></li>
      <li class="a-slideshow-description"><?php echo $item->description ?></li>
      <li class="a-slideshow-credit"><?php echo $item->credit ?></li>
      <li class="a-slideshow-tags"><?php echo implode(", ", $item->tags) ?></li>
    </ul>
  </li>
<?php $first = false; $n++; endforeach ?>
</ul>
