<?php

namespace Fosc;

use Sunlight\User as BaseUser;

class User extends BaseUser
{
    public static function hasAllPrivileges(array $privileges): bool
    {
        $result = null;
        foreach ($privileges as $privilege) {
            $result = User::hasPrivilege($privilege);
            // if the user does not have any of the required privileges, there is no need to test further
            if ($result === false) {
                break;
            }
        }
        return $result ?? false;
    }

    public static function hasAnyPrivileges(array $privileges): bool
    {
        $result = false;
        foreach ($privileges as $privilege) {
            $result = User::hasPrivilege($privilege);
            // if the user has any of the required privileges, there is no need to test further
            if ($result === true) {
                break;
            }
        }
        return $result;
    }
}