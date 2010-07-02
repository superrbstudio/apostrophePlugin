<ul id="a-breadcrumb-<?php echo ($name)? $name:'component' ?>" class="a-nav a-nav-breadcrumb breadcrumb">
	<?php foreach($nav as $pos => $item): ?>
		<li class="<?php echo $class;
        if($item['slug'] == $active) echo ' a-current-page'; ?>"><?php echo link_to($item['title'], aTools::urlForPage($item['slug'])) ?><?php if($pos+1 < count($nav)) echo '<span class="a-breadcrumb-separator">'.$separator.'</span>' ?></li>
	<?php endforeach ?>
</ul>