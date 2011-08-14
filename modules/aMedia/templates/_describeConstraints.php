<?php // Yes, this is template code, but we use regular PHP syntax because we are building a sentence and the introduction of ?>
<?php // newlines wrecks the punctuation. (OK, we're building a ul list now...) ?>
<?php use_helper('a') ?>
<?php // Images support cropping, which makes some of the constraints unnecessary to display ?>
<?php // With other media types we must find an item that satisfies all of them. ?>
<?php if (aMediaTools::getAttribute('type') === 'image'): ?>
  <?php // With cropping only minimum-width and minimum-height matter at this stage, ?>
  <?php // so it won't be too scary to give them real information and it'll help people ?>
  <?php // who understand their tools a little ?>
	<?php if ($limitSizes): ?>
  	<h3 class="a-simple-constraints">
  	  <?php if (aMediaTools::getAttribute('minimum-width') && aMediaTools::getAttribute('minimum-height')): ?>
  	    <?php echo __('Only images with a minimum size of minimum-widthxminimum-height pixels are displayed below. <br/>Some images in your media library may not be large enough to be selected.', array('minimum-width' => aMediaTools::getAttribute('minimum-width'), 'minimum-height' => aMediaTools::getAttribute('minimum-height')), 'apostrophe') ?>
  	  <?php elseif (aMediaTools::getAttribute('minimum-width')): ?>
  	    <?php echo __('Only images with a minimum width of minimum-width pixels are displayed below. <br/>Some images in your media library may not be wide enough to be selected.', array('minimum-width' => aMediaTools::getAttribute('minimum-width')), 'apostrophe') ?></h4>
  	  <?php elseif (aMediaTools::getAttribute('minimum-height')): ?>
  	    <?php echo __('Only images with a minimum height of minimum-height pixels are displayed below. <br/>Some images in your media library may not be tall enough to be selected.', array('minimum-height' => aMediaTools::getAttribute('minimum-height')), 'apostrophe') ?></h4>
  	  <?php endif ?>
  	</h3>
	<?php endif ?>
<?php else: ?>
  <?php // No cropping, their only hope is to get proper details from us on what is allowed ?>
  <?php
  $clauses = array();
  if (aMediaTools::getAttribute('aspect-width') && aMediaTools::getAttribute('aspect-height'))
  {
    $clauses[] = __('A %w%x%h% aspect ratio', array('%w%' => aMediaTools::getAttribute('aspect-width'), '%h%' => aMediaTools::getAttribute('aspect-height')), 'apostrophe');
  }
  if (aMediaTools::getAttribute('minimum-width'))
  {
    $clauses[] = __('A minimum width of %mw% pixels', array('%mw%' => aMediaTools::getAttribute('minimum-width')), 'apostrophe');
  }
  if (aMediaTools::getAttribute('minimum-height'))
  {
    $clauses[] = __('A minimum height of %mh% pixels', array('%mh%' => aMediaTools::getAttribute('minimum-height')), 'apostrophe');
  }
  if (aMediaTools::getAttribute('width'))
  {
    $clauses[] = __('A width of exactly %w% pixels', array('%w%' => aMediaTools::getAttribute('width')), 'apostrophe');
  }
  if (aMediaTools::getAttribute('height'))
  {
    $clauses[] = __('A height of exactly %h% pixels', array('%h%' => aMediaTools::getAttribute('height')), 'apostrophe');
  }
  if (aMediaTools::getAttribute('type'))
  {
    // Internationalize the plural so that can be correct too
    $type = __(aMediaTools::getAttribute('type') . "s", null, 'apostrophe');
  } 
  else
  {
    $type = __("items", null, 'apostrophe');
  }
  if (count($clauses))
  {
    // Markup change: for I18N it's better to use a list here rather than
    // trying to create a sentence with commas and 'and'
    echo('<h4 class="a-constraints-description">' . __("Displaying only %t% with:", array('%t%' => $type), 'apostrophe') . '</h4>');
    echo('<ul class="a-constraints">');
    foreach ($clauses as $clause)
    {
      echo('<li>' . $clause . '</li>');
    }
    echo('</ul>');
  }
  ?>
<?php endif ?>
