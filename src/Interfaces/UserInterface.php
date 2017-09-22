<?php
namespace ProspectOne\UserModule\Interfaces;

/**
 * Interfaces UserInterface
 * @package ProspectOne\UserModule\Interfaces
 */
interface UserInterface
{
    public function addRole($role);
    public function getId();
    public function getStatus();
    public function getPassword();
    public function getEmail();
    public function getFullName();
    public function getToken();
    public function getStatusRetired();
    public function getStatusActive();
    public function getRoleName();
    public function getPasswordResetTokenCreationDate();
    public function setPassword($password);
    public function setToken(?string $token);
    public function setPasswordResetToken($token);
    public function setPasswordResetTokenCreationDate($data);
    public function setEmail($email);
    public function setFullName($fullName);
    public function setStatus($status);
    public function setDateCreated($dateCreated);
}
