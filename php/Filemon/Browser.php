<?php
/**
 * Filemon_Browser
 * 
 * Filemon Browser Class Definition File
 * 
 * This file contains the definition for the Filemon_Browser
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
 * This is the main class that provides implementation of all file system operations
 */
class Filemon_Browser
{
    // Error messages
    const ERROR_DIR_READONLY         = 'Can\'t write to the target directory';
    const ERROR_DIR_NOT_EXISTS       = 'Target directory does not exists';
    const ERROR_INCORRECT_FILENAME   = 'Filename contains illegal characters';
    const ERROR_FILE_NOT_CREATED     = 'Unable to create file';
    const ERROR_DIR_NOT_CREATED      = 'Unable to create directory';
    const ERROR_FILE_ALREADY_EXISTS  = 'File with a given name already exists';
    const ERROR_MOVE_SOURCE_READONLY = 'Can\'t move selected files because its source directory is readonly';
    const ERROR_FILE_NOT_RENAMED     = 'Unable to rename file';
    const ERROR_FILE_NOT_EXISTS      = 'Target file does not exists';
    const ERROR_FILE_READONLY        = 'Target file is not writable';
    
    // Error messages for file upload
    const UPLOAD_ERR_INI_SIZE   = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
    const UPLOAD_ERR_FORM_SIZE  = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
    const UPLOAD_ERR_PARTIAL    = 'The uploaded file was only partially uploaded';
    const UPLOAD_ERR_NO_FILE    = 'Missing a temporary folder';
    const UPLOAD_ERR_CANT_WRITE = 'Failed to write file to disk';
    const UPLOAD_ERR_EXTENSION  = 'A PHP extension stopped the file upload';
    const UPLOAD_ERR_GENERAL    = 'Failed to upload file';
    
    /**
     * Path to operate on
     * 
     * @var string
     */
    private $_basePath;
    
    /**
     * Array with supported filetypes
     * 
     * @var array
     */
    private static $_fileTypes = array(
        'csv'  => 'txt',
        'txt'  => 'txt',
        'css'  => 'txt',
        'ini'  => 'txt',
        'xml'  => 'txt',
        'doc'  => 'document',
        'docx' => 'document',
        'pdf'  => 'document',
        'odt'  => 'document',
        'html' => 'html',
        'bmp'  => 'bmp',
        'jpg'  => 'image',
        'png'  => 'image',
        'jpeg' => 'image',
        'gif'  => 'image',
        'php'  => 'php',
        'zip'  => 'zip',
        'gz'   => 'zip',
        'tar'  => 'zip',
        'flv'  => 'movie',
        'xls'  => 'sheet',
        'xlsx' => 'sheet'
    );

    /**
     * Constructor
     * 
     * @param string $path path to operate on
     */
    public function __construct($path)
    {
        $this->_basePath = str_replace(array('/../', '/./', '\\..\\'), '/', $path);
        
        // removing trailing slash
        if ($this->_basePath[strlen($this->_basePath) - 1] == '/') {
            $this->_basePath = substr($this->_basePath, 0, strlen($this->_basePath) - 1);
        }
    }
    
    /**
     * Lists folders in desirable directory. Returns array that comforts Ext.tree.TreePanel.
     * 
     * @param string $dir directory to browse
     * @return array
     */
    public function listFolders($dir = '/')
    {
        // adding starting slash
        if ($dir{0} != '/') {
            $dir = '/' . $dir;
        }
        
        $dir = str_replace(array('/../', '/./', '\\..\\'), '/', $dir);
        
        // adding trailing slash
        if ($dir[strlen($dir) - 1] != '/') {
            $dir .= '/';
        }
        
        // full patch
        $path = $this->_basePath . $dir;
        
        $files = array();

        if (is_dir($path)) {
            $dh = opendir($path);

            while (($file = readdir($dh)) !== false) {
                if ($file[0] != '.' && filetype($path . $file) == 'dir') {
                    $files[] = array(
                        'text' => $file,
                        'id'   => $dir . $file,
                        'leaf' => false,
                        'cls'  => 'dir-tree-folder'
                    );
                }
            }

            closedir($dh);
        }
        
        return $files;
    }
    
    /**
     * Deletes unused cache files from the directory
     * 
     * @param string $dir path to directory
     */
    public function optimizeCache($dir)
    {
        // adding starting slash
        if ($dir{0} != '/') {
            $dir = '/' . $dir;
        }
        
        $dir = str_replace(array('/../', '/./', '\\..\\'), '/', $dir);
        
        // adding trailing slash
        if ($dir[strlen($dir) - 1] != '/') {
            $dir .= '/';
        }
        
        // full patch
        $path = $this->_basePath . $dir;
        
        if (is_dir($path . '.fm-cache')) {
            $cache = $path . '.fm-cache/';

            $dh = opendir($cache);

            while (($file = readdir($dh)) !== false) {
                if (!file_exists($path . $file)) {
                    // delete unused cache file
                    @unlink($cache . $file);
                }
            }

            closedir($dh);
        }
    }
    
    /**
     * List files in desirable directory.
     * 
     * @param string $dir directory to browse
     * @param string $filter list of allowed extensions (separated by "|")
     * @return array
     */
    public function listFiles($dir = '/', $filter = '')
    {
        // adding starting slash
        if ($dir[0] != '/') {
            $dir = '/' . $dir;
        }
        
        $dir = str_replace(array('/../', '/./', '\\..\\'), '/', $dir);
        
        // adding trailing slash
        if ($dir[strlen($dir) - 1] != '/') {
            $dir .= '/';
        }
        
        // full patch
        $path = $this->_basePath . $dir;
        
        if ($filter) {
            $filter = explode('|', $filter);
        }
        
        $files = array();

        if (is_dir($path)) {
            $dh = opendir($path);
            
            while (($file = readdir($dh)) !== false) {
                if ($file[0] != '.') { // hidden files are not displayed
                    $filePath = $path . $file;
                    $f = array();
                    
                    if (is_dir($filePath)) {
                        $f['dir']  = true;
                        $f['icon'] = 'folder';
                    } else {
                        // checking file extension
                        $dotPos = strrpos($file, '.');
                        $ext    = '';
                        if ($dotPos !== false) {
                            $ext = strtolower(substr($file, $dotPos + 1));
                        }
                        
                        if ($filter && !in_array($ext, $filter)) {
                            // current file is not on the display list
                            continue;
                        }
                        
                        // setting file icon
                        if (isset(self::$_fileTypes[$ext])) {
                            if (self::$_fileTypes[$ext] == 'image') {
                                if ($this->_generateThumb($path, $file)) {
                                    $f['icon'] = 'custom';
                                } else {
                                    $f['icon'] = 'image';
                                }
                                
                                list($f['width'], $f['height']) = @getimagesize($path . $file);
                            } else {
                                $f['icon'] = self::$_fileTypes[$ext];
                            }
                        } else {
                            $f['icon'] = 'file';
                        }
                        
                        $f['dir']  = false;
                        $f['size'] = filesize($path . $file);
                    }
                    
                    $f['name']     = $file;
                    $f['path']     = $dir . $file;
                    $f['writable'] = is_writable($filePath);
                    
                    $files[] = $f;
                }
            }
            
            closedir($dh);
        }
        
        usort($files, array($this, 'cmpFiles')); // sorting result
        
        return $files;
    }
    
    /**
     * Prints thumbnail for target file
     *
     * @param string $dir directory where file resides
     * @param string $file file name 
     */
    public function printThumbnail($dir, $file)
    {
        // adding trailing slash
        if ($dir[strlen($dir) - 1] != '/') {
            $dir .= '/';
        }
        
        // addind thumbnail cache directory
        $dir .= '.fm-cache/';
        
        $this->printFile($dir, $file);
    }


    /**
     * Prints target file to the output
     *
     * @param string $dir directory where file resides
     * @param string $file file name 
     */
    public function printFile($dir, $file)
    {
        // adding starting slash
        if ($dir[0] != '/') {
            $dir = '/' . $dir;
        }
        
        $dir = str_replace(array('/../', '/./', '\\..\\'), '/', $dir);
        
        // adding trailing slash
        if ($dir[strlen($dir) - 1] != '/') {
            $dir .= '/';
        }
        
        // full patch
        $path = $this->_basePath . $dir;
        
        if (file_exists($path . $file)) {
            $dotPos = strrpos($file, '.');
            $ext    = '';
            if ($dotPos !== false) {
                $ext = strtolower(substr($file, $dotPos + 1));
            }
            
            $mimeArray = Filemon_Mime::getMimeArray();
            
            if (isset($mimeArray[$ext])) {
                $mimeType = $mimeArray[$ext];

                if (is_array($mimeType)) {
                    $mimeType = $mimeType[0];
                }
            } else {
                $mimeType = 'application/octet-stream';
            }
            
            header("Content-Type: {$mimeType}; name=\"{$file}\"");
            header("Content-Disposition: inline; filename=\"{$file}\"");
            header('Content-Length: ' . filesize($path . $file));
            readfile($path . $file);
        }
    }
    
    /**
     * Creates new file. Returns true or error message.
     * 
     * @param string $fileName name of the file
     * @param string $dir directory
     * @return string|boolean
     */
    public function createFile($fileName, $dir)
    {
        // adding starting slash
        if ($dir[0] != '/') {
            $dir = '/' . $dir;
        }
        
        $dir = str_replace(array('/../', '/./', '\\..\\'), '/', $dir);
        
        // adding trailing slash
        if ($dir[strlen($dir) - 1] != '/') {
            $dir .= '/';
        }
        
        // full patch
        $path = $this->_basePath . $dir;
        
        if (!is_dir($path)) {
            return self::ERROR_DIR_NOT_EXISTS;
        }
        
        if (!is_writable($path)) {
            return self::ERROR_DIR_READONLY;
        }
        
        if (preg_match('/[\/\\\?%\*:|"\'<>]/', $fileName) || $fileName[0] == '.') {
            return self::ERROR_INCORRECT_FILENAME;
        }
        
        if (file_exists($path . $fileName)) {
            return self::ERROR_FILE_ALREADY_EXISTS;
        }
        
        if (!@touch($path . $fileName)) {
            return self::ERROR_FILE_NOT_CREATED;
        }
        
        $this->optimizeCache($dir);
        
        return true;
    }
    
    /**
     * Creates new directory. Returns true or error message.
     * 
     * @param string $dirName name of the new directory
     * @param string $dir directory
     * @return string|boolean
     */
    public function createDir($dirName, $dir)
    {
        // adding starting slash
        if ($dir[0] != '/') {
            $dir = '/' . $dir;
        }
        
        $dir = str_replace(array('/../', '/./', '\\..\\'), '/', $dir);
        
        // adding trailing slash
        if ($dir[strlen($dir) - 1] != '/') {
            $dir .= '/';
        }
        
        // full patch
        $path = $this->_basePath . $dir;
        
        if (!is_dir($path)) {
            return self::ERROR_DIR_NOT_EXISTS;
        }
        
        if (!is_writable($path)) {
            return self::ERROR_DIR_READONLY;
        }
        
        if (preg_match('/[\/\\\?%\*:|"\'<>]/', $dirName) || $dirName[0] == '.') {
            return self::ERROR_INCORRECT_FILENAME;
        }
        
        if (file_exists($path . $dirName)) {
            return self::ERROR_FILE_ALREADY_EXISTS;
        }
        
        if (!@mkdir($path . $dirName)) {
            return self::ERROR_DIR_NOT_CREATED;
        }
        
        $this->optimizeCache($dir);
        
        return true;
    }
    
    /**
     * Renames selected file
     * 
     * @param string $dir directory in which the operation will be carried out
     * @param string $oldName old file name
     * @param string $newName new file name
     * @return string|boolean
     */
    public function renameFile($dir, $oldName, $newName)
    {
        // adding starting slash
        if ($dir[0] != '/') {
            $dir = '/' . $dir;
        }
        
        $dir = str_replace(array('/../', '/./', '\\..\\'), '/', $dir);
        
        // adding trailing slash
        if ($dir[strlen($dir) - 1] != '/') {
            $dir .= '/';
        }
        
        // full patch
        $path = $this->_basePath . $dir;
        
        if (!is_dir($path)) {
            return self::ERROR_DIR_NOT_EXISTS;
        }
        
        if (!is_writable($path)) {
            return self::ERROR_DIR_READONLY;
        }
        
        if (preg_match('/[\/\\\?%\*:|"\'<>]/', $newName) || $newName[0] == '.') {
            return self::ERROR_INCORRECT_FILENAME;
        }
        
        if (!file_exists($path . $oldName)) {
            return self::ERROR_FILE_NOT_EXISTS;
        }
        
        if (file_exists($path . $newName)) {
            return self::ERROR_FILE_ALREADY_EXISTS;
        }
        
        if (!is_writable($path . $oldName)) {
            return self::ERROR_FILE_READONLY;
        }
        
        if (!@rename($path . $oldName, $path . $newName)) {
            return self::ERROR_FILE_NOT_RENAMED;
        }
        
        $this->optimizeCache($dir);
        
        return true;
    }
    
    /**
     * Saves content to the file
     * 
     * @param string $file path to the file
     * @param string $content content of the file
     * @return string|boolean
     */
    public function saveFile($file, $content)
    {
        // adding starting slash
        if ($file[0] != '/') {
            $file = '/' . $file;
        }
        
        $file = str_replace(array('/../', '/./', '\\..\\'), '/', $file);
        
        // full patch
        $path = $this->_basePath . $file;
        
        if (!file_exists($path) || is_dir($path)) {
            return self::ERROR_FILE_NOT_EXISTS;
        }
        
        if (!is_writable($path)) {
            return self::ERROR_FILE_READONLY;
        }
        
        if (ini_get('magic_quotes_gpc')) {
            $content = stripslashes($content);
        }
        
        @file_put_contents($path, $content);
        
        return true;
    }
    
    /**
     * Moves selected files to the destination folder
     * 
     * @param string $destination destination folder to move files
     * @param array $files list of files to move
     * @return array|boolean
     */
    public function moveFiles($destination, array $files)
    {
        // adding starting slash
        if ($destination[0] != '/') {
            $destination = '/' . $destination;
        }
        
        $destination = str_replace(array('/../', '/./', '\\..\\'), '/', $destination);
        
        // adding trailing slash
        if ($destination[strlen($destination) - 1] != '/') {
            $destination .= '/';
        }
        
        // full patch
        $path = $this->_basePath . $destination;
        
        if (!is_dir($path)) {
            return self::ERROR_DIR_NOT_EXISTS;
        }
        
        if (!is_writable($path)) {
            return self::ERROR_DIR_READONLY;
        }
        
        // By this point path is ok. It's time to check files.
        $filesArray = array(); // array with correct files
        foreach ($files as $file) {
            $filePath = $this->_basePath . str_replace(array('/../', '/./', '\\..\\'), '/', $file);
            
            if ($file[0] == '.') {
                continue; // all files that starts with dot are omitted
            }
            
            if (!file_exists($filePath)) {
                continue; // not existing files are ommited
            }
            
            if (strpos($destination, $file) === 0) {
                continue; // can't move directory to its descendant
            }
            
            if (!is_writable(dirname($filePath))) {
                return self::ERROR_MOVE_SOURCE_READONLY;
            }
            
            if (!is_writable($filePath)) {
                return self::ERROR_FILE_READONLY;
            }
            
            $filesArray[] = $filePath;
        }
        // Now all files are checked. It's time to move them.
        
        foreach ($filesArray as $file) {
            @rename($file, $path . basename($file));
            
            // if cache data exists it has to be removed
            $cache = @dirname($file) . '/.fm-cache/' . basename($file);
            if (file_exists($cache)) {
                @unlink($cache);
            }
        }
        
        return true;
    }
    
    /**
     * Deletes directory and all files that it contains
     * 
     * @param string $dir directory path
     * @return boolean
     */
    private function _deleteDir($dir)
    {
        if ($dir[strlen($dir) - 1] != '/') {
            $dir .= '/';
        }
        
        $dh = opendir($dir);
        
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                $filePath = $dir . $file;
                
                if (is_dir($filePath)) {
                    if (!$this->_deleteDir($filePath)) {
                        return false;
                    }
                } else {
                    if (!@unlink($filePath)) {
                        return false;
                    }
                }
            }
        }
        
        closedir($dh);
        
        if (!rmdir($dir)) {
            return false;
        }
        
        return true;
    }

    /**
     * Deletes selected files. Returns true or array of files with can't be deleted.
     * 
     * @param array $files list of files to delete
     * @return array|boolean
     */
    public function delete(array $files)
    {
        $errors = array();
        
        foreach ($files as $file) {
            // adding starting slash
            if ($file[0] != '/') {
                $file = '/' . $file;
            }
            
            $file = str_replace(array('/../', '/./', '\\..\\'), '/', $file);
            
            $filePath = $this->_basePath . $file;
            
            if ($file[0] == '.' || $file == '/') {
                continue;
            }
            
            if (!file_exists($filePath)) {
                continue; // not existing files are ommited
            }
            
            if (is_dir($filePath)) {
                if (!$this->_deleteDir($filePath)) {
                    $errors[] = $file;
                }
            } else {
                if (@unlink($filePath)) {
                    $cache = dirname($filePath) . '/.fm-cache/' . basename($filePath);
                    if (file_exists($cache)) {
                        @unlink($cache);
                    }
                } else {
                    $errors[] = $file;
                }
            }
        }
        
        if (!empty($errors)) {
            return $errors;
        }
        
        return true;
    }
    
    /**
     * Uploads file to the target directory
     * 
     * @param string $dir destination directory
     * @return string|boolean
     */
    public function uploadFile($dir)
    {
        // adding starting slash
        if ($dir[0] != '/') {
            $dir = '/' . $dir;
        }
        
        $dir = str_replace(array('/../', '/./', '\\..\\'), '/', $dir);
        
        // adding trailing slash
        if ($dir[strlen($dir) - 1] != '/') {
            $dir .= '/';
        }
        
        // full patch
        $path = $this->_basePath . $dir;
        
        if (!is_dir($path)) {
            return self::ERROR_DIR_NOT_EXISTS;
        }
        
        if (!is_writable($path)) {
            return self::ERROR_DIR_READONLY;
        }
        
        $newFilePath = $path . basename($_FILES['uploadFile']['name']);
        
        if (is_uploaded_file($_FILES['uploadFile']['tmp_name'])) {
            // uploading file to destination
            move_uploaded_file($_FILES['uploadFile']['tmp_name'], $newFilePath);
            
            $this->optimizeCache($dir);

            return true;
        } else {
            switch ($_FILES['uploadFile']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    return self::UPLOAD_ERR_INI_SIZE;
                case UPLOAD_ERR_FORM_SIZE:
                    return self::UPLOAD_ERR_FORM_SIZE;
                case UPLOAD_ERR_PARTIAL:
                    return self::UPLOAD_ERR_PARTIAL;
                case UPLOAD_ERR_NO_FILE:
                    return self::UPLOAD_ERR_NO_FILE;
                case UPLOAD_ERR_CANT_WRITE:
                    return self::UPLOAD_ERR_CANT_WRITE;
                case UPLOAD_ERR_EXTENSION:
                    return self::UPLOAD_ERR_EXTENSION;
            }
            
            return self::UPLOAD_ERR_GENERAL;
        }
    }
    
    /**
     * Returns properties of the selected directory
     * 
     * @param string $dir path to directory
     * @return array
     */
    private function _getDirProperties($dir)
    {
        $return = array('fileCount' => 0, 'dirCount' => 0, 'size' => 0);
        
        if (!is_dir($dir)) {
            return $return;
        }
        
        if ($dir[strlen($dir) - 1] != '/') {
            $dir .= '/';
        }

        $dh = opendir($dir);    

        while (($file = readdir($dh)) !== false) {
            if ($file[0] != '.') {
                if (is_dir($dir . $file)) {
                    $return['dirCount']++;

                    $s = $this->_getDirProperties($dir . $file);

                    $return['dirCount']   += $s['dirCount'];
                    $return['fileCount']  += $s['fileCount'];
                    $return['size']       += $s['size'];
                } else {
                    $return['fileCount']++;
                    $return['size'] += @filesize($dir . $file);
                }
            }
        }

        closedir($dh);

        return $return;
    }
    
    /**
     * Returns properties of selected file
     * 
     * @param string $file path to file
     * @return array
     */
    private function _getFileProperties($file)
    {
        // adding starting slash
        if ($file[0] != '/') {
            $file = '/' . $file;
        }
        
        $file = str_replace(array('/../', '/./', '\\..\\'), '/', $file);
        
        // full patch
        $path = $this->_basePath . $file;
        
        $filename = basename($file);
        
        $properties = array(
            'size'     => 0,
            'filename' => $filename
        );
        
        if (!file_exists($path)) {
            return $properties;
        }
        
        if (is_dir($path)) {
            $s = $this->_getDirProperties($path);
            
            $properties['type']      = 'dir';
            $properties['fileCount'] = $s['fileCount'];
            $properties['dirCount']  = $s['dirCount'];
            $properties['size']      = $s['size'];
            $properties['icon']      = 'folder';
        } else {
            $s = @stat($path);
            
            if (!$s) {
                return $properties;
            }
            
            $properties['size']  = $s['size'];
            $properties['mtime'] = date('Y-m-d H:i:s', $s['mtime']);
            $properties['ctime'] = date('Y-m-d H:i:s', $s['ctime']);
            $properties['type']  = 'file';
            
            $dotPos = strrpos($filename, '.');
            if ($dotPos !== false) {
                $ext = strtolower(substr($filename, $dotPos + 1));
                
                if (isset(self::$_fileTypes[$ext]) && self::$_fileTypes[$ext] == 'image') {
                    list ($properties['width'], $properties['height']) = getimagesize($path);
                    
                    $properties['type'] = 'image';
                    
                    if (file_exists(dirname($path) . '/.fm-cache/' . $filename)) {
                        $properties['icon'] = 'custom';
                    } else {
                        $properties['icon'] = 'image';
                    }
                } else {
                    if (isset(self::$_fileTypes[$ext])) {
                        $properties['icon'] = self::$_fileTypes[$ext];
                    } else {
                        $properties['icon'] = 'file';
                    }
                }
            } else {
                $properties['icon'] = 'file';
            }
        }
        
        return $properties;
    }

    /**
     * Returns properties for selected files
     * 
     * @param array $files list of files
     * @return array
     */
    public function properties(array $files)
    {
        if (count($files) == 1) {
            $file = str_replace(array('/../', '/./', '\\..\\'), '/', $files[0]);
            
            return $this->_getFileProperties($file);
        } else {
            $properties = array('type' => 'multiple', 'fileCount' => 0, 'dirCount' => 0,
                'icon' => 'multiple', 'size' => 0);
            
            foreach ($files as $file) {
                // adding starting slash
                if ($file[0] != '/') {
                    $file = '/' . $file;
                }
                
                $path = $this->_basePath . str_replace(array('/../', '/./', '\\..\\'), '/', $file);
                
                if (!file_exists($path)) {
                    continue;
                }
                
                if (is_dir($path)) {
                    $properties['dirCount']++;

                    $s = $this->_getDirProperties($path);

                    $properties['fileCount'] += $s['fileCount'];
                    $properties['dirCount']  += $s['dirCount'];
                    $properties['size']      += $s['size'];
                } else {
                    $properties['fileCount']++;
                    $properties['size'] += @filesize($path);
                }
            }
            
            return $properties;
        }
    }
    
    /**
     * Forces download of selected files
     * 
     * @param string|array $files list of files to download
     */
    public function download($files)
    {
        if (is_array($files)) {
            $downloadArray = array();
            
            // preparing list of files to download
            foreach ($files as $file) {
                $downloadArray[] = $this->_basePath . str_replace(array('/../', '/./', '\\..\\'), '/', $file);
            }
            
            $zipFile = $this->zipFiles($downloadArray);
            
            $this->forceFileDownload($zipFile, 'files.zip');
            
            @unlink($zipFile);
        } else {
            $file = $this->_basePath . str_replace(array('/../', '/./', '\\..\\'), '/', $files);
            
            if (!file_exists($file)) {
                return;
            }
            
            if (is_dir($file)) {
                // file is a directory - zipping directory and its contents
                $zipFile = $this->zipFiles(array($file));

                $this->forceFileDownload($zipFile, 'files.zip');

                @unlink($zipFile);
                exit;
            } else {
                // single file download
                $this->forceFileDownload($file);
                exit;
            }
        }
    }
    
    /**
     * Force file download
     * 
     * @param string $file path to file
     * @param type $name optional name of the file that will be presented to the web browser
     */
    public function forceFileDownload($file, $name = '')
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='. ($name ? $name : basename($file)) );
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        
        readfile($file);
    }
    
    /**
     * Creates zip archiwe in system temporary dir and adds files to the archive.
     * Returns path to the zip file.
     * 
     * @param array $files list of files to add to archive
     * @return string
     */
    public function zipFiles(array $files)
    {
        $zip = tempnam(sys_get_temp_dir(), 'FMD');
        
        $arch = new ZipArchive();
        $arch->open($zip);
        
        foreach ($files as $file) {
            $this->_addToArchive($arch, $file);
        }
        
        $arch->close();

        return $zip;
    }
    
    /**
     * Adds selected file or directory to the archive
     * 
     * @param ZipArchive $arch archive to add files to
     * @param string $file path to the file
     * @param string $innerPath inner path of the archive
     */
    private function _addToArchive(ZipArchive $arch, $file, $innerPath = '')
    {
        $fileName = basename($file);

        // can't add the file to the archive because it does not exists or it's a hidden file
        if (!file_exists($file) || $fileName[0] == '.') {
            return;
        }

        if (is_dir($file)) {
            $arch->addEmptyDir($innerPath . $fileName);
            
            $innerPath .= $fileName . '/';

            $dh = opendir($file);
            
            while (($f = readdir($dh)) !== false) {
                // hidden files are omitted
                if ($f[0] != '.') {
                    if (is_dir($file . '/' . $f)) {
                        // adding directory and all files that it contains
                        $arch->addEmptyDir($innerPath . $f);

                        $this->_addToArchive($arch, $file . '/' . $f, $innerPath);
                    } else {
                        // adding single file
                        $arch->addFile($file . '/' . $f, $innerPath . $f);
                    }
                }
            }
            
            closedir($dh);
        } else {
            $arch->addFile($file, $innerPath . $fileName);
        }
    }

    /**
     * Generates thumbnail for image file
     * 
     * @param string $path path to file
     * @param string $file filename
     * @return boolean
     */
    private function _generateThumb($path, $file)
    {
        if (file_exists($path . '.fm-cache/' . $file)) {
            // thumb already exists
            return true;
        }
        
        if (!is_writable($path)) {
            return false;
        }
        
        if (!is_dir($path . '.fm-cache')) {
            // training to create cache dir
            if (!mkdir($path . '.fm-cache')) {
                return false;
            }
        }
        
        // creating thumb
        $thumb = Filemon_Thumb_Factory::create($path . $file);
        $thumb->adaptiveResize(64, 64);
        
        $thumb->save($path . '.fm-cache/' . $file);
        
        return true;
    }
    
    /**
     * Compares file names (used for sorting files list)
     * 
     * @param string $file1 name of file numer 1
     * @param string $file2 name of file numer 2
     * @return int
     */
    public function cmpFiles($file1, $file2)
    {
        if ($file2['dir'] === $file1['dir']) {
            return strcmp($file1['name'], $file2['name']);
        }

        return $file2['dir'] - $file1['dir'];
    }
    
    /**
     * Sets the base path
     * 
     * @param string $basePath base path
     */
    public function setBasePath($basePath)
    {
        $this->_basePath = $basePath;
    }
    
    /**
     * Returns the base path
     * 
     * @return string
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }
}
