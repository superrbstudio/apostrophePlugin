<?php if ($form['url']->hasError()): ?>
  <div class="a-error">Invalid URL. A valid example: http://www.punkave.com/</div>
<?php endif ?>
<div class="a-form-row">URL: <?php echo $form['url']->render() ?></div>
<div class="a-form-row">Title: <?php echo $form['title']->render() ?></div>
<?php echo $form->renderHiddenFields() ?>
