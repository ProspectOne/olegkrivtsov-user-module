<?php

namespace ProspectOne\UserModule\Model;

use ProspectOne\UserModule\Entity\User;
use ProspectOne\UserModule\Mapper\UserMapper;

/**
 * Class UserModel
 * @package ProspectOne\UserModule\Model
 */
class UserModel
{
    /**
     * @var UserMapper
     */
    private $userMapper;

    /**
     * UserModel constructor.
     * @param UserMapper $userMapper
     */
    public function __construct(UserMapper $userMapper)
    {
        $this->userMapper = $userMapper;
    }

    /**
     * @return UserMapper
     */
    public function getUserMapper(): UserMapper
    {
        return $this->userMapper;
    }

    /**
     * @param UserMapper $userMapper
     */
    public function setUserMapper(UserMapper $userMapper): void
    {
        $this->userMapper = $userMapper;
    }

    /**
     * @param $email
     * @return User
     * @throws \Doctrine\ORM\ORMException
     */
    public function getUserByEmail($email)
    {
        static $users;
        if (!empty($users[$email])) {
            return $users[$email];
        }
        $entity = $this->getUserMapper()->findByEmail($email);
        $users[$email] = $entity;
        return $entity;
    }
}
