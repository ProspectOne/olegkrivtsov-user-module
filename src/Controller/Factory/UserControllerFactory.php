<?php
namespace ProspectOne\UserModule\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ProspectOne\UserModule\Controller\UserController;
use ProspectOne\UserModule\Service\UserManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * This is the factory for UserController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class UserControllerFactory implements FactoryInterface
{
    const DEFAULT_USER_ROLE_ID = 1;

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return UserController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var ServiceLocatorInterface $container */

        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userManager = $container->get(UserManager::class);

        $config = $container->get("Config");
        $userRoleId = $config['ProspectOne\UserModule']['userRoleId'] ?? self::DEFAULT_USER_ROLE_ID;
        
        // Instantiate the controller and inject dependencies
        return new UserController($entityManager, $userManager, $container, $userRoleId);
    }
}
