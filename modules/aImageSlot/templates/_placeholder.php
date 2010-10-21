<?php if ($sf_user->isAuthenticated() && (isset($options['singleton']) != true)): ?>
	<?php ($options['width'])?  $width = $options['width'] .'px;': $width = '100%;'; ?>
	<?php ($options['height'])? $height = $options['height'].'px;' : $height = (($options['width']) ? floor($options['width']*.56):'100px;'); ?>		
	<?php $style = 'width:'.$width.' height:'.$height ?>
	<div class="a-media-placeholder" style="<?php echo $style ?>">
		<span style="line-height:<?php echo $height ?>px;"><?php echo $placeholderText ?></span>
	</div>
<?php endif ?>