<?php

use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/../vendor/autoload.php';

class DashboardConfig
{
  private $configFile;
  
  public function __construct()
  {
    $this->configFile = __DIR__ . '/../config/dashboard.yml';
  }
  
  public function load()
  {
    if( !file_exists($this->configFile)) {
      return null;
    }
    
    return Yaml::parseFile($this->configFile);
  }
  
  public function save($config)
  {
    $yamlContent = Yaml::dump($config, 4, 2);
    
    if (!is_dir(dirname($this->configFile))) {
      mkdir(dirname($this->configFile), 0777, true);
    }
    
    file_put_contents($this->configFile, $yamlContent);
    return true;
  }
}

?>
