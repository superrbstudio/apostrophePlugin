/**
 * 
 * @param widget_id
 * @param id
 */
function aWidgetFormMediaSelector(widget_id, id)
{
  jQuery('#' + widget_id).val(id);
  TopUp.close();
}

/**
 * 
 * @param url
 */
function aWidgetFormMediaResetSelecting(url)
{

  jQuery.get(url);
}