<?php use_helper('I18N') ?>
<ul id="a-breadcrumb">

<?php $first = true; ?> 
<?php $skipNext = false; ?>
<?php $ancestorsInfo[] = array('title' => $page->title, 'slug' => $page->slug, 'archived' => $page->archived, 'id' => $page->id); ?> <?php // ancestors info doesn't include the page itself ?>

<?php foreach ($ancestorsInfo as $pinfo): ?>

  <?php if ($skipNext): ?>
    <?php $skipNext = false ?>
    <?php continue ?>
  <?php endif ?>

  <?php if (!$first): ?>
  	<li class="a-breadcrumb-slash">/</li>
  <?php else: ?>
  	<?php $first = false; ?>
  <?php endif ?>
	
  <?php $title = $pinfo['title'] ?>
  <?php if ($pinfo['archived']): ?> 
    <?php $title = "<span class='a-archived-page'>".$title."</span>" ?>
  <?php endif ?>

  <?php if ($page->id === $pinfo['id']): ?>
		<li class="a-breadcrumb-title current-page" id="a-breadcrumb-title-rename">
			<?php include_partial('a/renamePage', array('page' => $page, 'edit' => $page->userHasPrivilege('edit'))) ?>
		</li>
	<?php else: ?>
		<li class="a-breadcrumb-title" id="a-breadcrumb-title-<?php echo $pinfo['id'] ?>">
			<?php echo link_to($title, aTools::urlForPage($pinfo['slug'])) ?>
		</li>
	<?php endif ?>
	
  <?php if ($page->id === $pinfo['id']): ?>
    <?php if ($page->userHasPrivilege('edit')): ?>  
		<li class="a-breadcrumb-page-settings" id="a-breadcrumb-page-settings">
      <?php $id = $page->id ?>
      <?php // Sets up open and close buttons, ajax loading of form ?>
      <?php echo a_remote_dialog_toggle(
        array("id" => "a-page-settings", 
          "label" => "Page Settings",
          "loading" => "/apostrophePlugin/images/a-icon-page-settings-ani.gif",
          "chadFrom" => ".a-breadcrumb-page-settings",
          "action" => "a/settings?id=$id")) ?>
		</li>												
    <?php endif ?>	
  <?php endif ?>

<?php endforeach ?>

<?php if ($page->userHasPrivilege('manage')): ?>
  <?php if (has_slot('a_add_page')): ?>
    <?php include_slot('a_add_page') ?>
  <?php else: ?>
  	<li class="a-breadcrumb-slash">/</li>
    <li id="a-breadcrumb-create-childpage" class="a-breadcrumb-create-childpage">
			<?php include_partial('a/createPage', array('page' => $page, 'edit' => $page->userHasPrivilege('edit'))); ?>
    </li>	
  <?php endif ?>
<?php endif ?>
<?php include_partial('a/breadcrumbExtra', array('page' => $page)); ?>
</ul>

<script type="text/javascript">
	$(document).ready(function(){
		$('#a-breadcrumb .a-breadcrumb-form.add').hide();
	});
</script>