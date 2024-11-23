$(document).ready(function() {
  let columnCount = 3;
  
  // Initialize dashboard
  initDashboard();
  
  // Add column button
  $('#addColumn').click(function() {
    if (columnCount < 6) {
      addColumn();
      columnCount++;
      saveDashboardConfig();
    }
  });
  
  // Remove column button
  $('#removeColumn').click(function() {
    if (columnCount > 1) {
      const lastColumn = $('.dashboard-column').last();
      if (lastColumn.find('.panel').length === 0 || confirm('Last column contains panels. Are you sure you want to remove it?')) {
        lastColumn.remove();
        columnCount--;
        saveDashboardConfig();
      }
    }
  });
  
  // Remove panel button
  $(document).on('click', '.remove-panel', function() {
    $(this).closest('.panel').remove();
    saveDashboardConfig();
  });
  
  // Make panel titles editable
  $(document).on('blur', '.panel-title', function() {
    saveDashboardConfig();
  });
  
  function initDashboard() {
    // Load initial configuration
    $.getJSON('ajax/load_config.php', function(config) {
      if (!config) {
        // Configuration is loaded from PHP initially
        initDragAndDrop();
      } else {
        loadDashboardConfig(config);
      }
      initWidgets();
    });
  }
  
  function loadDashboardConfig(config) {
    $('#dashboard').empty();
    
    config.columns.forEach(function(column) {
      const columnDiv = addColumn();
      
      column.panels.forEach(function(panel) {
        const panelDiv = $('<div>').addClass('panel');
        
        // Create panel header
        const headerDiv = $('<div>').addClass('panel-header');
        const title = $('<h3>').addClass('panel-title').attr('contenteditable', 'true').text(panel.title);
        const removeBtn = $('<button>').addClass('btn btn-sm btn-danger remove-panel').html('&times;');
        headerDiv.append(title, removeBtn);
        
        // Create panel content
        const contentDiv = $('<div>').addClass('panel-content');
        
        panel.widgets.forEach(function(widget) {
          const widgetDiv = $('<div>')
            .addClass('widget')
            .attr('data-type', widget.type)
            .attr('data-source', widget.source);
          
          const handleDiv = $('<div>').addClass('widget-handle').text('⋮');
          widgetDiv.append(handleDiv);
          
          if (widget.type === 'file') {
            // Load file content
            $.get('ajax/get_files.php', function(files) {
              const file = files.find(f => f.path === widget.source);
              if (file) {
                const title = $('<h4>').text(file.name);
                const content = $('<pre>').text(
                  typeof file.content === 'object' 
                    ? JSON.stringify(file.content, null, 2) 
                    : file.content
                );
                widgetDiv.append(title, content);
              }
            });
          }
          
          contentDiv.append(widgetDiv);
        });
        
        panelDiv.append(headerDiv, contentDiv);
        columnDiv.append(panelDiv);
      });
    });
    
    initDragAndDrop();
  }
  
  function addColumn() {
    const column = $('<div>').addClass('dashboard-column');
    $('#dashboard').append(column);
    
    // Make column droppable for panels
    column.droppable({
      accept: '.panel',
      tolerance: 'pointer',
      drop: function(event, ui) {
        const panel = ui.draggable;
        panel.appendTo($(this));
        saveDashboardConfig();
      }
    });
    
    return column;
  }
  
  function initDragAndDrop() {
    // Make panels draggable
    $('.panel').draggable({
      handle: '.panel-header',
      connectToSortable: '.dashboard-column',
      placeholder: 'panel-placeholder',
      start: function(event, ui) {
        ui.helper.width($(this).width());
      }
    });
    
    // Make widgets draggable within panels
    $('.widget').draggable({
      handle: '.widget-handle',
      connectToSortable: '.panel-content',
      placeholder: 'widget-placeholder'
    });
    
    // Make panels sortable within columns
    $('.dashboard-column').sortable({
      handle: '.panel-header',
      connectWith: '.dashboard-column',
      placeholder: 'panel-placeholder',
      stop: function() {
        saveDashboardConfig();
      }
    });
    
    // Make widgets sortable within panels
    $('.panel-content').sortable({
      handle: '.widget-handle',
      connectWith: '.panel-content',
      placeholder: 'widget-placeholder',
      stop: function() {
        saveDashboardConfig();
      }
    });
  }
  
  function initWidgets() {
    // Initialize Chart.js widgets
    $('.widget[data-type="chart"]').each(function() {
      const chartContainer = $(this).find('canvas');
      if (chartContainer.length) {
        $.getJSON($(this).data('source'), function(data) {
          const ctx = chartContainer[0].getContext('2d');
          new Chart(ctx, {
            type: $(chartContainer).closest('.widget').find('.chart-type').val(),
            data: prepareChartData(data.data),
            options: JSON.parse($(chartContainer).closest('.widget').find('.chart-options').val())
          });
        });
      }
    });

    // Initialize auto-refresh for stats widgets
    $('.widget[data-type="stats"]').each(function() {
      const widget = $(this);
      const refreshInterval = widget.data('refresh-interval') || 60;
      setInterval(() => {
        $.getJSON(widget.data('source'), function(data) {
          updateStatsWidget(widget, data);
        });
      }, refreshInterval * 1000);
    });
  }

  function prepareChartData(data) {
    if (Array.isArray(data)) {
      // For line charts
      if (data[0].hasOwnProperty('month') && data[0].hasOwnProperty('revenue')) {
        return {
          labels: data.map(item => item.month),
          datasets: [{
            label: 'Revenue',
            data: data.map(item => item.revenue),
            borderColor: '#2193b0',
            tension: 0.1
          }]
        };
      }
      // For pie charts
      if (data[0].hasOwnProperty('label') && data[0].hasOwnProperty('value')) {
        return {
          labels: data.map(item => item.label),
          datasets: [{
            data: data.map(item => item.value),
            backgroundColor: [
              '#FF6384',
              '#36A2EB',
              '#FFCE56',
              '#4BC0C0'
            ]
          }]
        };
      }
    }
    return data;
  }

  function updateStatsWidget(widget, data) {
    const container = widget.find('.stats-grid');
    container.empty();
    
    data.data.forEach(stat => {
      container.append(`
        <div class="stat-item">
          <div class="stat-label">${stat.metric}</div>
          <div class="stat-value">${stat.value}</div>
          <div class="stat-trend ${stat.trend}">${stat.trend}</div>
        </div>
      `);
    });
  }

  function saveDashboardConfig() {
    const config = {
      columns: []
    };
    
    $('.dashboard-column').each(function() {
      const column = {
        panels: []
      };
      
      $(this).find('.panel').each(function() {
        const panel = {
          title: $(this).find('.panel-title').text(),
          widgets: []
        };
        
        $(this).find('.widget').each(function() {
          panel.widgets.push({
            type: $(this).data('type'),
            source: $(this).data('source')
          });
        });
        
        column.panels.push(panel);
      });
      
      config.columns.push(column);
    });
    
    $.ajax({
      url: 'ajax/save_config.php',
      method: 'POST',
      data: { config: JSON.stringify(config) },
      success: function(response) {
        console.log('Configuration saved');
      }
    });
  }
});
