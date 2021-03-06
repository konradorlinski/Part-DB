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
*/

/*
 * Please note:
 *  The files "edit_categories.php", "edit_footprints.php", "edit_manufacturers.php",
 *  "edit_suppliers.php", "edit_devices.php", "edit_storelocations.php" and "edit_filetypes.php"
 *  are quite similar.
 *  If you make changes in one of them, please check if you should change the other files too.
 */

include_once('start_session.php');

use PartDB\AttachmentType;
use PartDB\Database;
use PartDB\HTML;
use PartDB\Log;
use PartDB\Permissions\PermissionManager;
use PartDB\Permissions\StructuralPermission;
use PartDB\User;

$messages = array();
$fatal_error = false; // if a fatal error occurs, only the $messages will be printed, but not the site content

/********************************************************************************
 *
 *   Evaluate $_REQUEST
 *
 *   Notes:
 *       - "$selected_id == 0" means that we will show the form for creating a new attachement type
 *       - the $new_* variables contains the new values after editing an existing
 *           or creating a new attachement type
 *
 *********************************************************************************/

$selected_id                = isset($_REQUEST['selected_id'])   ? (int)$_REQUEST['selected_id'] : 0;
$new_name                   = isset($_POST['name'])          ? (string)$_POST['name']         : '';
$new_parent_id              = isset($_POST['parent_id'])     ? (int)$_POST['parent_id']   : 0;
$add_more                   = isset($_POST['add_more']);
$new_comment                = isset($_POST['comment'])       ? (string)$_POST['comment']      : "";

$action = 'default';
if (isset($_POST["add"])) {
    $action = 'add';
}
if (isset($_POST["delete"])) {
    $action = 'delete';
}
if (isset($_POST["delete_confirmed"])) {
    $action = 'delete_confirmed';
}
if (isset($_POST["apply"])) {
    $action = 'apply';
}

/********************************************************************************
 *
 *   Initialize Objects
 *
 *********************************************************************************/

$html = new HTML($config['html']['theme'], $user_config['theme'], _('Dateitypen'));

try {
    $database               = new Database();
    $log                    = new Log($database);
    $current_user           = User::getLoggedInUser($database, $log);
    $root_attachement_type  = AttachmentType::getInstance($database, $current_user, $log, 0);

    $current_user->tryDo(PermissionManager::ATTACHEMENT_TYPES, StructuralPermission::READ);

    if ($selected_id > 0) {
        $selected_attachement_type = AttachmentType::getInstance($database, $current_user, $log, $selected_id);
    } else {
        $selected_attachement_type = null;
    }
} catch (Exception $e) {
    $messages[] = array('text' => nl2br($e->getMessage()), 'strong' => true, 'color' => 'red');
    $fatal_error = true;
}

/********************************************************************************
 *
 *   Execute actions
 *
 *********************************************************************************/

if (! $fatal_error) {
    switch ($action) {
        case 'add':
            try {
                $new_attachement_type = AttachmentType::add($database, $current_user, $log, $new_name, $new_parent_id);

                if (! $add_more) {
                    $selected_attachement_type = $new_attachement_type;
                    $selected_id = $selected_attachement_type->getID();
                }
            } catch (Exception $e) {
                $messages[] = array('text' => _('Der neue Dateityp konnte nicht angelegt werden!'), 'strong' => true, 'color' => 'red');
                $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
            }
            break;

        case 'delete':
            try {
                if (! is_object($selected_attachement_type)) {
                    throw new Exception(_('Es ist kein Dateityp markiert oder es trat ein Fehler auf!'));
                }

                $attachements = $selected_attachement_type->getAttachementsForType();
                $count = count($attachements);

                if ($count > 0) {
                    $messages[] = array('text' => sprintf(_('Es gibt noch %d Dateianhänge mit diesem Dateityp, '.
                        'daher kann der Dateityp nicht gelöscht werden.'), $count), 'strong' => true, 'color' => 'red');
                } else {
                    $messages[] = array('text' => 'Soll der Dateityp "'.$selected_attachement_type->getFullPath().
                        '" wirklich unwiederruflich gelöscht werden?', 'strong' => true, 'color' => 'red');
                    $messages[] = array('text' => '<br>Hinweise:', 'strong' => true);
                    $messages[] = array('text' => _('&nbsp;&nbsp;&bull; Es gibt keine Dateianhänge mit diesem Dateityp.'));
                    $messages[] = array('text' => _('&nbsp;&nbsp;&bull; Beinhaltet diese Dateityp noch Unterdateitypen, dann werden diese eine Ebene nach oben verschoben.'));
                    $messages[] = array('html' => '<input type="hidden" name="selected_id" value="'.$selected_attachement_type->getID().'">');
                    $messages[] = array('html' => '<input class="btn btn-secondary" type="submit" value="'._("Nein, nicht löschen").'">', 'no_linebreak' => true);
                    $messages[] = array('html' => '<input class="btn btn-danger" type="submit" name="delete_confirmed" value="'._("Ja, Dateityp löschen"). '">');
                }
            } catch (Exception $e) {
                $messages[] = array('text' => _('Es trat ein Fehler auf!'), 'strong' => true, 'color' => 'red');
                $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
            }
            break;

        case 'delete_confirmed':
            try {
                if (! is_object($selected_attachement_type)) {
                    throw new Exception(_('Es ist kein Dateityp markiert oder es trat ein Fehler auf!'));
                }

                $selected_attachement_type->delete();
                $selected_attachement_type = null;
            } catch (Exception $e) {
                $messages[] = array('text' => _('Der Dateityp konnte nicht gelöscht werden!'), 'strong' => true, 'color' => 'red');
                $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
            }
            break;

        case 'apply':
            try {
                if (! is_object($selected_attachement_type)) {
                    throw new Exception(_('Es ist kein Dateityp markiert oder es trat ein Fehler auf!'));
                }

                $selected_attachement_type->setAttributes(array(   'name'      => $new_name,
                    'parent_id' => $new_parent_id,
                    "comment" => $new_comment));
            } catch (Exception $e) {
                $messages[] = array('text' => _('Die neuen Werte konnten nicht gespeichert werden!'), 'strong' => true, 'color' => 'red');
                $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
            }
            break;
    }
}

/********************************************************************************
 *
 *   Set the rest of the HTML variables
 *
 *********************************************************************************/

$html->setVariable('add_more', $add_more, 'boolean');

if (! $fatal_error) {
    try {
        if (is_object($selected_attachement_type)) {
            $parent_id = $selected_attachement_type->getParentID();
            $html->setVariable('id', $selected_attachement_type->getID(), 'integer');
            $name = $selected_attachement_type->getName();
            $comment = $selected_attachement_type->getComment(false);
            $html->setVariable('datetime_added', $selected_attachement_type->getDatetimeAdded(true));
            $html->setVariable('last_modified', $selected_attachement_type->getLastModified(true));
            $last_modified_user = $selected_attachement_type->getLastModifiedUser();
            $creation_user = $selected_attachement_type->getCreationUser();
            if ($last_modified_user != null) {
                $html->setVariable('last_modified_user', $last_modified_user->getFullName(true), "string");
                $html->setVariable('last_modified_user_id', $last_modified_user->getID(), "int");
            }
            if ($creation_user != null) {
                $html->setVariable('creation_user', $creation_user->getFullName(true), "string");
                $html->setVariable('creation_user_id', $creation_user->getID(), "int");
            }
        } elseif ($action == 'add') {
            $parent_id = $new_parent_id;
            $name = $new_name;
            $comment = $new_comment;
        } else {
            $parent_id = 0;
            $name = '';
            $comment = "";
        }

        $html->setVariable('name', $name, 'string');

        $attachement_types_list = $root_attachement_type->buildHtmlTree($selected_id, true, false);
        $html->setVariable('attachement_types_list', $attachement_types_list, 'string');
        $html->setVariable("comment", $comment, "string");

        $parent_attachement_types_list = $root_attachement_type->buildHtmlTree($parent_id, true, true);
        $html->setVariable('parent_attachement_types_list', $parent_attachement_types_list, 'string');
    } catch (Exception $e) {
        $messages[] = array('text' => nl2br($e->getMessage()), 'strong' => true, 'color' => 'red', );
        $fatal_error = true;
    }
}

try {
    $html->setVariable("can_delete", $current_user->canDo(PermissionManager::ATTACHEMENT_TYPES, StructuralPermission::DELETE));
    $html->setVariable("can_edit", $current_user->canDo(PermissionManager::ATTACHEMENT_TYPES, StructuralPermission::EDIT));
    $html->setVariable("can_create", $current_user->canDo(PermissionManager::ATTACHEMENT_TYPES, StructuralPermission::CREATE));
    $html->setVariable("can_move", $current_user->canDo(PermissionManager::ATTACHEMENT_TYPES, StructuralPermission::MOVE));
    $html->setVariable("can_read", $current_user->canDo(PermissionManager::ATTACHEMENT_TYPES, StructuralPermission::READ));
    $html->setVariable('can_visit_user', $current_user->canDo(PermissionManager::USERS, \PartDB\Permissions\UserPermission::READ));
} catch (Exception $e) {
    $messages[] = array('text' => nl2br($e->getMessage()), 'strong' => true, 'color' => 'red', );
    $fatal_error = true;
}
/********************************************************************************
 *
 *   Generate HTML Output
 *
 *********************************************************************************/

//If a ajax version is requested, say this the template engine.
if (isset($_REQUEST["ajax"])) {
    $html->setVariable("ajax_request", true);
}

$reload_link = $fatal_error ? 'edit_attachment_types.php' : '';    // an empty string means that the...
$html->printHeader($messages, $reload_link);                       // ...reload-button won't be visible

if (! $fatal_error) {
    $html->printTemplate('edit_attachement_types');
}

$html->printFooter();
