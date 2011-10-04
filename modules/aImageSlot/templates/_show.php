<?php $item = $sf_data->getRaw('item') ?>
<?php $embed = $sf_data->getRaw('embed') ?>
<?php $options = $sf_data->getRaw('options') ?>
<?php $dimensions = $sf_data->getRaw('dimensions') ?>

<?php // one set of code with or without a real item so I don't goof ?>
<?php if ((!$item) && ($options['defaultImage'])): ?>
  <?php $item = new stdclass() ?>
  <?php $item->title = '' ?>
  <?php $item->description = '' ?>
  <?php $embed = '<img src="'.$options['defaultImage'].'" width="'.$options['width'].'" height="'.$options['height'].'" />' ?>
<?php endif ?>

<?php if ((!$item) && (!$options['defaultImage'])): ?>
	<?php include_partial('aImageSlot/placeholder', array('placeholderText' => a_("Add an Image"), 'options' => $options)) ?>
<?php endif ?>

<?php if ($item): ?>
  <ul class="a-media-image">
    <li class="a-image-embed">
    <?php if ($options['link']): ?>
      <?php $embed = "<a href='".$options['link']."'>$embed</a>" ?>
    <?php endif ?>
    <?php echo $embed ?>
    </li>
    <?php if ($options['title']): ?>
      <li class="a-media-meta a-image-title"><?php echo $item->title ?></li>
    <?php endif ?>
    <?php if ($options['description']): ?>
      <li class="a-media-meta a-image-description"><?php echo $item->description ?></li>
    <?php endif ?>
  </ul>
<?php endif ?>