<?php
/*
    part-db version 0.1
    Copyright (C) 2005 Christoph Lechner
    http://www.cl-projects.de/

    part-db version 0.2+
    Copyright (C) 2009 K. Jacobs and others (see authors.php)
    http://code.google.com/p/part-db/

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA

    $Id: tools/label.php 511 2012-08-05 weinbauer73@gmail.com $
*/

require_once ('../lib.php');

$tmpl =& new vlibTemplate(BASE."/templates/".$conf['html']['theme']."/rechner.php/vlib_rechner.tmpl");
$tmpl -> setVar('path',str_replace($_SERVER['DOCUMENT_ROOT'],'',str_replace('tools','',dirname(__FILE__))));
$tmpl -> setVar('head_theme', $conf['html']['theme']);
$tmpl -> setVar('head_charset', $conf['html']['http_charset']);
$tmpl -> pparse();

?>
