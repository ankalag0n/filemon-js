## Filemon JS - The JavaScript File Manager

version 1.0.0

-------------------------------------------------------------------
                         INSTALATION

1. Copy files to your project and prepare server side scripts (see
   the examples and online documentation for more details)
2. Include all required files

      1. CSS files
          * ExtJS CSS style   : js/extjs/resources/css/ext-all.css
          * Filemon CSS style : js/style.css
      
      2. JS files
          * ExtJS adapter                   : js/extjs/adapter/ext-base.js
          * ExtJS library                   : js/extjs/ext-all.js
          * (optional) ExtJS language file  : js/extjs/locale/ext-lang-[your lang code].js
          * ExtJS plugins                   : js/extjs/plugins/data-view-plugins.js and js/extjs/plugins/BrowseButton.js
          * (optional) Filemon language file: js/locale/filemon-locale-[your lang code].js
          * Filemon JS library              : js/Filemon.min.js
    
3. Write JavaScript code that will display Filemon JS widgets (see the examples and online documentation for more details):

    ```javascript
    <script type="text/javascript">
    // All Filemon JS widgets can be created only after entire document was loaded
    Ext.onReady(function () {
        // Create Filemon JS panel
        new Filemon.Panel({
            api : {
                defaultLink : '/link/to/server/side/scripts/:action'
            },
            width     : 800, // width and height of the panel (in pixels)
            height    : 500,
            iconsPath : 'js/images/', // path to Filemon JS image folder
            renderTo  : 'filemon-div' // id of the div where Filemon JS will be rendered
        });

        // or display Filemon JS dialog
        var win = new Filemon.Window({
            api : {
                defaultLink : '/link/to/server/side/scripts/:action'
            },
            width     : 800,
            height    : 500,
            iconsPath : 'js/images/',
            listeners : {
                // This function will be called when user will select some files
                filesselected : function (win, files) {
                    win.close();
                    
                    alert("Selected files:\n" + files.join("\n"));
                }
            }
        });
          
        win.show(); // this will display the window
    });
    </script>
    <div id="filemon-div"></div> <!-- dialog to render Filemon JS Panel -->
    ```


-------------------------------------------------------------------
                           LICENCE

Filemon JS is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 3 or
GNU Lesser General Public License version 3 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License or GNU Lesser General Public License 
for more details.

You should have received a copy of the GNU General Public License and
GNU Lesser General Public License along with this program; if not, 
write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, 
Boston, MA 02110-1301 USA