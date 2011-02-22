<?php

class sfWidgetFormSchemaFormatterAAdmin extends sfWidgetFormSchemaFormatter 
{
  protected
    $rowFormat = "<div class=\"a-form-row\">\n  %label%\n  <div class=\"a-form-field\">%field%</div> <div class='a-form-error'>%error%</div>\n %help%%hidden_fields%\n</div>\n",
    $errorRowFormat = '%errors%',
    $helpFormat = '<div class="a-form-help-text">%help%</div>',
    $decoratorFormat ="<div class=\"a-admin-form-container\">\n %content%\n</div>";
}
