<?php
/**
 * Filemon JS example
 * 
 * This example uses Zend Framework
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

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Defune path to public directory
defined('PUBLIC_PATH')
    || define('PUBLIC_PATH', realpath(dirname(__FILE__)));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();