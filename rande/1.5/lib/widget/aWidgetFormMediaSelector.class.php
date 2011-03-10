<?php


class aWidgetFormMediaSelector extends sfWidgetFormInputText
{
  
  /**
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addOption('mediaitem', false);
    $this->addOption('width', 150);
    $this->addOption('height', 150);
    $this->addOption('resizeType', 's');
    $this->addOption('format', 'jpg');
    $this->addOption('link_class', 'a-widget-form-media-selector-link');
    $this->addOption('template', '%s %s <br /> %s');
    $this->addOption('media_library_link', 'a_media_other');
    $this->addOption('media_library_params', array('mode' => 'widget','action' => 'select'));
    $this->addOption('fancy_config', array(
      'speedIn' => 600,
      'speedOut' => 200,
      'type' => 'iframe',
      'width' => '800',
      'height' => '600'
    ));
  }
  
  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $attributes['type'] = 'hidden';
    
    $hidden = parent::render($name, $value, $attributes, $errors);
    
    $html = "";
    if($item = $this->getOption('mediaitem'))
    {
      $img = $this->renderTag('img', array( 
        'src' => $item->getThumbnailPath($this->getOption('width'), $this->getOption('height'), array(
          'resizeType' => $this->getOption('resizeType'),
          'format'     => $this->getOption('format'),
        )),
        'class' => 'a-widget-form-media-selector-image',
        'width'   => $this->getOption('width'),
        'height'  => $this->getOption('height'),
        'resizeType' => $this->getOption('resizeType'),
        'format'  => $this->getOption('format'),
        'item'    => $item->id,
        'id'      => $this->generateId($name).'-preview'
      ));
    }
    else
    {
      $img = $this->renderTag('img', array( 
        'src'     => "",
        'class'   => 'a-widget-form-media-selector-image',
        'width'   => $this->getOption('width'),
        'height'  => $this->getOption('height'),
        'resizeType' => $this->getOption('resizeType'),
        'format'  => $this->getOption('format'),
        'item'    => false,
        'id'      => $this->generateId($name).'-preview'
      ));
    }

    $params = $this->getOption('media_library_params');
    $params['widget_id'] = $this->generateId($name);

    $link_id = $this->generateId($name).'-link';
    $link = sprintf('<a href="%s" class="%s" rel="%s" id="%s">%s</a>',
        url_for($this->getOption('media_library_link'), $params ),
        $this->getOption('link_class'),
        $this->generateId($name),
        $link_id,
        $this->translate('click here to select a media')
    );

    $div = $this->renderContentTag('div', sprintf($this->getOption('template'), $hidden, $img, $link), array(
      'class' => 'a-widget-form-media-selector-container',
      'id'    => $this->generateId($name).'-container'
    ));

    $div .= "\n<script>";

    $options = '';
    foreach($this->getOption('fancy_config') as $name => $value)
    {
      $options .= sprintf('%s: %s,', $name, json_encode($value));
    }

    $url = url_for('@a_media_other?action=clearSelecting');

    $div .= <<<JS

    jQuery(document).ready(function() {
        jQuery('a#{$link_id}').fancybox({
            {$options}
            onClosed: function() {
                jQuery.get('{$url}');
            }
        });
    });
    
JS;
    
    $div .= "function aWidgetFormMediaSelector(widget_id, id)
    {
      jQuery('#' + widget_id).val(id);
      jQuery('#' + widget_id + '-preview').attr('item', id);

      aWidgetFormMediaResetRefreshThumbnail();
      jQuery.fancybox.close();
    }

    function aWidgetFormMediaResetRefreshThumbnail()
    {
      jQuery('img.a-widget-form-media-selector-image').each(function() {
        var elm = jQuery(this);

        var width = elm.width();
        var height = elm.height();
        var resizeType = elm.attr('resizeType');
        var format = elm.attr('format');
        var id = elm.attr('item');
        
        var src = '".url_for('@a_media_other?action=redirectToMedia')."&id=' + id + '&width=' + width + '&height=' + height + '&resizeType=' + resizeType + '&format=' + format;
        elm.attr('src', src);
      });
    }
    
    function aWidgetFormMediaResetSelecting(url)
    {

      jQuery.get(url);
    }";
    
    $div .= "</script>";
    
    return $div;
  }
  
  public function getJavaScripts()
  {
    return array(
      '/apostrophePlugin/js/aWidgetFormMediaSelector.js'
    );
  }
}

