<?php

class FileReader
{
  private $debugDir;
  
  public function __construct()
  {
    $this->debugDir = __DIR__ . '/../debug/';
  }
  
  public function listFiles()
  {
    $files = [];
    if (is_dir($this->debugDir)) {
      foreach (scandir($this->debugDir) as $file) {
        if ($file != '.' && $file != '..') {
          $files[] = [
            'name' => $file,
            'path' => 'debug/' . $file,
            'content' => $this->readFile($this->debugDir . $file)
          ];
        }
      }
    }
    return $files;
  }
  
  private function readFile($path)
  {
    if (!file_exists($path)) {
      return null;
    }
    
    $content = file_get_contents($path);
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    
    if ($ext === 'json') {
      $decoded = json_decode($content, true);
      if ($decoded !== null) {
        return $decoded;
      }
    }
    
    return $content;
  }
}

?>
