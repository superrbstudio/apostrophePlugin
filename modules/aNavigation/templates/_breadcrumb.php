<ul id="a-breadcrumb-<?php echo ($name)? $name:'component' ?>" class="a-navigation breadcrumb a-navigation-<?php echo $name ?>">
<?php foreach($nav as $pos => $item): ?>
<li class="a-breadcrumb-item <?php echo $item['class'] ?>"><?php echo link_to($item['title'], aTools::urlForPage($item['slug'])) ?><?php if($pos+1 < count($nav)) echo '<span class="a-breadcrumb-separator">'.$separator.'</span>' ?></li>
<?php endforeach ?>
</ul>