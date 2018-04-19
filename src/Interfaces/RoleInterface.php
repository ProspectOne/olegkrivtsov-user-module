<?php

namespace ProspectOne\UserModule\Interfaces;

/**
 * Interface RoleInterface
 * @package ProspectOne\UserModule\Interfaces
 */
interface RoleInterface
{
    public function getRoleId();
    public function setRoleId($roleId);
    public function getRoleName();
    public function setRoleName($roleName);
}
