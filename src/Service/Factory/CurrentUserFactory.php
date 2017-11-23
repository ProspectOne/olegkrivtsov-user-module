<?php

namespace ProspectOne\UserModule\Service\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ProspectOne\UserModule\Interfaces\UserInterface;
use Zend\Authentication\AuthenticationService;
use BadMethodCallException;

/**
 * Class CurrentUserFactory
 * @package ProspectOne\UserModule\Service\Factory
 */
class CurrentUserFactory implements FactoryInterface
{
    /**
     * Create an currently logged in user object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return UserInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : UserInterface
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        /** @var AuthenticationService $authService */
        $authService = $container->get(AuthenticationService::class);
        $email = $authService->getIdentity();
        if (empty($email)) {
            throw new BadMethodCallException("Can be created for only logged in users");
        }
        $config = $container->get("Config");
        $userEntityClassName = $config['ProspectOne\UserModule']['userEntity'];
        /** @var UserInterface $user */
        $user = $entityManager->getRepository($userEntityClassName)->findBy(['email' => $email])[0];
        return $user;
    }
}
