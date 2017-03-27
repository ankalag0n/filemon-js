<?php
/**
 * Filemon JS example
 * 
 * This example does not use Zend Framework
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
 * @package Filemon_Example
 */

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/library'),
    get_include_path(),
)));

require_once 'Filemon/Autoload.php';

Filemon_Autoload::registerAutoload(); // registering autoload function

// base path to operate on
define('BASE_PATH', realpath(dirname(__FILE__) . '/files/example'));

$browser = new Filemon_Browser(BASE_PATH);

// actions
switch (@$_GET['action']) {
    case 'list-folders':
        $dir = @$_REQUEST['node'];
        echo json_encode($browser->listFolders($dir));
        break;
    case 'list-files':
        $dir    = @$_REQUEST['path'];
        $filter = @$_REQUEST['filter'];
        echo json_encode($browser->listFiles($dir, $filter));
        break;
    case 'print-thumbnail':
        $file = @$_REQUEST['file'];
        $browser->printThumbnail(dirname($file), basename($file));
        break;
    case 'print-file':
        $file = @$_REQUEST['file'];
        $browser->printFile(dirname($file), basename($file));
        break;
    case 'save-file':
        $file    = @$_REQUEST['file'];
        $content = @$_REQUEST['content'];
        
        $result = $browser->saveFile($file, $content);
        
        if ($result === true) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'errorMsg' => $result));
        }
        break;
    case 'create-file':
        $dir      = @$_REQUEST['path'];
        $filename = @$_REQUEST['fileName'];
        
        $result = $browser->createFile($filename, $dir);
        
        if ($result === true) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'errorMsg' => $result));
        }
        break;
    case 'create-dir':
        $dir     = @$_REQUEST['path'];
        $dirName = @$_REQUEST['dirName'];
        
        $result = $browser->createDir($dirName, $dir);
        
        if ($result === true) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'errorMsg' => $result));
        }
        break;
    case 'upload-file':
        $path = @$_REQUEST['path'];
        
        $result = $browser->uploadFile($path);

        if ($result === true) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'errorMsg' => $result));
        }
        break;
    case 'download':
        $browser->download(@$_REQUEST['files']);
        break;
    case 'move-files':
        $destination = @$_REQUEST['destination'];
        $files       = (array)@json_decode(@$_REQUEST['files']);
        
        $result = $browser->moveFiles($destination, $files);
        
        if ($result === true) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'errorMsg' => $result));
        }
        break;
    case 'rename-file':
        $oldName = @$_REQUEST['oldName'];
        $newName = @$_REQUEST['newName'];
        
        $result = $browser->renameFile(dirname($oldName), basename($oldName), $newName);
        
        if ($result === true) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'errorMsg' => $result));
        }
        break;
    case 'delete':
        $files = (array)@json_decode(@$_REQUEST['files']);
        
        $result = $browser->delete($files);
        
        if ($result === true) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'errorMsg' => $result));
        }
        break;
    case 'properties':
        $files = (array)@json_decode(@$_REQUEST['files']);
        echo json_encode($browser->properties($files));
        break;
    default:
        header("HTTP/1.0 404 Not Found");
        echo '404 Not Found';
        break;
}
