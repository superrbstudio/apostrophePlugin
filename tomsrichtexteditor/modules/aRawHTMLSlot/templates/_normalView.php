<?php use_helper('I18N') ?>
<?php include_partial('a/simpleEditButton', array('pageid' => $page->id, 'name' => $name, 'permid' => $permid)) ?>

<?php if (!strlen($value)): ?>
<ul class="a-raw-html-info">
  <?php if ($editable): ?>
    <li>
    <?php if (isset($options['directions'])): ?>
      <?php echo $options['directions'] ?>
    <?php else: ?>
      <?php echo __('Click edit to add raw HTML markup, such as embed codes.', null, 'apostrophe') ?> 
    <?php endif ?>
    </li>
    <li><?php echo __('Use this slot with caution. If bad markup causes the page to become uneditable, add ?safemode=1 to the URL and edit the slot to correct the markup.', null, 'apostrophe') ?></li>
  <?php endif ?>
</ul>
<?php else: ?>
  <?php if ($sf_params->get('safemode')): ?>
    <?php echo htmlspecialchars($value) ?>
  <?php else: ?>
    <?php echo $value ?>
  <?php endif ?>
<?php endif ?>

