<?php
/**
 * Filemon_Zend_Controller
 * 
 * Filemon Zend Controller Class Definition File
 * 
 * This file contains the definition for the Filemon_Zend_Controller
 * 
 * PHP Version 5
 * Filemon JS : The JavaScript File Manager <http://mobilegb.eu/filemon>
 * Copyright (c) 2011, Grzegorz Bednarz
 * 
 * Author: Grzegorz Bednarz <grzesiek@mobilegb.eu>
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
 * @author Grzegorz Bednarz <grzesiek@mobilegb.eu>
 * @copyright Copyright (c) 2011, Grzegorz Bednarz
 * @link http://mobilegb.eu/filemon
 * @version 1.0.0
 * @package Filemon
 */

/**
 * This is Zend Framework Controller that provides interface to the JavaScript 
 * part of the application. If you want to use this class you may simply copy
 * it to yours controllers folder and modify it for your needs. You may also
 * create new Zend Framework Controller that extends this class. In any case
 * remember to provide base path so the controller will know on witch path to
 * operate on.
 * 
 * Example:
 * class MyController extends Filemon_Zend_Controller
 * {
 *   public function init()
 *   {
 *       parent::init();
 *       
 *       // Setting up base path
 *       $this->_basePath = PUBLIC_PATH . '/example';
 *   }
 * }
 */
class Filemon_Zend_Controller extends Zend_Controller_Action
{
    /**
     * Base path for browsing
     * 
     * @var string
     */
    protected $_basePath;
    
    /**
     * Lists folders. Response is JSON encoded. Response format is suitable for Ext.tree.TreePanel.
     */
    public function listFoldersAction()
    {
        $dir = $this->_getParam('node', '/');
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $this->_helper->json($browser->listFolders($dir));
    }
    
    /**
     * List files. Response is JSON encoded.
     */
    public function listFilesAction()
    {
        $dir    = $this->_getParam('path', '/');
        $filter = $this->_getParam('filter', '');
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $this->_helper->json($browser->listFiles($dir, $filter));
    }
    
    /**
     * Displays image thumbnail
     */
    public function printThumbnailAction()
    {
        // disabling Zend_View
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $file = $this->_getParam('file');
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $browser->printThumbnail(dirname($file), basename($file));
    }
    
    /**
     * Prints file content
     */
    public function printFileAction()
    {
        // disabling Zend_View
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $file = $this->_getParam('file');
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $browser->printFile(dirname($file), basename($file));
    }
    
    /**
     * Saves content to the file
     */
    public function saveFileAction()
    {
        $file    = $this->_getParam('file');
        $content = $this->_getParam('content');
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $result = $browser->saveFile($file, $content);
        
        if ($result === true) {
            $this->_helper->json(array('success' => true));
        } else {
            $this->_helper->json(array('success' => false, 'errorMsg' => $result));
        }
    }
    
    /**
     * Creates new file
     */
    public function createFileAction()
    {
        $dir      = $this->_getParam('path', '/');
        $filename = $this->_getParam('fileName', '');
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $result = $browser->createFile($filename, $dir);
        
        if ($result === true) {
            $this->_helper->json(array('success' => true));
        } else {
            $this->_helper->json(array('success' => false, 'errorMsg' => $result));
        }
    }
    
    /**
     * Creates new directory
     */
    public function createDirAction()
    {
        $dir     = $this->_getParam('path', '/');
        $dirName = $this->_getParam('dirName', '');
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $result = $browser->createDir($dirName, $dir);
        
        if ($result === true) {
            $this->_helper->json(array('success' => true));
        } else {
            $this->_helper->json(array('success' => false, 'errorMsg' => $result));
        }
    }
    
    /**
     * Uploads file
     */
    public function uploadFileAction()
    {
        // disabling Zend_View
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $path = $this->_getParam('path', '/');
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $result = $browser->uploadFile($path);
        
        // Can't use $this->_helper->json because of the bug in firefox.
        // Firefox (v 4.x) is trying to download the result page.
        if ($result === true) {
            $this->getResponse()->setBody('{ success: true }');
        } else {
            $this->getResponse()->setBody('{ success: false, errorMsg: "'.$result.'" }');
        }
    }
    
    /**
     * File download
     */
    public function downloadAction()
    {
        // disabling Zend_View
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $browser->download($this->_getParam('files'));
    }
    
    /**
     * Moves selected files to the destination folder
     */
    public function moveFilesAction()
    {
        $destination = $this->_getParam('destination', '/');
        $files       = (array)@json_decode($this->_getParam('files'));
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $result = $browser->moveFiles($destination, $files);
        
        if ($result === true) {
            $this->_helper->json(array('success' => true));
        } else {
            $this->_helper->json(array('success' => false, 'errorMsg' => $result));
        }
    }
    
    /**
     * Renames selected file
     */
    public function renameFileAction()
    {
        $oldName = $this->_getParam('oldName');
        $newName = $this->_getParam('newName');
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $result = $browser->renameFile(dirname($oldName), basename($oldName), $newName);
        
        if ($result === true) {
            $this->_helper->json(array('success' => true));
        } else {
            $this->_helper->json(array('success' => false, 'errorMsg' => $result));
        }
    }
    
    /**
     * Deletes selected files
     */
    public function deleteAction()
    {
        $files = (array)@json_decode($this->_getParam('files'));
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $result = $browser->delete($files);
        
        if ($result === true) {
            $this->_helper->json(array('success' => true));
        } else {
            $this->_helper->json(array('success' => false, 'errorFiles' => $result));
        }
    }
    
    /**
     * Displays properties of selected files
     */
    public function propertiesAction()
    {
        $files = (array)@json_decode($this->_getParam('files'));
        
        $browser = new Filemon_Browser($this->_basePath);
        
        $this->_helper->json($browser->properties($files));
    }
}
