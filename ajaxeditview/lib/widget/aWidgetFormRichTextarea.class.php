<?php

 /**
 * aWidgetFormRichTextarea represents a rich text editor.
 *
 * @author     Tom Boutell <tom@punkave.com>
 *
 * TODO:
 * 
 * Aesthetics
 * Keyboard control
 * Anchor tags
 * Table editing
 * Finish link browser
 *
 */
class aWidgetFormRichTextarea extends sfWidgetFormTextarea 
{
  /**
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {    
    $this->addOption('internal_browser', false);
    
    parent::configure($options, $attributes);
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
    $attributes = array_merge($attributes, $this->getOptions());
    $attributes = array_merge($attributes, array('name' => $name));
    $attributes = $this->fixFormId($attributes);
    $id = trim($attributes['id']);
    $name = $attributes['name'];
    if (!strlen($value))
    {
      // Quirk: if the value is blank there is no way to get started editing.
      $value = '&nbsp;';
    }
    $internalBrowser = json_encode($this->getOption('internal_browser'));
    $escapedValue = htmlspecialchars($value);
    return <<<EOM
<div class="aNewRichTextEditorWrapper" 
  id="$id-wrapper">
  <input type="hidden" name="$name" id="$id" value="$escapedValue" />
  <ul>
    <li class="a-btn bold"><a href="#">Bold</a></li>
    <li class="a-btn italic"><a href="#">Italic</a></li>
    <li class="a-btn normal"><a href="#">Normal</a></li>
    <li class="a-btn ainternal"><a href="#">Internal Link</a><select></select></li>
    <li class="a-btn aexternal"><a href="#">External Link</a></li>
    <li class="a-btn list"><a href="#">List</a></li>
    <li class="a-btn indent"><a href="#">&gt;</a></li>
    <li class="a-btn outdent"><a href="#">&lt;</a></li>
    <li class="a-btn h3"><a href="#">H3</a></li>
    <li class="a-btn h4"><a href="#">H4</a></li>
    <li class="a-btn h5"><a href="#">H5</a></li>
    <li class="a-btn h6"><a href="#">H6</a></li>
  </ul>
  <div class="aNewRichTextEditor" id="$id-editor" contentEditable="true">$value</div>    
</div>
<script type="text/javascript" charset="utf-8">
$(document).ready(function() {
  var wrapper = $('#$id-wrapper');
  var editor = wrapper.find('.aNewRichTextEditor');
  function command(cmd, arg)
  {
    // Affects the current editable element
    var result = document.execCommand(cmd, false, arg);
    $(editor).focus();
  }
  wrapper.find(".bold a").click(function() {
    command('bold');
    return false;
  });
  wrapper.find(".italic a").click(function() {
    command('italic');
    return false;
  });
  if (!$internalBrowser)
  {
    wrapper.find(".ainternal").hide();
  }
  else
  {
    wrapper.find(".ainternal select").hide();
    wrapper.find(".ainternal a").click(function() {
      $.get($internalBrowser, function(data) {
        var browser = $('.ainternal select');
        browser.html(data);
        browser.show();
        browser.change(function() {
          var url = browser.val();
          if (url.length)
          {
            command('createLink', url);
          }
          browser.hide();
        });
      });
      return false;
    });
  }
  wrapper.find(".aexternal a").click(function() {
    url = prompt('URL to link to');
    if (url)
    {
      command('createLink', url);
    }
    return false;
  });
  wrapper.find(".normal a").click(function() {
    command('removeFormat');
    command('removeLink');
    return false;
  });
  wrapper.find(".list a").click(function() {
    command('insertUnorderedList');
    return false;
  });
  wrapper.find(".indent a").click(function() {
    command('indent');
    return false;
  });
  wrapper.find(".outdent a").click(function() {
    command('outdent');
    return false;
  });
  wrapper.find(".h3 a").click(function() {
    command('formatBlock', '<h3>');
    return false;
  });
  wrapper.find(".h4 a").click(function() {
    command('formatBlock', '<h4>');
    return false;
  });
  wrapper.find(".h5 a").click(function() {
    command('formatBlock', '<h5>');
    return false;
  });
  wrapper.find(".h6 a").click(function() {
    command('formatBlock', '<h6>');
    return false;
  });
  // This works only for non-AJAX forms. For AJAX forms you'll have
  // to do this yourself as part of your submit
  $("#$id-editor").parents("form").submit(function() {
    $('#$id').val($('#$id-editor').html());
  })
}); 
</script>
EOM
;
  }
}
