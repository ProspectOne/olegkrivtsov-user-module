<?php

namespace ProspectOne\UserModule\Controller;

use ProspectOne\UserModule\Service\UserManager;
use ProspectOne\UserModule\Interfaces\UserInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Doctrine\ORM\EntityManager;

/**
 * Class ConsoleController
 * @package ProspectOne\UserModule\Controller
 */
class ConsoleController extends AbstractActionController
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    public $userEntityClassName;

    /**
     * @return UserManager
     */
    public function getUserManager(): UserManager
    {
        return $this->userManager;
    }

    /**
     * @param UserManager $userManager
     * @return ConsoleController
     */
    public function setUserManager(UserManager $userManager): ConsoleController
    {
        $this->userManager = $userManager;
        return $this;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     * @return ConsoleController
     */
    public function setEntityManager(EntityManager $entityManager): ConsoleController
    {
        $this->entityManager = $entityManager;
        return $this;
    }

    /**
     * ConsoleController constructor.
     * @param UserManager $userManager
     * @param EntityManager $entityManager
     * @param $userEntityClassName
     */
    public function __construct(UserManager $userManager, EntityManager $entityManager, $userEntityClassName)
    {
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->userEntityClassName = $userEntityClassName;
    }

    /**
     *
     */
    public function regenerateTokensAction()
    {
        /** @var UserInterface[] $users */
        $users = $this->entityManager->getRepository($this->userEntityClassName)->findAll();
        foreach($users as $user) {
            $token = $this->getUserManager()->generateToken();
            $user->setToken($token);
            $this->getEntityManager()->persist($user);
        }
        $this->getEntityManager()->flush();
    }
}
