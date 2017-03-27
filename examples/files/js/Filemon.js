/**
 * Filemon JS - The JavaScript File Manager
 * 
 * This file contains all JavaScript code for Filemon JS
 * 
 * Filemon JS : The JavaScript File Manager <http://mobilegb.eu/filemon>
 * Copyright (c) 2011, Grzegorz Bednarz
 * 
 * Author: Grzegorz Bednarz
 * 
 * Filemon JS is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 or
 * GNU Lesser General Public License version 3 as published by
 * the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License or GNU Lesser General Public License 
 * for more details.
 *
 * You should have received a copy of the GNU General Public License and
 * GNU Lesser General Public License along with this program; if not, 
 * write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, 
 * Boston, MA 02110-1301 USA
 * 
 * Redistributions of files must retain the above copyright notice.
 * 
 * @author Grzegorz Bednarz
 * @copyright Copyright (c) 2011, Grzegorz Bednarz
 * @link http://mobilegb.eu/filemon
 * @version 1.0.0
 * @package Filemon
 */

Ext.namespace('Filemon', 'Filemon.lang', 'Filemon.util');

/**
 * Translation table. Modified by language files.
 */
if (!Filemon.lang.translations) Filemon.lang.translations = {};

/**
 * Translates the phrase.
 * 
 * @param phrase Phrase to translate
 * @return String
 */
Filemon.lang.translate = function (phrase) {
    if (Filemon.lang.translations[phrase]) {
        return Filemon.lang.translations[phrase];
    }
    
    return phrase;
};

/**
 * Retrives API link for action.
 * 
 * @param api API object with actions list
 * @param action name of the action
 * @return String
 */
Filemon.getApi = function (api, action) {
    if (api[action]) {
        return api[action];
    }
    
    return api.defaultLink.replace(':action', action);
}

/**
 * Main panel that displays files.
 * 
 * @param cfg configuration object
 */
Filemon.Panel = function (cfg) {
    cfg = cfg || {};
    
    // Default config params
    Ext.applyIf(cfg, {
        multiSelect    : true, // Is multiple file selection allowed
        allowDirSelect : true, // Is directory selection allowed
        allowPreview   : true, // Does preview after double click is allowed
        curDir         : '/',  // Start directory
        filter         : '',   // File extensions filter,
        height         : 300,  // Panel height
        iconsPath      : 'filemon/images/' // Path to icons
    });
    
    if (!cfg.api) {
        throw 'Please provide API configuration';
    }
    
    // Ext.Panel config - not to be changed
    Ext.apply(cfg, {
        layout : 'border'
    });
    
    Filemon.Panel.superclass.constructor.call(this, cfg);
};

Ext.extend(Filemon.Panel, Ext.Panel, {
    /**
     * Components initialization
     */
    initComponent : function () {
        var thisPanel = this;
        
        this.addEvents('filedblclicked');
        
        // direcotry tree
        this.dirTree = new Ext.tree.TreePanel({
            title       : Filemon.lang.translate('Directory tree'),
            ctCls       : 'filemon-dir-tree',
            animate     : true,
            containerScroll : true,
            enableDD    : true,
            ddGroup     : 'filemonDD',
            autoScroll  : true,
            rootVisible : true,
            loader      : new Ext.tree.TreeLoader({
                dataUrl : Filemon.getApi(this.api, 'list-folders')
            }),
            root        : new Ext.tree.AsyncTreeNode({
                text      : '/',
                allowDrag : false,
                id        : '/',
                cls       : 'dir-tree-folder'
            }),
            region      : 'west',
            collapsible : true,
            split       : true,
            minWidth    : 130,
            width       : 160,
            dropConfig  : {
                ddGroup    : 'filemonDD',
                appendOnly : true,
                onNodeDrop : function (nodeData, source, e, data) {
                    var dragFiles = [];
                    var dropDir   = nodeData.node.attributes.id;
                    var reloadDirTree = false;
                    
                    if (data.filesDrag) {
                        var records = thisPanel.filesView.getRecords(data.nodes);
                        
                        for (var i in records) {
                            if (!records[i].data) {
                                continue;
                            }
                            
                            if (Filemon.util.checkMovePath(records[i].data.path, dropDir)) {
                                if (records[i].data.dir) {
                                    reloadDirTree = true;
                                }
                                dragFiles.push(records[i].data.path);
                            }
                        }
                    } else {
                        if (Filemon.util.checkMovePath(data.node.attributes.id, dropDir)) {
                            reloadDirTree = true;
                            dragFiles.push(data.node.attributes.id);
                        }
                    }
                    
                    if (dragFiles.length == 0) {
                        return false;
                    }
                    
                    thisPanel.moveFiles(dragFiles, dropDir, reloadDirTree);

                    return true;
                }
            }
        });
        
        // click action for the directory tree elements
        this.dirTree.on('click', function (elem) {
            thisPanel.changeDir(elem.attributes.id);
        });
        
        // files list view
        this.filesView = new Ext.DataView({
            store         : new Ext.data.JsonStore({
                url        : Filemon.getApi(this.api, 'list-files'),
                fields     : [
                    'icon', 'name', 'dir', 'path', 'writable', 'size', 'width', 'height',
                    {name: 'shortName', mapping: 'name', convert: Filemon.util.cropFileName}
                ],
                baseParams : {filter : this.filter},
                autoLoad   : true,
                listeners : {
                    load : function () {
                        // fixing bug in Ext.DataView.DragSelector
                        // after store was reloaded DragSelector was not displaying selection frame
                        thisPanel.filesView.removeListener('render', thisPanel.filesView.plugins.onRender);
                        thisPanel.filesView.removeListener('containerclick', thisPanel.filesView.plugins.cancelClick);
                        
                        thisPanel.filesView.plugins = new Ext.DataView.DragSelector({dragSafe:true});
                        thisPanel.filesView.plugins.init(thisPanel.filesView);
                        thisPanel.filesView.plugins.onRender(thisPanel.filesView);
                    }
                }
            }),
            tpl           : new Ext.XTemplate('<tpl for=".">',
                                '<div class="filemon-file-wrap<tpl if="dir"> filemon-directory</tpl>">',
                                '<div class="filemon-thumb">',
                                '<tpl if="icon == \'custom\'">',
                                '<img src="'+Filemon.getApi(this.api, 'print-thumbnail')+(Filemon.getApi(this.api, 'print-thumbnail').indexOf('?') != -1 ? '&' : '?')+
                                    'file={path}" class="filemon-thumb-img" alt="{name}" title="{name}" />',
                                '</tpl>',
                                '<tpl if="icon != \'custom\'">',
                                '<img src="'+this.iconsPath+'{icon}.png" class="filemon-thumb-img" alt="{name}" title="{name}" />',
                                '</tpl>',
                                '<tpl if="!writable"><img src="'+this.iconsPath+'lock.png" alt="lock" style="position: absolute; top: 5px; left: 5px;" /></tpl>',
                                '</div>',
                                '<span class="filemon-filename">{shortName}</span>',
                                '</div>',
                                '</tpl>'
                            ),
            itemSelector  : '.filemon-file-wrap',
            overClass     : 'filemon-file-over',
            selectedClass : 'filemon-file-selected',
            multiSelect   : this.multiSelect,
            singleSelect  : !this.multiSelect,
            listeners     : {
                render : function () {
                    new Filemon.FileDragZone(this, {containerScroll : true, ddGroup: 'filemonDD'});
                    new Filemon.FileDropZone(thisPanel, {ddGroup: 'filemonDD'});
                }
            },
            plugins       : new Ext.DataView.DragSelector({dragSafe:true})
        });
        
        // double click on file or folder in mail view
        this.filesView.on('dblclick', function (dataView, index) {
            var item = dataView.store.getAt(index);

            if (item.data.dir) {
                thisPanel.changeDir(item.data.path);
            } else {
                if (thisPanel.allowPreview) {
                    var dotPos = item.data.name.lastIndexOf('.');
                    if (dotPos > 0) {
                        var ext = item.data.name.substring(dotPos + 1).toLowerCase();
                        
                        if (ext == 'jpg' || ext == 'jpeg' || ext == 'png' || ext == 'gif') {
                            thisPanel.showImagePreview(item.data);
                        } else if (ext == 'txt' || ext == 'css' || ext == 'html' || ext == 'ini') {
                            thisPanel.showTextPreview(item.data);
                        }
                    }
                }

                thisPanel.fireEvent('filedblclicked', item.data);
            }
        });
        
        // context menu displayed after clicking right mouse button on a file
        this.rightClickMenu = new Ext.menu.Menu({
            items : [
                {
                    iconCls  : 'filemon-menu-rename',
                    text     : Filemon.lang.translate('Rename'),
                    handler  : function () {
                        if (thisPanel.filesView.getSelectionCount() == 0) {
                            return;
                        }
                        
                        var file = thisPanel.filesView.getSelectedRecords()[0];
                        
                        thisPanel.showFileNameWindow(file.data.name, 'Enter a new name for file / directory', function (value, win) {
                            win.loadMask.show();
                                    
                            Ext.Ajax.request({
                                url    : Filemon.getApi(thisPanel.api, 'rename-file'),
                                params : {
                                    newName : value,
                                    oldName : file.data.path
                                },
                                success : function (response) {
                                    win.loadMask.hide();

                                    var data = Ext.decode(response.responseText);

                                    if (data.success) {
                                        thisPanel.filesView.store.reload();
                                        if (file.data.dir) {
                                            thisPanel.dirTree.root.reload();
                                        }
                                        
                                        win.hide();
                                    } else {
                                        Filemon.util.error('Unable to rename file', data.errorMsg);
                                    }
                                },
                                failure : function (response) {
                                    win.loadMask.hide();

                                    Filemon.util.connectionError();
                                }
                            });
                        });
                    }
                },
                {
                    iconCls  : 'filemon-menu-remove',
                    text     : Filemon.lang.translate('Delete'),
                    handler  : function () {
                        if (thisPanel.filesView.getSelectionCount() == 0) {
                            return;
                        }
                        
                        var msg;
                        if (thisPanel.filesView.getSelectionCount() == 1) {
                            msg = String.format(Filemon.lang.translate('Delete {0}?'), thisPanel.filesView.getSelectedRecords()[0].data.name);
                        } else {
                            msg = Filemon.lang.translate('Delete selected files?');
                        }
                        
                        Ext.MessageBox.confirm(Filemon.lang.translate('Are you sure?'), msg, function (btn) {
                            if (btn == 'yes') {
                                var filesToDelete = [];
                                var selectedFiles = thisPanel.filesView.getSelectedRecords();
                                
                                var reloadDirTree = false;
                                
                                for (var i in selectedFiles) {
                                    if (!selectedFiles[i].data) {
                                        continue;
                                    }
                                    
                                    if (selectedFiles[i].data.dir) {
                                        reloadDirTree = true;
                                    }
                                    
                                    filesToDelete.push(selectedFiles[i].data.path);
                                }
                                
                                thisPanel.mask.show();
                                Ext.Ajax.request({
                                    url    : Filemon.getApi(thisPanel.api, 'delete'),
                                    params : {
                                        files : Ext.encode(filesToDelete)
                                    },
                                    success : function (response) {
                                        thisPanel.mask.hide();
                                        
                                        var data = Ext.decode(response.responseText);

                                        if (data.success) {
                                            thisPanel.filesView.store.reload();
                                            
                                            if (reloadDirTree) {
                                                thisPanel.dirTree.root.reload();
                                            }
                                        } else {
                                            // displaying list of files that were not deleted
                                            var errorMsg = '<ul class="filemon-error-files-list">';
                                            for (var i in data.errorFiles) {
                                                if (typeof data.errorFiles[i] == 'string') {
                                                    errorMsg += '<li>' + data.errorFiles[i] + '</li>';
                                                }
                                            }
                                            errorMsg += '</ul>';
                                            
                                            // Window with error message
                                            var win = new Ext.Window({
                                                layout      : 'anchor',
                                                bodyStyle   : 'padding:5px;',
                                                iconCls     : 'filemon-win-error',
                                                width       : 450,
                                                height      : 230,
                                                title       : Filemon.lang.translate('Failed to delete files'),
                                                modal       : true,
                                                items       : [
                                                    {
                                                        bodyStyle : 'background-color: transparent;',
                                                        html      : '<div class="x-window-dlg"><div class="ext-mb-icon ext-mb-error"></div><div class="ext-mb-content">' + 
                                                            '<div class="ext-mb-text" style="text-align: left;">' +
                                                            Filemon.lang.translate('Below is a list of files which could not be removed:') +
                                                            '</div></div></div>',
                                                        height    : 50,
                                                        border    : false
                                                    },
                                                    {
                                                        anchor     : '-, -50',
                                                        autoScroll : true,
                                                        bodyStyle  : 'text-align: left; padding: 5px;',
                                                        html       : errorMsg
                                                    }
                                                ]
                                            });
                                            
                                            win.show();
                                            
                                            // because some files might have been deleted successfully we have to reload our data
                                            thisPanel.filesView.store.reload();
                                            
                                            if (reloadDirTree) {
                                                thisPanel.dirTree.root.reload();
                                            }
                                        }
                                    },
                                    failure : function (response) {
                                        thisPanel.mask.hide();
                                        
                                        Filemon.util.connectionError();
                                    }
                                });
                            }
                        });
                    }
                },
                { 
                    text     : Filemon.lang.translate('Properties'),
                    iconCls  : 'filemon-menu-properties',
                    handler  : function () {
                        if (thisPanel.filesView.getSelectionCount() == 0) {
                            return;
                        }
                        
                        var selectedFiles = thisPanel.filesView.getSelectedRecords();
                        
                        var files = [];
                        
                        for (var i in selectedFiles) {
                            if (!selectedFiles[i].data) {
                                continue;
                            }

                            files.push(selectedFiles[i].data.path);
                        }
                        
                        thisPanel.mask.show();
                        Ext.Ajax.request({
                            url    : Filemon.getApi(thisPanel.api, 'properties'),
                            params : {
                                files : Ext.encode(files)
                            },
                            success : function (response) {
                                thisPanel.mask.hide();
                                
                                var responseData = Ext.decode(response.responseText);
                                
                                var names = {
                                    size      : Filemon.lang.translate('Size'),
                                    mtime     : Filemon.lang.translate('Modification date'),
                                    ctime     : Filemon.lang.translate('Creation date'),
                                    width     : Filemon.lang.translate('Width'),
                                    height    : Filemon.lang.translate('Height'),
                                    fileCount : Filemon.lang.translate('Number of files'),
                                    dirCount  : Filemon.lang.translate('Number of directories')
                                };

                                var data = [];
                                for (var i in responseData) {
                                    if (i != 'icon' && i != 'type' && i != 'filename') {
                                        if (i == 'size') {
                                            data.push( [ names[i], Ext.util.Format.fileSize(responseData[i]) ] );
                                        } else {
                                            data.push( [ names[i], responseData[i] ] );
                                        }
                                    }
                                }
                                
                                var store = new Ext.data.Store({
                                    'data' : data,
                                    reader : new Ext.data.ArrayReader({}, [
                                        'name',
                                        'value'
                                    ])
                                });
                                
                                var win = new Ext.Window({
                                    title : Filemon.lang.translate('Properties'),
                                    iconCls : 'filemon-win-properties',
                                    resizable : false,
                                    items : [
                                        {
                                            bodyStyle : 'background-color: transparent; text-align: center;',
                                            border    : false,
                                            html      : '<div class="filemon-thumb"><img src="' + 
                                                ((responseData.icon == 'custom') ? 
                                                Filemon.getApi(thisPanel.api, 'print-thumbnail') + (Filemon.getApi(thisPanel.api, 'print-thumbnail').indexOf('?') != -1 ? '&' : '?') + 
                                                'file='+thisPanel.curDir + responseData.filename : 
                                                (thisPanel.iconsPath + responseData.icon + '.png')) + 
                                                '" class="filemon-thumb-img" /></div>'
                                        },
                                        {
                                            bodyStyle : 'background-color: transparent; text-align: center;',
                                            border    : false,
                                            html      : responseData.filename != undefined ? responseData.filename : '&nbsp;'
                                        },
                                        new Ext.grid.GridPanel({
                                            'store' : store,
                                            autoHeight : true,
                                            width   : 250,
                                            columns : [
                                                {header : '', dataIndex: 'name', width: 125},
                                                {header : '', dataIndex: 'value', width: 125}
                                            ]
                                        })
                                    ]
                                });

                                win.show();
                            },
                            failure : function (response) {
                                thisPanel.mask.hide();
                                
                                Filemon.util.connectionError();
                            }
                        });
                    }
                }
            ]
        });
        
        this.filesView.on('contextmenu', function (view, index, node, event) {
            if (!view.isSelected(node)) {
                view.select(node);
            }
            
            thisPanel.rightClickMenu.showAt(event.getXY());

            event.stopEvent();
            
            return false;
        });
        
        this.filesView.on('click', function (view, index, node, event) {
            if (event.type == 'mousedown' && event.button == 2) {
                // click was made by the right mouse button
                view.fireEvent('contextmenu', view, index, node, event);
            }
        });
        
        
        
        // Files list panel
        this.filesPanel = new Ext.Panel({
            title      : this.curDir,
            region     : 'center',
            layout     : 'fit',
            autoScroll : true,
            items      : [
                this.filesView
            ],
            tbar       : [
                {   // button: move up in the directory structure
                    iconCls : 'filemon-tb-up',
                    text    : Filemon.lang.translate('Up'),
                    handler : function () {
                        var curDir = thisPanel.curDir;

                        var newDir = curDir.substring(0, curDir.lastIndexOf('/'));

                        if (newDir == '') {
                            newDir = '/';
                        }

                        thisPanel.changeDir(newDir);
                    }
                },
                '|',
                {   // button: create new file or folder
                    iconCls : 'filemon-tb-new-file',
                    text    : Filemon.lang.translate('New'),
                    menu    : [
                        {   // button: new folder
                            iconCls : 'filemon-tb-new-folder',
                            text    : Filemon.lang.translate('New folder'),
                            handler : function () {
                                thisPanel.showFileNameWindow('', 'Enter a name for the new folder', function (name, win) {
                                    win.loadMask.show();
                                    
                                    Ext.Ajax.request({
                                        url    : Filemon.getApi(thisPanel.api, 'create-dir'),
                                        params : {
                                            dirName : name,
                                            path    : thisPanel.curDir
                                        },
                                        success : function (response) {
                                            win.loadMask.hide();
                                            
                                            var data = Ext.decode(response.responseText);
                                            
                                            if (data.success) {
                                                thisPanel.filesView.store.reload();
                                                thisPanel.dirTree.root.reload();
                                                win.hide();
                                            } else {
                                                Filemon.util.error('Unable to create directory', data.errorMsg);
                                            }
                                        },
                                        failure : function (response) {
                                            win.loadMask.hide();
                                            
                                            Filemon.util.connectionError();
                                        }
                                    });
                                });
                            }
                        },
                        {   // button: new file
                            text    : Filemon.lang.translate('New file'),
                            iconCls : 'filemon-tb-new-file',
                            handler : function () {
                                thisPanel.showFileNameWindow(Filemon.lang.translate('unnamed.txt'), 'Enter a name for the new file', function (name, win) {
                                    win.loadMask.show();
                                    
                                    Ext.Ajax.request({
                                        url    : Filemon.getApi(thisPanel.api, 'create-file'),
                                        params : {
                                            fileName : name,
                                            path     : thisPanel.curDir
                                        },
                                        success : function (response) {
                                            win.loadMask.hide();
                                            
                                            var data = Ext.decode(response.responseText);
                                            
                                            if (data.success) {
                                                thisPanel.filesView.store.reload();
                                                win.hide();
                                            } else {
                                                Filemon.util.error('Unable to create file', data.errorMsg);
                                            }
                                        },
                                        failure : function (response) {
                                            win.loadMask.hide();
                                            
                                            Filemon.util.connectionError();
                                        }
                                    });
                                });
                            }
                        }
                    ]
                },
                {   // button: file upload
                    xtype   : 'browsebutton',
                    iconCls : 'filemon-tb-upload',
                    text    : Filemon.lang.translate('Upload file'),
                    inputFileName : 'uploadFile',
                    handler : function (bt) {
                        // file upload
                        var form = document.createElement('form');
                        var file = bt.detachInputFile().dom;

                        form.appendChild(file);
                        form.style.display = 'none';

                        document.body.appendChild(form);
                        
                        var basicForm = new Ext.form.BasicForm(form, {
                            fileUpload : true,
                            url        : Filemon.getApi(thisPanel.api, 'upload-file'),
                            baseParams : {
                                'path' : thisPanel.curDir
                            }
                        });
                        
                        thisPanel.mask.show();
                        
                        basicForm.submit({
                            success : function () {
                                document.body.removeChild(form);
                                
                                // timeout is needed because of strange bug
                                // when refreshing data store immediately after 
                                // file upload new file is not always shown
                                window.setTimeout(function () {
                                    thisPanel.mask.hide();
                                    thisPanel.filesView.store.reload();
                                }, 1000);
                            },
                            failure : function (f, a) {
                                thisPanel.mask.hide();
                                document.body.removeChild(form);
                                if (a.result.errorMsg != '') {
                                    Filemon.util.error('Failed to upload file', a.result.errorMsg);
                                } else {
                                    Filemon.util.error('Error', 'Failed to upload file');
                                }
                            }
                        });
                    }
                },
                {   // button: download selected files
                    iconCls  : 'filemon-tb-download',
                    text     : Filemon.lang.translate('Download files'),
                    handler  : function () {
                        if (thisPanel.filesView.getSelectionCount() == 0) {
                            return;
                        }
                        
                        var filesList; // list of file to download

                        if (thisPanel.filesView.getSelectionCount() == 1) {
                            filesList = {'files' : thisPanel.filesView.getSelectedRecords()[0].data.path};
                        } else {
                            filesList = {};

                            var records = thisPanel.filesView.getSelectedRecords();

                            for (var i = 0; i < records.length; i++) {
                                filesList['files[' + i + ']'] = records[i].data.path;
                            }
                        }
                        
                        var form = document.createElement('form');
                        form.style.display = 'none';
                        document.body.appendChild(form);

                        var basicForm = new Ext.form.BasicForm(form, {
                            fileUpload : true,
                            url        : Filemon.getApi(thisPanel.api, 'download'),
                            baseParams : filesList
                        });

                        basicForm.submit({
                            success : function () {
                                document.body.removeChild(form);
                            },
                            failure : function () {
                                document.body.removeChild(form);
                                Filemon.util.error('Error', 'Failed to download file');
                            }
                        });
                    }
                },
                '|',
                {   // button: refresh list of files and folders in the current directory
                    iconCls : 'filemon-tb-refresh',
                    text    : Filemon.lang.translate('Refresh'),
                    handler : function () {
                        thisPanel.dirTree.root.reload();
                        thisPanel.filesView.store.reload();
                    }
                },
                '->',
                {
                    iconCls : 'filemon-tb-info',
                    handler : function () {
                        var win = new Ext.Window({
                            title     : Filemon.lang.translate('About'),
                            width     : 180,
                            iconCls   : 'filemon-win-info',
                            height    : 260,
                            resizable : false,
                            bodyStyle : 'padding: 5px; text-align: center; background-color: white;',
                            html      : '<img src="'+thisPanel.iconsPath+'icon.png" alt="Filemon JS" />'+
                                '<div style="font-size: large;">Filemon JS</div>'+
                                '<div style="color: grey;">'+Filemon.lang.translate('version')+' '+Filemon.version+'</div>'+
                                '<div style="padding-top: 15px;">'+Filemon.lang.translate('author')+': Grzegorz Bednarz</div>'+
                                '<div><a href="http://mobilegb.eu" target="_blank">http://mobilegb.eu</a></div>'
                        });
                        
                        win.show();
                    }
                }
            ]
        });
        
        this.items = [this.dirTree, this.filesPanel];
        
        Filemon.Panel.superclass.initComponent.call(this);
    },
    
    /**
     * Changes currently displayed directory
     * 
     * @param dir directory to change to
     */
    changeDir : function (dir) {
        this.filesPanel.setTitle(dir);

        this.filesView.store.baseParams.path = dir;
        
        this.curDir = dir;
        this.filesView.store.reload();
    },
    
    /**
     * Shows text files preview (with edit option)
     * 
     * @param data file data
     */
    showTextPreview: function (data) {
        var thisPanel = this;
        
        this.mask.show();
        Ext.Ajax.request({
            url    : Filemon.getApi(this.api, 'print-file'),
            params : {
                file : data.path
            },
            success : function (response) {
                thisPanel.mask.hide();
                
                var win;
                if (data.writable) {
                    var textArea = new Ext.form.TextArea({
                        value : response.responseText
                    });                    
                    
                    win = new Ext.Window({
                        title      : data.name,
                        filePath   : data.path,
                        width      : 700,
                        iconCls    : 'filemon-win-edit',
                        height     : 500,
                        layout     : 'fit',
                        maximizable: true,
                        items      : [
                            textArea
                        ],
                        tbar       : [
                            {
                                text    : Filemon.lang.translate('Save'),
                                iconCls : 'filemon-tb-save',
                                handler : function (bt) {
                                    var mask = new Ext.LoadMask(win.getId(), {removeMask: true});
                                    mask.show();
                                    
                                    Ext.Ajax.request({
                                        url    : Filemon.getApi(thisPanel.api, 'save-file'),
                                        params : {
                                            file    : data.path,
                                            content : textArea.getValue()
                                        },
                                        success : function (response) {
                                            mask.hide();
                                            
                                            var data = Ext.decode(response.responseText);
                                            
                                            if (!data.success) {
                                                Filemon.util.error('Unable to save file', data.errorMsg);
                                            }
                                        },
                                        failure : function (response) {
                                            mask.hide();

                                            Filemon.util.connectionError();
                                        }
                                    });
                                }
                            }
                        ]
                    });

                    win.show();
                } else {
                    win = new Ext.Window({
                        title      : data.name,
                        width      : 700,
                        height     : 500,
                        layout     : 'fit',
                        iconCls    : 'filemon-win-edit',
                        maximizable: true,
                        items      : [
                            {
                                xtype    : 'textarea',
                                value    : response.responseText,
                                readOnly : true
                            }
                        ]
                    });

                    win.show();
                }
            },
            failure : function (response) {
                thisPanel.mask.hide();

                Filemon.util.connectionError();
            }
        });
    },
    
    /**
     * Displays window with image preview
     * 
     * @param data file data
     */
    showImagePreview : function (data) {
        var w, h;
        if (data.width < 100) {
            w = 100;
        } else if (data.width > 800) {
            w = 800;
        } else {
            w = data.width;
        }

        if (data.height < 100) {
            h = 100;
        } else if (data.height > 700) {
            h = 700;
        } else {
            h = data.height;
        }

        var win = new Ext.Window({
            title      : data.name,
            width      : w,
            height     : h,
            maximizable: true,
            iconCls    : 'filemon-win-image',
            autoScroll : true,
            html : '<img src="' + Filemon.getApi(this.api, 'print-file') + (Filemon.getApi(this.api, 'print-file').indexOf('?') != -1 ? '&' : '?') + 
                'file=' + data.path + '" width="' + data.width + '" height="' + data.height + '" alt="' + data.name + '" />'
        });

        win.show();
    },
    
    /**
     * Displays window with field for entering filename
     *
     * @param value initial input value
     * @param title window title
     * @param handler function executed when send button is pressed
     * @return Ext.Window
     */
    showFileNameWindow : function (value, title, handler) {
        if (!this.fileNameWindow) {
            var thisPanel = this;
            
            this.fileNameWindow = new Ext.Window({
                layout      : 'fit',
                bodyStyle   : 'padding:5px;',
                iconCls     : 'filemon-win-edit',
                width       : 400,
                autoHeight  : true,
                closeAction : 'hide',
                modal       : true,
                items       : new Ext.FormPanel({
                    labelWidth  : 40,
                    defaultType : 'textfield',
                    baseCls     : 'x-plain',
                    items       : [
                        {
                            fieldLabel : Filemon.lang.translate('Name'),
                            anchor     : '100%',
                            name       : 'fileName',
                            vtype      : 'fileName',
                            listeners  : {
                                specialkey : function (input, event) {
                                    if (event.getKey() == event.ENTER) {
                                        thisPanel.fileNameWindow.buttons[0].handler.call(thisPanel);
                                    }
                                }
                            }
                        }
                    ]
                }),
                buttons   : [
                    {
                        text    : Filemon.lang.translate('Send'),
                        scope   : this,
                        handler : function () {
                            var value = this.fileNameWindow.items.items[0].getForm().findField('fileName').getValue();
                            
                            if (Filemon.util.checkFilename(value)) {
                                this.fileNameWindow.handler(value, this.fileNameWindow);
                            }
                        }
                    },
                    {
                        text    : Filemon.lang.translate('Cancel'),
                        scope   : this,
                        handler : function () {
                            this.fileNameWindow.hide();
                        }
                    }
                ],
                listeners : {
                    show : function () {
                        if (!this.loadMask)
                            this.loadMask = new Ext.LoadMask(this.getId());
                    }
                }
            });
        }
        
        this.fileNameWindow.handler = handler;
        
        this.fileNameWindow.setTitle(Filemon.lang.translate(title));
        
        this.fileNameWindow.show();
        
        this.fileNameWindow.items.items[0].getForm().findField('fileName').setValue(value);

        return this.fileNameWindow;
    },
    
    /**
     * Executes AJAX query to initialize files move
     * 
     * @param files array with file paths
     * @param destDir path to destination directory
     * @param reloadDirTree if set to true after operation is completed reloads directory tree
     */
    moveFiles : function (files, destDir, reloadDirTree) {        
        var thisPanel = this;
        
        this.mask.show();
        Ext.Ajax.request({
            url    : Filemon.getApi(this.api, 'move-files'),
            params : {
                destination : destDir,
                files       : Ext.encode(files)
            },
            success : function (response) {
                thisPanel.mask.hide();

                var data = Ext.decode(response.responseText);

                if (data.success) {
                    thisPanel.filesView.store.reload();
                    if (reloadDirTree) {
                        thisPanel.dirTree.root.reload();
                    }
                } else {
                    Filemon.util.error('Unable to move files', data.errorMsg);
                }
            },
            failure : function (response) {
                thisPanel.mask.hide();

                Filemon.util.connectionError();
            }
        });
    },
    
    /**
     * Returns paths of selected files
     * 
     * @return Array
     */
    getSelectedFiles : function () {
        var data = [];
        
        var selected = this.getSelectedData();
        
        for (var i in selected) {
            if (selected[i].path)
                data.push(selected[i].path);
        }
        
        return data;
    },
    
    /**
     * Returns information about selected files
     * 
     * @return Array
     */
    getSelectedData : function () {
        var data = [];
        
        var selected = this.filesView.getSelectedRecords();
        
        for (var i in selected) {
            if (!selected[i].data) {
                continue;
            }
            // if directory selection is not allowed - dirs are filtered from the result
            if (!this.allowDirSelect && selected[i].data.dir) {
                continue;
            }
            
            data.push(selected[i].data);
        }
        
        return data;
    },
    
    /**
     * @see Ext.Component.onRender
     */
    onRender : function (ct, pos) {
        Filemon.Panel.superclass.onRender.call(this, ct, pos);

        this.mask = new Ext.LoadMask(this.getId(), {store : this.filesView.store});

        this.dirTree.root.expand();
    }
});

/**
 * Window that allows file selection
 * 
 * @param cfg configuration object
 */
Filemon.Window = function (cfg) {
    cfg = cfg || {};
    
    // Default config params
    Ext.applyIf(cfg, {
        multiSelect    : true, // Is multiple file selection allowed
        allowDirSelect : true, // Is directory selection allowed
        allowPreview   : false,// Does preview after double click is allowed
        curDir         : '/',  // Start directory
        filter         : '',   // File extensions filter,
        iconsPath      : 'filemon/images/', // Path to icons
        iconCls        : 'filemon-win-default-icon', // Icon class
        title          : Filemon.lang.translate('Select a file') // Window title
    });
    
    if (!cfg.api) {
        throw 'Please provide API configuration';
    }
    
    // Ext.Panel config - not to be changed
    Ext.apply(cfg, {
        layout : 'fit'
    });
    
    Filemon.Window.superclass.constructor.call(this, cfg);
};

Ext.extend(Filemon.Window, Ext.Window, {
    /**
     * Initialization of components
     */
    initComponent : function () {
        var thisWindow = this;
        
        this.addEvents('filesselected');
        
        this.panel = new Filemon.Panel({
            multiSelect    : this.multiSelect,
            allowDirSelect : this.allowDirSelect,
            allowPreview   : this.allowPreview,
            curDir         : this.curDir,
            filter         : this.filter,
            iconsPath      : this.iconsPath,
            api            : this.api,
            border         : false,
            listeners      : {
                filedblclicked : function (data) {
                    if (!thisWindow.allowPreview) {
                        var files = [data.path];

                        thisWindow.fireEvent('filesselected', thisWindow, files);
                    }
                }
            }
        });
        
        this.items = [this.panel];
        
        this.buttons = [
            {
                text    : Filemon.lang.translate('Select'),
                handler : function () {
                    var files = thisWindow.panel.getSelectedFiles();
                    
                    if (files.length == 0) {
                        Filemon.util.error('No file selected', 'Please select a file');
                    } else {
                        thisWindow.fireEvent('filesselected', thisWindow, files);
                    }
                }
            },
            {
                text    : Filemon.lang.translate('Cancel'),
                handler : function () {
                    if (thisWindow.closeAction == 'hide') {
                        thisWindow.hide();
                    } else {
                        thisWindow.close();
                    }
                }
            }
        ];
        
        Filemon.Panel.superclass.initComponent.call(this);
    },
    
    /**
     * Returns paths of selected files
     * 
     * @return Array
     */
    getSelectedFiles : function () {
        return this.panel.getSelectedFiles();
    },
    
    /**
     * Returns information about selected files
     * 
     * @return Array
     */
    getSelectedData : function () {
        return this.panel.getSelectedData();
    }
});

/**
 * DragZone for files
 */
Filemon.FileDragZone = function(view, config) {
    this.view = view;
    Filemon.FileDragZone.superclass.constructor.call(this, view.getEl(), config);
};

Ext.extend(Filemon.FileDragZone, Ext.dd.DragZone, {
    /**
     * @see Ext.dd.DragZone.getDragData
     */
    getDragData : function(e) {
        var target = e.getTarget('.filemon-file-wrap');
        
        if (target) {
            var view = this.view;
            
            if (!view.isSelected(target)) {                
                view.onClick(e);
            }
            
            var selNodes = view.getSelectedNodes();
            
            var dragData = {
                nodes     : selNodes,
                filesDrag : true // info that this drag event comes from files list
            };
            
            if (selNodes.length == 1) {
                dragData.ddel = target.firstChild.firstChild; // the img element
                dragData.single = true;
            } else {
                var div = document.createElement('div'); // create the multi element drag "ghost"
                div.className = 'filemon-multi-proxy';
                
                for (var i = 0, len = selNodes.length; i < len; i++) {
                    div.appendChild(selNodes[i].firstChild.firstChild.cloneNode(true));
                    
                    if ((i+1) % 3 == 0) {
                        div.appendChild(document.createElement('br'));
                    }
                }
                
                dragData.ddel  = div;
                dragData.multi = true;
            }
            
            return dragData;
        }
        
        return false;
    },
    
    /**
     * @see Ext.dd.DragZone.afterRepair
     */
    afterRepair : function() {
        for (var i = 0, len = this.dragData.nodes.length; i < len; i++) {
            Ext.fly(this.dragData.nodes[i]).frame('#8db2e3', 1);
        }
        
        this.dragging = false;
    },
    
    /**
     * @see Ext.dd.DragZone.getRepairXY
     */
    getRepairXY : function(e){
        if (!this.dragData.multi) {
            var xy = Ext.Element.fly(this.dragData.ddel).getXY();
            xy[0] += 3;
            xy[1] += 3;
            
            return xy;
        }
        
        return false;
    }
});

/**
 * DropZone for files
 */
Filemon.FileDropZone = function(panel, config) {
    this.panel = panel;
    Filemon.FileDropZone.superclass.constructor.call(this, panel.filesView.getEl(), config);
};

Ext.extend(Filemon.FileDropZone, Ext.dd.DropZone, {
    /**
     * @see Ext.dd.DropZone.getTargetFromEvent
     */
    getTargetFromEvent : function(e) {
        var target = e.getTarget('.filemon-directory');
        
        if (target) {
            var view = this.panel.filesView;
            
            if (view.isSelected(target)) {
                return false;
            }
            
            return {
                ddel     : target,
                dropData : view.getRecord(target)
            };
        }
        
        return false;
    },
    
    /**
     * @see Ext.dd.DropZone.onNodeDrop
     */
    onNodeDrop : function (nodeData, source, e, data) {        
        var dragFiles = [];
        var dropDir   = nodeData.dropData.data.path;
        var reloadDirTree = false;

        if (data.filesDrag) {
            var records = this.panel.filesView.getRecords(data.nodes);

            for (var i in records) {
                if (!records[i].data) {
                    continue;
                }

                if (Filemon.util.checkMovePath(records[i].data.path, dropDir)) {
                    if (records[i].data.dir) {
                        reloadDirTree = true;
                    }
                    dragFiles.push(records[i].data.path);
                }
            }
        } else {
            if (Filemon.util.checkMovePath(data.node.attributes.id, dropDir)) {
                reloadDirTree = true;
                dragFiles.push(data.node.attributes.id);
            }
        }

        if (dragFiles.length == 0) {
            return false;
        }

        this.panel.moveFiles(dragFiles, dropDir, reloadDirTree);

        return true;
    }
});

/**
 * Crops the file name to 12 characters
 *
 * @param name file name
 */
Filemon.util.cropFileName = function (name) {
    if (name.length > 12) {
        return name.substr(0, 9) + '...';
    }

    return name;
};

/**
 * Displays error messages
 *
 * @param title error window title
 * @param msg error message
 */
Filemon.util.error = function(title, msg) {
    Ext.MessageBox.show({
        title   : Filemon.lang.translate(title),
        msg     : Filemon.lang.translate(msg),
        buttons : Ext.MessageBox.OK,
        icon    : Ext.MessageBox.ERROR
    });
};

/**
 * Displays connection error message
 */
Filemon.util.connectionError = function () {
    Filemon.util.error('Unable to connect to the server', 'Can\'t connect to the server. Please try again later.');
};

/**
 * Checks if entered text is a correct filename
 *
 * @param name filename
 * @return boolean
 */
Filemon.util.checkFilename = function (name) {
    if (name == '') {
        Filemon.util.error('Name is empty', 'Enter name of the file / directory');
        return false;
    }

    var correctN = /[\/\\\?%\*:|"'<>]/;

    if (correctN.test(name)) {
        Filemon.util.error('Name contains illegal characters', 'The name of the file / directory may not contain the following characters: / \\ ? % * : | " \' < >');
        return false;
    }

    if (name.charAt(0) == '.') {
        Filemon.util.error('Name contains illegal characters', 'The name of the file / directory may not start with a dot');
        return false;
    }

    return true;
};

/**
 * Checks if file under source path can be moved to destination path
 * 
 * @param source source path
 * @param dest destination path
 * @return boolean
 */
Filemon.util.checkMovePath = function (source, dest) {
    return dest.indexOf(source) !== 0;
};

// VType for filename
Ext.form.VTypes['fileNameVal']  = /[^\/\\\?%\*:|"'<>]/;
Ext.form.VTypes['fileNameMask'] = /[^\/\\\?%\*:|"'<>]/;
Ext.form.VTypes['fileNameText'] = Filemon.lang.translate('Invalid file name');
Ext.form.VTypes['fileName']     = function (v) {
    return Ext.form.VTypes['fileNameVal'].test(v) && v.charAt(0) != '.';
};

Filemon.version = '1.0.0';

Ext.reg('filemonpanel', Filemon.Panel);
