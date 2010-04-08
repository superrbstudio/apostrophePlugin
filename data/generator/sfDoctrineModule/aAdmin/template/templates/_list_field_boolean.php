[?php if ($value): ?]
  [?php echo image_tag(((sfConfig::get('app_aAdmin_web_dir'))?sfConfig::get('app_aAdmin_web_dir'):'/apostrophePlugin').'/images/tick.png', array('alt' => __('Checked', array(), 'apostrophe'), 'title' => __('Checked', array(), 'apostrophe'))) ?]
[?php else: ?]
  &nbsp;
[?php endif; ?]
