<?php // Yes, this is template code, but we use regular PHP syntax because we are building a sentence and the introduction of ?>
<?php // newlines wrecks the punctuation. ?>
<?php 
$clauses = array();
if (aMediaTools::getAttribute('aspect-width') && aMediaTools::getAttribute('aspect-height'))
{
  $clauses[] = "a " . aMediaTools::getAttribute('aspect-width') . 'x' . aMediaTools::getAttribute('aspect-height') . " aspect ratio";
}
if (aMediaTools::getAttribute('minimum-width'))
{
  $clauses[] = "a minimum width of " . aMediaTools::getAttribute('minimum-width') . " pixels";
}
if (aMediaTools::getAttribute('minimum-height'))
{
  $clauses[] = "a minimum height of " . aMediaTools::getAttribute('minimum-height') . " pixels";
}
if (aMediaTools::getAttribute('width'))
{
  $clauses[] = "a width of exactly " . aMediaTools::getAttribute('width') . " pixels";
}
if (aMediaTools::getAttribute('height'))
{
  $clauses[] = "a height of exactly " . aMediaTools::getAttribute('height') . " pixels";
}
if (aMediaTools::getAttribute('type'))
{
  $type = aMediaTools::getAttribute('type') . "s";
} 
else
{
  $type = "items";
}
if (count($clauses))
{
  echo("<h3>Displaying only $type with ");
  if (count($clauses) > 1)
  {
    for ($i = 0; ($i < count($clauses) - 1); $i++)
    {
      if ($i > 0)
      {
        echo(", ");
      }
      echo($clauses[$i]);
    }
    echo(" and " . $clauses[count($clauses) - 1]);
  }
  else
  {
    echo($clauses[0]);
  }
  echo(".</h3>\n");
}
