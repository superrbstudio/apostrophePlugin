<ul class="a-navigation a-navigation-<?php echo $name ?><?php echo (isset($tabs))? ' tabs':' accordion' ?> nav-level-depth-<?php echo $nest?>" id="a-tab-navigation-<?php echo $name ?>-<?php echo $nest ?>">

  <?php foreach($nav as $pos => $item): ?>

    <li class="<?php echo $item['class']?> <?php if($pos == 0) echo 'first' ?> <?php if($pos == 1) echo 'second' ?> <?php if($pos == count($pos) - 1) echo 'next-last' ?> <?php if($pos == count($nav)-1) echo 'last' ?>" id="a-tab-nav-item-<?php echo $name ?>-<?php echo $item['id']?>">

      <?php echo link_to($item['title'], aTools::urlForPage($item['slug'])) ?>

      <?php if(isset($item['children']) && $nest < $maxDepth): ?>
        <?php include_partial('aNavigation/accordion', array('nav' => $item['children'], 'draggable' => $draggable, 'maxDepth' => $maxDepth + 1, 'name' => $name, 'nest' => $nest+1, 'dragIcon' => $dragIcon)) ?>
      <?php endif ?>

      <?php if ($dragIcon && $draggable): ?>
        <span class="a-btn icon a-drag a-controls nobg"></span>
      <?php endif ?>

    </li>

  <?php endforeach ?>

</ul>

<?php if ($draggable): ?>
<script type="text/javascript" charset="utf-8">
	 //<![CDATA[
  $(document).ready(
    function() 
    {
			var nav = $("#a-tab-navigation-<?php echo $name ?>-<?php echo $nest ?>");
			
      nav.sortable(
      { 
        delay: 100,
        update: function(e, ui) 
        { 
          var serial = jQuery("#a-tab-navigation-<?php echo $name ?>-<?php echo $nest ?>").sortable('serialize', {key:'a-tab-nav-item[]'});
          var options = {"url":<?php echo json_encode(url_for('a/sortNav').'?page=' . $item['id']); ?>,"type":"POST"};
          options['data'] = serial;
          $.ajax(options);
					
					// Fixes Margin
					nav.children().removeClass('first second next-last last');
					nav.children(':first').addClass('first');
					nav.children(':last').addClass('last');
					nav.children(':first').next("li").addClass('second');
					nav.children(':last').prev("li").addClass('next-last');
        }
      });

    });
  //]]>
  </script>
<?php endif ?>