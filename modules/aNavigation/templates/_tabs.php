<ul>
<?php foreach($nav as $pos => $item): ?>
<li><?php echo link_to($item['title'], aTools::urlForPage($item['slug'])) ?>
<?php endforeach ?>
</ul>