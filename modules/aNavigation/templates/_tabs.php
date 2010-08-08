<?php
  // Compatible with sf_escaping_strategy: true
  $active = isset($active) ? $sf_data->getRaw('active') : null;
  $class = isset($class) ? $sf_data->getRaw('class') : null;
  $depth = isset($depth) ? $sf_data->getRaw('depth') : null;
  $dragIcon = isset($dragIcon) ? $sf_data->getRaw('dragIcon') : null;
  $draggable = isset($draggable) ? $sf_data->getRaw('draggable') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $nav = isset($nav) ? $sf_data->getRaw('nav') : null;
?>
<?php include_partial('aNavigation/accordion', array('nav' => $nav, 'maxDepth' => $depth, 'nest' => 0, 'draggable' => $draggable, 'name' => $name, 'dragIcon' => $dragIcon, 'tabs' => true, 'class' => $class, 'active' => $active)) ?>