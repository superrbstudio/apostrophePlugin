<?php if ($form['url']->hasError()): ?>
  <p class="a-error">Invalid URL. A valid example: http://www.punkave.com/</p>
<?php endif ?>
<p>URL: <?php echo $form['url']->render() ?></p>
<p>Title: <?php echo $form['title']->render() ?></p>