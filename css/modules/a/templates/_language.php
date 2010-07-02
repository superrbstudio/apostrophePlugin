<?php if (sfConfig::get('app_a_i18n_switch')): ?>
  <li id="a-language-switch">
    <form method="POST" action="<?php echo url_for('a/language') ?>">
      <?php $form = new aLanguageForm(null, array('languages' => sfConfig::get('app_a_i18n_languages'))) ?>
      <?php echo $form->renderHiddenFields() ?>
      <?php echo $form['language']->render() ?>
    </form>
		<script type="text/javascript" charset="utf-8">
      $('#a-language-switch select').change(function() { this.form.submit() });
    </script>
  </li>
<?php endif ?>