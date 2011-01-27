<?php

class csscolorsreportTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'apostrophe';
    $this->name             = 'css-colors-report';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [css-colors-report|INFO] task generates a report of all colors found in 
CSS files in  apostrophePlugin and the web/css folder, sorted by the number 
of rules that invoke them. Hovering each color displays a list of rules and 
the files in which they appear. IMPORTANT: this report is generated as 
css-colors-report.html in the web/ folder of your project.

Call it with:

  [php symfony css-colors-report|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    chdir(sfConfig::get('sf_root_dir'));
    $files = glob('plugins/apostrophePlugin/web/css/*');
    $files = array_merge($files, glob(sfConfig::get('sf_web_dir') . '/css/*.css'));

    foreach ($files as $file)
    {
      $content = file_get_contents($file);
      // Don't let contents confuse the parsing
      $content = preg_replace('|/\*.*?\*/|s', '', $content);
      if (preg_match_all('/[^\{\}]+\{.*?\}/s', $content, $rules))
      {
        foreach ($rules[0] as $rule)
        {
          if (preg_match('/([^\{\}]+)(\{.*?\})/s', $rule, $matches))
          {
            list($dummy, $matching, $styles) = $matches;
            $matching = trim($matching);
            $matching = preg_replace('/\s+/', ' ', $matching);
            $styles = trim($styles);
            if (preg_match_all('/(#[A-Fa-f0-9]+|rgba?\(.*?\))/', $styles, $matches))
            {
              foreach ($matches[0] as $color)
              {
                $this->addColor($color, '<b>' . preg_replace('/^.*\//', '', $file) . '</b>: ' . $matching);
              }
            }
          }
        }
      }
    }
    
    arsort($this->colors);

    echo("Found a total of " . count($this->colors) . " colors\n");
    echo("Writing css-colors-report.html to the web folder.\n");
    ob_start();

    echo(<<<EOM
<style>
div.colorBar { font-size: 20px; float: left; width: 300px; height: 24px; overflow: hidden; vertical-align: center; border: 4px solid white }
div.colorBar .usages { background-color: white }
div:hover.colorBar { height: auto; overflow: auto }
</style>
EOM
    );

    foreach ($this->colors as $color => $matching)
    {
      echo("<div style='background-color: $color' class='colorBar'>" . count($matching) . ": $color<div class='usages'>$color<br />" . implode("<br />\n", $matching) . "</div></div>\n");
    }

    file_put_contents(sfConfig::get('sf_web_dir') . '/css-colors-report.html', ob_get_clean());
    echo("Done\n");
  }
  
  function addColor($color, $matching)
  {
    if (strlen($color) < 7)
    {
      $color = $color . substr($color, 1);
    }
    $color = strtolower($color);
    $this->colors[$color][] = $matching;
  }
}
