Create a single page web application for a customizable dashboard using PHP, Bootstrap 5.3 and jQuery UI. The primary purpose of the dashboard is to display data from files in a directory folder in widgets. Add some sample data in /debug that is loaded in the app. But there may also be special widget types, samples: chart, table, calendar, weather

Requirements

- The dashboard should have a responsive grid layout with x columns, scrollable horizontally (use 3 for now but provide buttons to add and remove cols)
- Each column should contain one or more draggable and resizable panels (groups)
- Each panel may have an optional editable headline
- Panels should contain one or more widgets and use col-6 within a single row
- For file widgets the file name is thw widget content
- Implement the following features

   - Drag and drop functionality for widgets and panels
   - Editable panel headlines
   - Removable panels

- Store the dashboard configuration in a YAML file, including

   - Layout structure (columns, panels, widgets)
   - Widget source file, or information for special widget types
   - Panel headlines

Please outline the structure of the web application, including HTML, CSS, and JavaScript components, as well as the YAML configuration file format and PHP code for saving position changes via ajax.

Indent all codes with 2 spaces and put the { on the next line.
