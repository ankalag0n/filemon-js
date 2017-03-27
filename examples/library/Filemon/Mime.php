<?php
/**
 * Filemon_Mime
 * 
 * Filemon Mime Class Definition File
 * 
 * This file contains the definition for the Filemon_Mime
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
 * This class provides information about file extensions and mime types
 */
class Filemon_Mime
{
    private static $_mimes = array( 
        'hqx'   =>  'application/mac-binhex40',
        'cpt'   =>  'application/mac-compactpro',
        'csv'   =>  array('text/csv', 'text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
        'bin'   =>  'application/macbinary',
        'dms'   =>  'application/octet-stream',
        'lha'   =>  'application/octet-stream',
        'lzh'   =>  'application/octet-stream',
        'exe'   =>  'application/octet-stream',
        'class' =>  'application/octet-stream',
        'psd'   =>  'application/x-photoshop',
        'so'    =>  'application/octet-stream',
        'sea'   =>  'application/octet-stream',
        'dll'   =>  'application/octet-stream',
        'oda'   =>  'application/oda',
        'pdf'   =>  array('application/pdf', 'application/x-download'),
        'ai'    =>  'application/postscript',
        'eps'   =>  'application/postscript',
        'ps'    =>  'application/postscript',
        'smi'   =>  'application/smil',
        'smil'  =>  'application/smil',
        'mif'   =>  'application/vnd.mif',
        'xls'   =>  array('application/excel', 'application/vnd.ms-excel'),
        'ppt'   =>  array('application/powerpoint', 'application/vnd.ms-powerpoint'),
        'wbxml' =>  'application/wbxml',
        'wmlc'  =>  'application/wmlc',
        'dcr'   =>  'application/x-director',
        'dir'   =>  'application/x-director',
        'dxr'   =>  'application/x-director',
        'dvi'   =>  'application/x-dvi',
        'gtar'  =>  'application/x-gtar',
        'gz'    =>  'application/x-gzip',
        'php'   =>  'application/x-httpd-php',
        'php4'  =>  'application/x-httpd-php',
        'php3'  =>  'application/x-httpd-php',
        'phtml' =>  'application/x-httpd-php',
        'phps'  =>  'application/x-httpd-php-source',
        'js'    =>  'application/x-javascript',
        'swf'   =>  'application/x-shockwave-flash',
        'sit'   =>  'application/x-stuffit',
        'tar'   =>  'application/x-tar',
        'tgz'   =>  'application/x-tar',
        'xhtml' =>  'application/xhtml+xml',
        'xht'   =>  'application/xhtml+xml',
        'zip'   =>  array('application/zip', 'application/x-zip', 'application/x-zip-compressed'),
        'mid'   =>  'audio/midi',
        'midi'  =>  'audio/midi',
        'mpga'  =>  'audio/mpeg',
        'mp2'   =>  'audio/mpeg',
        'mp3'   =>  array('audio/mpeg', 'audio/mpg'),
        'aif'   =>  'audio/x-aiff',
        'aiff'  =>  'audio/x-aiff',
        'aifc'  =>  'audio/x-aiff',
        'ram'   =>  'audio/x-pn-realaudio',
        'rm'    =>  'audio/x-pn-realaudio',
        'rpm'   =>  'audio/x-pn-realaudio-plugin',
        'ra'    =>  'audio/x-realaudio',
        'rv'    =>  'video/vnd.rn-realvideo',
        'wav'   =>  'audio/x-wav',
        'bmp'   =>  'image/bmp',
        'gif'   =>  'image/gif',
        'jpeg'  =>  array('image/jpeg', 'image/pjpeg'),
        'jpg'   =>  array('image/jpeg', 'image/pjpeg'),
        'jpe'   =>  array('image/jpeg', 'image/pjpeg'),
        'png'   =>  array('image/png',  'image/x-png'),
        'tiff'  =>  'image/tiff',
        'tif'   =>  'image/tiff',
        'css'   =>  'text/css',
        'html'  =>  'text/html',
        'htm'   =>  'text/html',
        'shtml' =>  'text/html',
        'txt'   =>  'text/plain',
        'text'  =>  'text/plain',
        'log'   =>  array('text/plain', 'text/x-log'),
        'rtx'   =>  'text/richtext',
        'rtf'   =>  'text/rtf',
        'xml'   =>  'text/xml',
        'xsl'   =>  'text/xml',
        'mpeg'  =>  'video/mpeg',
        'mpg'   =>  'video/mpeg',
        'mpe'   =>  'video/mpeg',
        'qt'    =>  'video/quicktime',
        'mov'   =>  'video/quicktime',
        'avi'   =>  'video/x-msvideo',
        'movie' =>  'video/x-sgi-movie',
        'doc'   =>  'application/msword',
        'docx'  =>  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx'  =>  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'word'  =>  array('application/msword', 'application/octet-stream'),
        'xl'    =>  'application/excel',
        'eml'   =>  'message/rfc822',
        'flv'   =>  'video/x-flv'
    );


    /**
     * Returns list of mime types and corresponding extensions
     * 
     * @return array
     */
    public static function getMimeArray()
    {
        return self::$_mimes;
    }
}
