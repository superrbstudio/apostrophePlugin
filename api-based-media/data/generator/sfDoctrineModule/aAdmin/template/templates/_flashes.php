[?php if ($sf_user->hasFlash('notice')): ?]
  <div class="a-admin-flashes notice">[?php echo __($sf_user->getFlash('notice'), array(), 'a-admin') ?]</div><br class="c"/>
[?php endif; ?]

[?php if ($sf_user->hasFlash('error')): ?]
  <div class="a-admin-flashes error">[?php echo __($sf_user->getFlash('error'), array(), 'a-admin') ?]</div><br class="c"/>
[?php endif; ?]
