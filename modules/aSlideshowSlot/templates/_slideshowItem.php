<?php
  // Compatible with sf_escaping_strategy: true
  $embed = isset($embed) ? $sf_data->getRaw('embed') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
  $n = isset($n) ? $sf_data->getRaw('n') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
?>
<?php use_helper('a') ?>
<ul>
  <?php $height = ($options['height']) ? 'height:' . $options['height'] . 'px;' : '' ?>
  <li class="a-slideshow-image" style="<?php echo $height ?><?php echo ($n==0)? 'display:block':'' ?>"><?php echo $embed ?></li>
  <?php if ($options['title'] && strlen($item->title)): ?>
    <li class="a-slideshow-meta a-slideshow-title"><?php echo $item->title ?></li>
  <?php endif ?>
  <?php if ($options['description'] && strlen($item->description)): ?>
    <li class="a-slideshow-meta a-slideshow-description"><?php echo $item->description ?></li>
  <?php endif ?>
  <?php if ($options['credit'] && strlen($item->credit)): ?>
    <li class="a-slideshow-meta a-slideshow-credit"><?php echo __('Photo Credit: %credit%', array('%credit%' => $item->credit), 'apostrophe') ?></li>
  <?php endif ?>
</ul>