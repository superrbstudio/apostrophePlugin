<ul class="a-breadcrumb-<?php echo $this->name ?>">
<?php foreach($nav as $pos => $item): ?>
<li class="a-breadcrumb-item <?php echo $item['class'] ?>"><?php echo link_to($item['title'], aTools::urlForPage($item['slug'])) ?><?php if($pos+1 < count($nav)) echo $seperator ?></li>
<?php endforeach ?>
</ul>