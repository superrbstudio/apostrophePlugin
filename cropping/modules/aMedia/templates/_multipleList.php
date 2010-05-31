<?php use_helper('I18N', 'jQuery') ?>

<?php $imageInfo = aMediaTools::getAttribute('imageInfo') ?>
<?php $ids = aArray::getIds(aMediaTools::getSelection()) ?>

<?php foreach ($items as $item): ?>
<li id="a-media-selection-list-item-<?php echo $item->getId() ?>" class="a-media-selection-list-item">
	<?php $id = $item->getId() ?>
  <ul class="a-controls a-media-multiple-list-controls">	
	  <li><?php echo jq_link_to_remote(__("remove this item", null, 'apostrophe'),
    array(
      'url' => 'aMedia/multipleRemove?id='.$id,
      'update' => 'a-media-selection-list',
			'complete' => 'aUI("a-media-selection-list"); aMediaDeselectItem('.$id.')', 
    ), array(
			'class'=> 'a-btn icon a-delete no-label',
			'title' => __('Remove', null, 'apostrophe'), )) ?>
		</li>
	</ul>	

	<?/*<div class="a-media-selected-item-drag-overlay" title="<?php echo __('Drag &amp; Drop to Order', null, 'apostrophe') ?>"></div>*/?>
	<div class="a-media-selected-item-overlay"></div>
  <img src="<?php echo url_for($item->getScaledUrl(aMediaTools::getOption('selected_constraints'))) ?>" />

</li>
<?php endforeach ?>

<script type="text/javascript" charset="utf-8">

	function aMediaItemsIndicateSelected(params)
	{
	  var ids = params.ids;
	  aCrop.init(params);
		$('.a-media-selected-overlay').remove();
		
	  var i;
	  for (i = 0; (i < ids.length); i++)
	  {
	    id = ids[i];
	    var selector = '#a-media-item-' + id;
	    if (!$(selector).hasClass('a-media-selected')) 
	    {
	      $(selector).addClass('a-media-selected');
			}
		}
	
		$('.a-media-item.a-media-selected').each(function(){
			$(this).children('.a-media-item-thumbnail').prepend('<div class="a-media-selected-overlay"></div>');
		});

	 	$('.a-media-selected-overlay').fadeTo(0, 0.66);
	}

	function aMediaDeselectItem(id)
	{
		$('#a-media-item-'+id).removeClass('a-media-selected');
		$('#a-media-item-'+id).children('.a-media-selected-overlay').remove();
	}

	$('.a-media-thumb-link').click(function(){
		$(this).addClass('a-media-selected');
	});

	$(document).ready(function() { // On page ready indicate selected items
		aMediaItemsIndicateSelected(
      {
        ids: <?php echo $ids ?>,
        aspectRatio: <?php echo $aspectRatio ?>,
        minimumWidth: <?php echo aMediaTools::getAttribute('minimum-width') ?>,
        minimumHeight: <?php echo aMediaTools::getAttribute('minimum-height') ?>,
        <?php // width height cropLeft cropTop cropWidth cropHeight hashed by image id ?>
        imageInfo: <?php echo json_encode(aMediaTools::getAttribute('imageInfo')) ?>
      });
		$('.a-media-selected-item-overlay').fadeTo(0,.35); //cross-browser opacity for overlay
		$('.a-media-selection-list-item').hover(function(){
			$(this).addClass('over');
		},function(){
			$(this).removeClass('over');			
		});
	});
</script>