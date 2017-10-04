<?php

namespace ProspectOne\UserModule\Service;

use ProspectOne\UserModule\Interfaces\UserInterface;
use ProspectOne\UserModule\Service\AuthAdapter;
use Zend\Authentication\Result;
use LogicException;
use \ProspectOne\UserModule\Exception\LogicException as UserModuleLogicException;

/**
 * Class AuthAdapterService
 * @package ProspectOne\UserModule\Service
 */
class AuthAdapterService extends AuthAdapter
{
    /**
     * @var UserInterface
     */
    protected $currentUser;

    /**
     * @return UserInterface
     */
    protected function getCurrentUser(): ?UserInterface
    {
        return $this->currentUser;
    }

    /**
     * @param UserInterface $currentUser
     * @return AuthAdapterService
     */
    protected function setCurrentUser(UserInterface $currentUser): AuthAdapterService
    {
        $this->currentUser = $currentUser;
        return $this;
    }

    /**
     * @return UserInterface
     */
    public function getCurrentUserEntity()
    {
        if(empty($this->getCurrentUser()) && !empty($this->getEmail())) {
            $user = $this->getUserByEmail($this->getEmail());
            if($user) {
                $this->setCurrentUser($user);
            }
        }
        return $this->getCurrentUser();
    }
}
