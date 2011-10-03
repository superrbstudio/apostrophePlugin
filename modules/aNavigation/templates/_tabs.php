<?php // If you call getRaw() and then pass to another partial and call getRaw() again, ?>
<?php // you do not get the expected result http://trac.symfony-project.org/ticket/7825 ?>
<?php // (specifically, the issue raised by pablodip at the end of that thread). ?>
<?php // This can lead to double-unescaping and therefore to XSS attacks. Eek. The workaround ?>
<?php // is to do the getRaw() transformation only once in the final partial ?>

<?php include_partial('aNavigation/accordion', array('nav' => $nav, 'maxDepth' => $depth, 'nest' => $nest, 'ulClass' => $ulClass, 'draggable' => $draggable, 'name' => $name, 'dragIcon' => $dragIcon, 'tabs' => true, 'class' => $class, 'active' => $active)) ?>