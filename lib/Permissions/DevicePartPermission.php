<?php
/**
 * Created by PhpStorm.
 * User: janhb
 * Date: 20.09.2017
 * Time: 13:46
 */

namespace PartDB\Permissions;


class DevicePartPermission extends BasePermission
{
    const CREATE = "create";
    const READ  = "read";
    const EDIT  = "edit";
    const DELETE = "delete";


    /**
     * Returns an array of all available operations for this Permission.
     * @return array All availabel operations.
     */
    public static function listOperations()
    {
        /**
         * Dont change these definitions, because it would break compatibility with older database.
         * However you can add other definitions, the return value can get high as 30, as the DB uses a 32bit integer.
         */
        $operations = array();
        $operations[] = static::buildOperationArray(0, static::READ, _("Anzeigen"));
        $operations[] = static::buildOperationArray(2, static::EDIT, _("Bearbeiten"));
        $operations[] = static::buildOperationArray(6, static::CREATE, _("Anlegen"));
        $operations[] = static::buildOperationArray(8, static::DELETE, _("Löschen"));

        return $operations;
    }

    protected function modifyValueBeforeSetting($operation, $new_value, $data)
    {
        //Set read permission, too, when you get edit permissions.
        if (($operation == static::EDIT
                || $operation == static::DELETE
                || $operation == static::CREATE)
            && $new_value == static::ALLOW) {
            return parent::writeBitPair($data, static::opToBitN(static::READ), static::ALLOW);
        }

        return $data;
    }
}