<?php echo jq_link_to_function("Add Page", 
	'$("#a-breadcrumb-create-childpage-form").fadeIn(250, function(){ $(".a-breadcrumb-create-childpage-title").focus(); }); 
	$("#a-breadcrumb-create-childpage-button").hide(); 
	$("#a-breadcrumb-create-childpage-button").prev().hide();
	$(".a-breadcrumb-create-childpage-controls a.a-cancel").parent().show();', 
	array(
		'id' => 'a-breadcrumb-create-childpage-button', 
		'class' => 'a-btn icon a-add', 
)) ?>

<form method="POST" action="<?php echo url_for('a/create') ?>" id="a-breadcrumb-create-childpage-form" class="a-breadcrumb-form add">

	<?php $form = new aCreateForm($page) ?>
	<?php echo $form->renderHiddenFields() ?>
	<?php echo $form['parent']->render(array('id' => 'a-breadcrumb-create-parent', )) ?>
	<?php echo $form['title']->render(array('id' => 'a-breadcrumb-create-title', )) ?>

	<ul class="a-form-controls a-breadcrumb-create-childpage-controls">
	  <li>
			<button type="submit" class="a-btn">Create Page</button>			
		</li>
	  <li>
			<?php echo jq_link_to_function("cancel", 
				'$("#a-breadcrumb-create-childpage-form").hide(); 
				$("#a-breadcrumb-create-childpage-button").fadeIn(); 
				$("#a-breadcrumb-create-childpage-button").prev(".a-i").fadeIn();', 
				array(
					'class' => 'a-btn icon a-cancel', 
			)) ?>
		</li>
	</ul>

	<script type="text/javascript" charset="utf-8">
		aInputSelfLabel('#a-breadcrumb-create-title', 'Page Title');
	</script>

</form>