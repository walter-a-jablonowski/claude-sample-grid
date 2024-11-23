<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grid</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
  
  <!-- Load jQuery first -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  
  <!-- Then load other dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <!-- Finally load Bootstrap and our custom JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="controller.js"></script>
</head>
<body>
  <div class="container-fluid p-3">
    <div class="d-flex justify-content-between mb-3">
      <h1>Grid view</h1>
      <div>
        <button id="addPanel" class="btn btn-info">Add Panel</button>
        <button id="saveConfig" class="btn btn-success">Save Configuration</button>
      </div>
    </div>

    <?php
    require_once 'lib/file_reader.php';
    require_once 'lib/widget_renderer.php';
    require_once 'vendor/autoload.php';
    
    use Symfony\Component\Yaml\Yaml;
    
    $configFile = __DIR__ . '/debug/dashboard.yml';
    if (!file_exists($configFile)) {
        die("No configuration file found!");
    }

    $config = Yaml::parseFile($configFile);
    $renderer = new WidgetRenderer();
    ?>

    <div class="row" id="dashboard">
      <?php foreach ($config['columns'] as $column): ?>
        <div class="col-12 col-md-6 col-lg-4 mb-4">
          <?php foreach ($column['panels'] as $panel): ?>
            <div class="card mb-3 panel">
              <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h5 class="card-title m-0"><?php echo htmlspecialchars($panel['title']); ?></h5>
                <button class="btn btn-sm btn-danger remove-panel">&times;</button>
              </div>
              <div class="card-body p-2">
                <?php foreach ($panel['widgets'] as $widget): ?>
                  <div class="widget mb-3" data-type="<?php echo htmlspecialchars($widget['type']); ?>" data-source="<?php echo htmlspecialchars($widget['source']); ?>">
                    <div class="widget-handle position-absolute top-0 end-0 p-1 text-muted">⋮</div>
                    <?php echo $renderer->render($widget); ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
