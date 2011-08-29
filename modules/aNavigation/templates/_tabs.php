<?php
  // Compatible with sf_escaping_strategy: true
  $active = isset($active) ? $sf_data->getRaw('active') : null;
  $class = isset($class) ? $sf_data->getRaw('class') : null;
  $depth = isset($depth) ? $sf_data->getRaw('depth') : null;
  $dragIcon = isset($dragIcon) ? $sf_data->getRaw('dragIcon') : null;
  $draggable = isset($draggable) ? $sf_data->getRaw('draggable') : null;
  $name = isset($name) ? $sf_data->getRaw('name') : null;
  $nav = isset($nav) ? $sf_data->getRaw('nav') : null;
  $ulClass = isset($ulClass) ? $sf_data->getRaw('ulClass') : null;
  $nest = isset($nest) ? $sf_data->getRaw('nest') : null;
?>
<?php include_partial('aNavigation/accordion', array('nav' => $nav, 'maxDepth' => $depth, 'nest' => $nest, 'ulClass' => $ulClass, 'draggable' => $draggable, 'name' => $name, 'dragIcon' => $dragIcon, 'tabs' => true, 'class' => $class, 'active' => $active)) ?>