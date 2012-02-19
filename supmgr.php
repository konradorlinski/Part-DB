<?PHP
/*
    part-db version 0.1
    Copyright (C) 2005 Christoph Lechner
    http://www.cl-projects.de/

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

    $Id: supmgr.php,v 1.3 2006/03/09 15:08:09 cl Exp $

*/
    include('lib.php');
    partdb_init();
    
    $action = 'default';
    if ( isset( $_REQUEST["add"]))    { $action = 'add';}
    if ( isset( $_REQUEST["delete"])) { $action = 'delete';}
    if ( isset( $_REQUEST["rename"])) { $action = 'rename';}


    if ( $action == 'add')
    {
        supplier_add( $_REQUEST['new_supplier']);
    }
    
    if ( $action == 'delete')
    {
        supplier_delete( $_REQUEST["supplier_sel"]);
    }

    if ( $action == 'rename')
    {
        supplier_rename( $_REQUEST["supplier_sel"], $_REQUEST["new_name"]);
    }

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
          "http://www.w3.org/TR/html4/struct.dtd">
<html>
<head>
    <title>Lieferanten</title>
    <?php print_http_charset(); ?>
    <link rel="StyleSheet" href="css/partdb.css" type="text/css">
</head>
<body class="body">

<div class="outer">
    <h2>Lieferant anlegen</h2>
    <div class="inner">
        <form action="" method="post">
            Neuer Lieferant:
            <input type="text" name="new_supplier">
            <input type="submit" name="add" value="Anlegen">
        </form>
    </div>
</div>


<div class="outer">
    <h2>Lieferant umbenennen/l&ouml;schen</h2>
    <div class="inner">
        <form action="" method="post">
            <table>
                <tr>
                    <td rowspan="2">
                        <select name="supplier_sel" size="8" multiple="multiple">
                        <?php build_suppliers_list(); ?>
                        </select>
                    </td>
                    <td>
                        <input type="submit" name="delete" value="L&ouml;schen">
                    </td>
                </tr>
                <tr>
                    <td>
                        Neuer Name:<br>
                        <input type="text" name="new_name">
                        <input type="submit" name="rename" value="Umbenennen">
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

</body>
</html>
