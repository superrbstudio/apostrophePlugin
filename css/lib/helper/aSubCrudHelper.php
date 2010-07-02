<?php

use_helper('jQuery');

/**
 * Helper for outputting an AJAX form using a subclass of the doctrine form classes.
 * 
 * @param string $label for display purposes
 * @param string $type usually the model class, in lower case, but sometimes (as with "profile") that is too annoying to use 
 * in CSS etc., so it's a parameter here and also a property of each form object.
 * @param string $subtype the subtype, in lower case, otherwise exactly as found in the form subclass name ("essentials" or "registration"). 
 * It is also a property of each form object.
 * @param object $object should be a doctrine object that matches the form class that the subform extends
 * @param boolean $publishedColumn is the name of the boolean column that indicates this subform is ready for publication.
 * @param string $canEditMethod is the name of the method of the object that returns whether the user can edit
 * the object. If you do not specify a method, $object->userCanEdit() will be called.
 * If you pass the string 'read-only', the user will never see an Edit button, which is useful when
 * you want to display only the static view of the object reusing the same partials etc
 */

function a_sub_crud_chunk($label, $type, $subtype, $object, $publishedColumn = false, $canEditMethod = 'userCanEdit')
{
  $s = '';
  ob_start();
  $ok = false;
  if ($publishedColumn === false)
  {
    $ok = true;
  }
  elseif ($object->get($publishedColumn))
  {
    $ok = true;
  }

  // If the user can edit then they have to have access whether it's published or not!  
  if ($canEditMethod === 'read-only')
  {
    $canEdit = false;
  }
  else
  {
    $canEdit = $object->$canEditMethod();
  }
  if ($canEdit)
  {
    $ok = true;
  }
  if ($ok)
  {
  ?>
		<li class="form-chunk" id="form-chunk-<?php echo $subtype ?>">
		  <h3><?php echo $label ?><?php if ($canEdit): ?><?php echo a_sub_crud_edit('edit', $type, $subtype, $object) ?><?php endif ?></h3>

      <div id="<?php echo "$type-$subtype" ?>">
        <?php echo include_partial("$type/$subtype", array($type => $object)) ?>
      </div>
    </li>
  <?php
  }
  return ob_get_clean();
}

// Edit button used by the above

function a_sub_crud_edit($label, $type, $subtype, $object)
{
  $editButton = $type.'-form-edit-'.$subtype;
  $displayData = $type.'-'.$subtype;

  $url = sfContext::getInstance()->getRouting()->generate($type.'_edit', $object, true);
  
  return jq_link_to_remote('edit', array(
    'url'      => $url, 
    'method'   => 'get', 
    'update'   => $displayData, 
    'with'     => '"form='.$subtype.'"', 
    'before'   => sprintf("$('#%s').data('a-form-swap', $('#%s').html()); aBusy(this)", $displayData, $displayData), 
  	'complete' => sprintf("aReady('#%s'); $('#%s').hide()", $editButton, $editButton),
  ), array(
    'class' => 'a-form-edit-button',
    'id' => $editButton
  )); 
}

// Outputs the AJAX form for a chunk as seen above

function a_sub_crud_form_tag($form)
{
  list($type, $subtype, $displayData) = _a_sub_crud_form_info($form);
  
  // Necessary when we're editing a relation (EventUser) rather than the thing itself (Event)
  if (method_exists($form, 'getCrudObject'))
  {
    $object = $form->getCrudObject();
  }
  else
  {
    $object = $form->getObject();
  }

  $url = sfContext::getInstance()->getRouting()->generate($type.'_update', array('sf_subject' => $object, 'form' => $subtype), true);

  $s = jq_form_remote_tag(array(
    'url' => $url,
    'update' => $displayData, 
    // Redisplay the edit button only if the update does not contain a form.
    // This way the edit form is not resurrected by validation errors
    'complete' => "if (!$('#$type-$subtype form').length) { $('#$type-form-edit-$subtype').show(); }"));

  $s .= '<input type="hidden" name="sf_method" value="PUT" />';
  $s .= a_sub_crud_form_body($form);

  // Oops I left this out earlier
  $s .= "</form>\n";
  return $s;
}

// Non-AJAX equivalents. These can be used standalone if you simply want 
// comparable styling for a form page, or as part of chunks that look and act
// like the AJAX chunks but already have the forms preloaded, and refresh the 
// page on save. (The latter is necessary because the static view would otherwise
// show stale information.)

// Forms used with these do not need to have $type and $subtype properties.

// $prefix is a prefix for IDs generated for the edit button, the form and the static view.
// It should be unique enough to distinguish this chunk from other chunks in the page.

// If $canView is false the user doesn't see the chunk at all.
// If $canEdit is false the user sees the static view only. 
// If $preOpen is true the form should be initially visible (important for validation errors).

function a_sub_crud_nonajax_chunk($label, $url, $staticPartial, $staticArgs, $form, $prefix, $canView = true, $canEdit = true, $preOpen = false)
{
  $s = '';
  ob_start();
  $ok = $canView;

  if ($ok)
  {
  ?>
		<li class="form-chunk" id="<?php echo $prefix ?>-chunk">
		  <h3><?php echo $label ?><?php if ($canEdit): ?><?php echo a_sub_crud_nonajax_edit('edit', "$prefix-edit", "$prefix-static", "$prefix-form", !$preOpen) ?><?php endif ?></h3>

      <div style="<?php echo $preOpen ? 'display: none' : '' ?>" id="<?php echo "$prefix-static" ?>">
        <?php echo include_partial($staticPartial, $staticArgs) ?>
      </div>
      <div style="<?php echo $preOpen ? '' : 'display: none' ?>" id="<?php echo "$prefix-form" ?>">
        <?php echo a_sub_crud_nonajax_form_tag($form, $url, false, $prefix) ?>
      </div>
    </li>
  <?php
  }
  return ob_get_clean();
}

// For other non-AJAX forms that want to use the same styling. Targets the
// specified url, which is usually built with $this->generateUrl('route_name', $object) or similar

// If $cancelUrl is not false the cancel button returns to that action, otherwise it just
// restores the corresponding static view, locating the edit button, static view and
// form via the CSS ID prefix specified by $prefix. 

function a_sub_crud_nonajax_form_tag($form, $url, $cancelUrl = false, $prefix = false)
{
  $s = '<form method="POST" action="' . $url . '">'; 
  ob_start();
  include_stylesheets_for_form($form);
  include_javascripts_for_form($form);
  // Redundant when you echo $form
  // echo $form->renderGlobalErrors();
  echo $form;
?>
  <ul class="a-form-row submit">
  	<li><input type="submit" value="Save" class="a-sub-submit"/></li>
  	<li>
  	  <?php if ($cancelUrl !== false): ?>
  	    <?php echo link_to('Cancel', $cancelUrl, array("class" => "a-sub-cancel")) ?>
  	  <?php else: ?>
  	    <?php echo jq_link_to_function('Cancel', "$('#$prefix-edit').show(); $('#$prefix-static').show(); $('#$prefix-form').hide();", array("class" => "a-sub-cancel")) ?>
  	  <?php endif ?>
  	</li>
  </ul>
</form>
<?php
  $s .= ob_get_clean();  
  return $s;
}

/* Variant of edit button for non-ajax chunks. The form is initially hidden but unlike the
  true AJAX forms it is already loaded. You must supply DOM IDs for the button this function
  will generate, the static view that is swapped with the form, and the form. */

function a_sub_crud_nonajax_edit($label, $buttonId, $staticId, $formId, $visible = true)
{
  return jq_link_to_function(
    'edit', 
    "$('#$buttonId').hide(); $('#$staticId').hide(); $('#$formId').show();",
    array(
      'class' => 'a-form-edit-button',
      'id' => $buttonId,
      'style' => $visible ? '' : 'display: none'
  )); 
}

// A hybrid of the two: used to output a "create" form which targets the standard
// create action for the type declared in the form's type property. 
// Used in the 'new' action, and targets the 'create' action. 
// Does NOT create an AJAX form, just follows the same styling. $form is usually a 
// form that is also used as a chunk later to allow editing later of the minimum required
// fields of the form. Or it might be a subclass of that form to allow
// for some differences in behavior.

function a_sub_crud_create_form_tag($form)
{
  list($type, $subtype, $displayData) = _a_sub_crud_form_info($form);
  return a_sub_crud_nonajax_form_tag($form, url_for("@$type" . "_create"), "@$type");
}

function _a_sub_crud_form_info($form)
{
  $type = $form->type;
  $subtype = $form->subtype;
  $displayData = $type . '-' . $subtype;
  return array($type, $subtype, $displayData);
}

function a_sub_crud_form_body($form)
{
  list($type, $subtype, $displayData) = _a_sub_crud_form_info($form);
  ob_start();
  include_stylesheets_for_form($form);
  echo $form;
?>
  <ul class="a-form-row submit">
  	<li><input type="submit" value="Save" class="a-sub-submit"/></li>
  	<li><?php echo link_to_function('Cancel', "$('#$displayData').html($('#$displayData').data('a-form-swap')); $('#$type-form-edit-$subtype').show()", array("class" => "a-sub-cancel")) ?></li>
  </ul>
<?php
  // Do this after the form so that we can do things like disabling stuff that
  // gets created by JS in widgets 
  include_javascripts_for_form($form);
  return ob_get_clean();
}
