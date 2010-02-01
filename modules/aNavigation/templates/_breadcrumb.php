<ul>
<?php foreach($nav as $pos => $item): ?>
<li><?php echo link_to($item['title'], aTools::urlForPage($item['slug'])) ?><?php if($pos+1 < count($nav)) echo $seperator ?></li>
<?php endforeach ?>
</ul>