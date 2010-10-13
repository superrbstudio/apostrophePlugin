<?php
  // Compatible with sf_escaping_strategy: true
  $active = isset($active) ? $sf_data->getRaw('active') : null;
  $class = isset($class) ? $sf_data->getRaw('class') : null;
  $draggable = isset($draggable) ? $sf_data->getRaw('draggable') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $nav = isset($nav) ? $sf_data->getRaw('nav') : null;
  $separator = isset($separator) ? $sf_data->getRaw('separator') : null;
?>
<ul id="a-breadcrumb-<?php echo ($name)? $name:'component' ?>" class="a-nav a-nav-breadcrumb breadcrumb">
	<?php foreach($nav as $pos => $item): ?>
		<?php if (!$item['archived'] || $draggable): ?>
			<li class="<?php echo $class;
				if($item['slug'] == $active) echo ' a-current-page'; ?>"><?php echo link_to($item['title'], aTools::urlForPage($item['slug'])) ?><?php if($pos+1 < count($nav)) echo '<span class="a-breadcrumb-separator">'.$separator.'</span>' ?>
			</li>
		<?php endif ?>		
	<?php endforeach ?>
</ul>