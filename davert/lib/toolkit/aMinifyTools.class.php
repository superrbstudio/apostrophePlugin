<?php

class aMinifyTools
{
  // You too can do this in a plugin dependent on a, see the provided stylesheet
  // for how to correctly specify an icon to go with your button. See the
  // apostrophePluginConfiguration class for the registration of the event listener.
  static public function getGlobalButtons()
  {
    $user = sfContext::getInstance()->getUser();
    if ($user->hasCredential('admin'))
    {
      $is_minified = $user->getAttribute('a_minify_mode',false);

      $class = $is_minified ? 'a-minify-edit' : 'a-minify-preview';
      $label = $is_minified ? 'Edit Mode' : 'Preview Mode';

      aTools::addGlobalButtons(array(
        new aGlobalButton($label, 'aMinify/switch', $class)));
    }
  }
}
