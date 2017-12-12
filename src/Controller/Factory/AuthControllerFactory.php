<?php
namespace ProspectOne\UserModule\Controller\Factory;

use Interop\Container\ContainerInterface;
use ProspectOne\UserModule\Controller\AuthController;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\Factory\FactoryInterface;
use ProspectOne\UserModule\Service\AuthManager;
use ProspectOne\UserModule\Service\UserManager;

/**
 * This is the factory for AuthController. Its purpose is to instantiate the controller
 * and inject dependencies into its constructor.
 */
class AuthControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object|AuthController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {   
        $params = $this->getParams($container);
        
        return $this->createService($params);
    }

    /**
     * @param ContainerInterface $container
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getParams(ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $authManager = $container->get(AuthManager::class);
        $authService = $container->get(AuthenticationService::class);
        $userManager = $container->get(UserManager::class);
        return [$entityManager, $authManager, $authService, $userManager];
    }

    /**
     * @param $params
     * @return AuthController
     */
    public function createService($params)
    {
        return new AuthController(...$params);
    }
}
