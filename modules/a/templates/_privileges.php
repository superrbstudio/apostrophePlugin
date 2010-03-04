<?php use_helper('I18N') ?>
<?php if (isset($form[$widget])): ?>
    <div class="a-form-row">
  
      <label><?php echo __($label) ?></label>
      <div class="a-page-settings-local-editors">
				<?php if (0): ?>
        	<h4><?php echo __('Local') ?></h4>	
				<?php endif ?>
        <?php echo $form[$widget] ?>
      </div>

      <?php if (count($inherited) > 0): ?>
      <div class="a-page-settings-inherited-editors">
        <h4><?php echo __('Inherited') ?></h4>
        <ul>
        <?php foreach($inherited as $editorName): ?>
          <li><?php echo htmlspecialchars($editorName) ?></li>
        <?php endforeach ?>
        </ul>
        <?php if (0): ?>
          <h4><?php echo __('Admin') ?></h4>
          <ul>
          <?php foreach($executive as $editorName): ?>
            <li><?php echo htmlspecialchars($editorName) ?></li>
          <?php endforeach ?>
          </ul>
        <?php endif ?>
      </div>
      <?php endif ?>
    
    </div>
<?php endif ?>
