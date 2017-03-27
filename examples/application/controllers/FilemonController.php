<?php
/**
 * FilemonController
 * 
 * FilemonController Class Definition File
 * 
 * This file contains the example of Filemon_Zend_Controller usage.
 * 
 * WARNING: this example dose not provide any access controll. If you want
 * to use this files in your web application please remember to add some
 * access controlle mechanizm.
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

/**
 * This is the example usage of the Filemon_Zend_Controller class
 */
class FilemonController extends Filemon_Zend_Controller
{
    /**
     * Controller initialization
     * 
     * @link http://framework.zend.com/apidoc/core/_Controller_Action.html#%5CZend_Controller_Action::init()
     */
    public function init()
    {
        parent::init();
        
        // Setting up base path
        $this->_basePath = PUBLIC_PATH . '/files/example';
    }
}
