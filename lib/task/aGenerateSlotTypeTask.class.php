<?php
/**
 * @package    apostrophePlugin
 * @subpackage    task
 * @author     P'unk Avenue <apostrophe@punkave.com>
 */
class aGenerateSlotTypeTask extends sfBaseTask
{

  /**
   * DOCUMENT ME
   */
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', null),
      new sfCommandOption('plugin', null, sfCommandOption::PARAMETER_REQUIRED, 'The plugin name'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'The slot type name')
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'generate-slot-type';
    $this->briefDescription = 'generate scaffolding for a new slot type you intend to develop';
    $this->detailedDescription = <<<EOF
The [apostrophe:generate-slot-type|INFO] task generates scaffolding for a new slot type you intend
to develop yourself. This is not the way to install existing types of slots in your application.

This task does the following:

1. Adds a new module for your new slot type, with actions and components classes that subclass the
appropriate Apostrophe base classes and _normalView and _editView partials ready for you to
complete.

2. Adds a new Doctrine model class for your new slot type. This class will extend the
aSlot class via Doctrine column aggregation inheritance. This will append to the
project-level schema.yml file, or to the plugin-level one if you specify --plugin.

3. Creates a stub form class for your slot's edit view. You are responsible for adding
fields and validators to that form clas and deciding how to save the result.

There is more to do before your new slot type is ready to use. You will need to enable the module
in settings.yml and add the new slot type to app_a_slot_types in app.yml, as well as adding it to
the page templates where you wish to employ it. This task is just a convenience to
help you down the road. See the Apostrophe documentation for more information.

For instance, to generate scaffolding for a slot that displays baseball box scores specifically in a single application, you might call the task like this:

  [./symfony apostrophe:generate-slot-type --application=frontend --type=baseball]
  
This creates the slot at the project level.

To scaffold a slot that you will reuse in other projects, you might specify a plugin instead of an application. The slot is added to the plugin, which is created (in the plugins folder) if it does not exist:
  
  [./symfony apostrophe:generate-slot-type --plugin=sfSportsPlugin --type=baseball]  
  
Please do not add 'Slot' to the end of the slot type name unless you really want classes with 'SlotSlot' in their name. Please reserve slot names beginning with 'a' and followed by an uppercase letter
for P'unk Avenue to avoid incompatibility with future releases of Apostrophe (or perhaps discuss your
fantastic plans with us in advance).
EOF;
  }

  /**
   * DOCUMENT ME
   * @param mixed $arguments
   * @param mixed $options
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!($options['application'] || $options['plugin']))
    {
      throw new sfException("You must specify either the --application option or the --plugin option.");
    }
    if ($options['application'] && $options['plugin'])
    {
      throw new sfException('Specify only one of --application and --plugin');
    }
    if (!$options['type'])
    {
      throw new sfException("You must specify the --type option.");
    }
    $type = $options['type'];
    $application = $options['application'];
    $plugin = $options['plugin'];
    if ($application)
    {
      $schema = sfConfig::get('sf_config_dir') . '/doctrine/schema.yml';
    }
    else
    {
      $schema = sfConfig::get('sf_plugins_dir') . "/$plugin/config/doctrine/schema.yml";
    }
    $typeSlot = $type . 'Slot';
    if (class_exists($typeSlot))
    {
      throw new sfException("The class $typeSlot already exists, did you already do this?");
    }
    if ($this->fileContains($schema, $typeSlot))
    {
      throw new sfException("You have already run this task for the type $type");
    }
    $this->ensureAndAppend($schema, <<<EOM

$typeSlot:
  # Doctrine doesn't produce useful forms with column aggregation inheritance anyway,
  # and slots often use serialization into the value column... the Doctrine forms are not
  # of much use here and they clutter the project
  options:
    symfony:
      form:   false
      filter: false

  # columns:
  #
  # You can add columns here. However, if you do not need foreign key relationships it is
  # often easier to store your data in the 'value' column via serialize(). If you do add columns, 
  # their names must be unique across all slots in your project, so use a unique prefix 
  # for your company.
    
  # This is how we are able to retrieve slots of various types with a single query from
  # a single table
  inheritance:
    extends: aSlot
    type: column_aggregation
    keyField: type
    keyValue: '$type'

EOM
    );
    
  if ($application)
  {
    $module = sfConfig::get('sf_root_dir') . "/apps/$application/modules/$typeSlot";
  }
  else
  {
    $module = sfConfig::get('sf_plugins_dir') . "/$plugin/modules/$typeSlot";
  }
  
  $typeSlotActions = $typeSlot . 'Actions';
  // Let's distinguish this better from common autogenerated class names in Doctrine projects
  $typeForm = $type . 'SlotEditForm';
  $this->ensureAndCreate("$module/actions/actions.class.php", '<?' . 'php' . <<<EOM

class $typeSlotActions extends aSlotActions
{
  public function executeEdit(sfRequest \$request)
  {
    \$this->editSetup();

    // Hyphen between slot and form to please our CSS
    \$value = \$this->getRequestParameter('slot-form-' . \$this->id);
    \$this->form = new $typeForm(\$this->id, array());
    \$this->form->bind(\$value);
    if (\$this->form->isValid())
    {
      // Serializes all of the values returned by the form into the 'value' column of the slot.
      // This is only one of many ways to save data in a slot. You can use custom columns,
      // including foreign key relationships (see schema.yml), or save a single text value 
      // directly in 'value'. serialize() and unserialize() are very useful here and much
      // faster than extra columns
      
      \$this->slot->setArrayValue(\$this->form->getValues());
      return \$this->editSave();
    }
    else
    {
      // Makes \$this->form available to the next iteration of the
      // edit view so that validation errors can be seen, if any
      return \$this->editRetry();
    }
  }
}
  
EOM
    );

  if ($application)
  {
    $form = sfConfig::get('sf_root_dir') . "/apps/$application/lib/form/$typeForm.class.php";
  }
  else
  {
    $form = sfConfig::get('sf_plugins_dir') . "/$plugin/lib/form/$typeForm.class.php";
  }
     
  $this->ensureAndCreate($form, '<?' . 'php' . <<<EOM
    
class $typeForm extends BaseForm
{
  // Ensures unique IDs throughout the page
  protected \$id;
  public function __construct(\$id, \$defaults = array(), \$options = array(), \$CSRFSecret = null)
  {
    \$this->id = \$id;
    parent::__construct(\$defaults, \$options, \$CSRFSecret);
  }
  public function configure()
  {
    // ADD YOUR FIELDS HERE
    
    // A simple example: a slot with a single 'text' field with a maximum length of 100 characters
    \$this->setWidgets(array('text' => new sfWidgetFormTextarea()));
    \$this->setValidators(array('text' => new sfValidatorString(array('required' => false, 'max_length' => 100))));
    
    // Ensures unique IDs throughout the page. Hyphen between slot and form to please our CSS
    \$this->widgetSchema->setNameFormat('slot-form-' . \$this->id . '[%s]');
    
    // You don't have to use our form formatter, but it makes things nice
    \$this->widgetSchema->setFormFormatterName('aAdmin');
  }
}

EOM
    );

  $typeSlotComponents = $typeSlot . 'Components';
  $this->ensureAndCreate("$module/actions/components.class.php", '<?' . 'php' . <<<EOM

class $typeSlotComponents extends aSlotComponents
{
  public function executeEditView()
  {
    // Must be at the start of both view components
    \$this->setup();
    
    // Careful, don't clobber a form object provided to us with validation errors
    // from an earlier pass
    if (!isset(\$this->form))
    {
      \$this->form = new $typeForm(\$this->id, \$this->slot->getArrayValue());
    }
  }
  public function executeNormalView()
  {
    \$this->setup();
    \$this->values = \$this->slot->getArrayValue();
  }
}

EOM
    );
    
    $typeSlotNormalView = $module . '/templates/_normalView.php';
    
    $openphp = '<?' . 'php';
    $closephp = '?' . '>';
    $this->ensureAndCreate($typeSlotNormalView, <<<EOM
$openphp use_helper('a') $closephp

$openphp include_partial('a/simpleEditWithVariants', array('pageid' => \$pageid, 'name' => \$name, 'permid' => \$permid, 'slot' => \$slot)) $closephp
$openphp if (isset(\$values['text'])): $closephp
  <h4>$openphp echo htmlspecialchars(\$values['text']) $closephp</h4>
$openphp endif $closephp

EOM
    );

    $typeSlotEditView = $module . '/templates/_editView.php';
    
    $this->ensureAndCreate($typeSlotEditView, <<<EOM
$openphp // Just echo the form. You might want to render the form fields differently $closephp
$openphp echo \$form $closephp
EOM
    );
    
    echo("\n\nDone!\n\n");
    echo("WHAT COMES NEXT\n\n");
    echo("1. Generate your Doctrine classes:\n");
    echo("./symfony doctrine:build --all-classes\n\n");
    echo("2. Enable your new slot type's module, $typeSlot, in settings.yml\n\n");
    echo("3. Add your new slot type name, $type, to the a_slot_types list in app.yml\n\n");
    echo("4. ./symfony cc\n\n");
    echo("5. Add your slot type to at least one page template\n\n");
    echo("6. If you generated a new plugin, enable your plugin in
config/ProjectConfiguration.class.php\n\n");
    echo("7. Try out your slot!\n\n");
    echo("8. Edit $schema and add custom columns and relations, if any. It is\n");
    echo("often best to serialize data in the 'value' column as shown in the\n");
    echo("generated sample code.\n\n");
    echo("9. Start customizing the _normalView and _editView partials as well as\n");
    echo("the actions and components classes of your slot type.\n\n");
    echo("Also consider adding a getSearchText() method to the $typeSlot class for\n");
    echo("search indexing purposes. Simply return entity-escaped text.\n\n");
    echo("Have fun!\n");
  }

  /**
   * DOCUMENT ME
   * @param mixed $path
   * @param mixed $text
   */
  public function ensureAndAppend($path, $text)
  {
    $this->ensureDirFor($path);
    $out = fopen($path, 'a');
    fwrite($out, $text);
    fclose($out);
  }

  /**
   * DOCUMENT ME
   * @param mixed $path
   */
  public function ensureDirFor($path)
  {
    $dir = dirname($path);
    if (!file_exists($dir))
    {
      // Use the handy recursive flag instead of shelling out to mkdir -p.
      // Note that 0777 is the default, I'm doing nothing more radical here than
      // plain old mkdir($dir)
      if (!mkdir($dir, 0777, true))
      {
        throw new sfException("Unable to create $dir in which to create $path\n");
      }
    }
  }

  /**
   * DOCUMENT ME
   * @param mixed $path
   * @param mixed $text
   */
  public function ensureAndCreate($path, $text)
  {
    $this->ensureDirFor($path);
    file_put_contents($path, $text);
  }

  /**
   * DOCUMENT ME
   * @param mixed $file
   * @param mixed $str
   * @return mixed
   */
  public function fileContains($file, $str)
  {
    if (!file_exists($file))
    {
      return false;
    }
    return (strstr(file_get_contents($file), $str) !== false);
  }
}
