<?php
/**
 * Filemon_Autoload
 * 
 * Filemon Autoload Class Definition File
 * 
 * This file contains the definition for the Filemon_Autoload
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
 * Provides basic auto loading mechanism
 */
class Filemon_Autoload
{
    /**
     * Class loading function
     * 
     * @param string $className class name
     * @return boolean
     */
    public static function autoload($className)
    {
        if (strpos($className, 'Filemon_') === 0) {
            @include_once str_replace('_', '/', $className).'.php';
            
            return class_exists($className);
        }
        
        return false;
    }
    
    /**
     * Registers autoload function
     */
    public static function registerAutoload()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
}
