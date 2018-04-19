<?php
namespace ProspectOne\UserModule\Service\Factory;

use Interop\Container\ContainerInterface;
use ProspectOne\UserModule\Model\UserModel;
use ProspectOne\UserModule\Service\UserManager;
use Zend\Crypt\Password\Bcrypt;

/**
 * This is the factory class for UserManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class UserManagerFactory
{
    /**
     * This method creates the UserManager service and returns its instance.
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return UserManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $params = $this->getParams($container);
                        
        return $this->createService($params, UserManager::class);
    }

    /**
     * @param ContainerInterface $container
     * @return array
     */
    protected function getParams(ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        /** @var Bcrypt $bcrypt */
        $bcrypt = $container->get('ProspectOne\UserModule\Bcrypt');

        $config = $container->get("Config");
        $userEntityClassName = $config['ProspectOne\UserModule']['userEntity'];
        $roleEntityClassName = $config['ProspectOne\UserModule']['roleEntity'];
        $userModel = $container->get(UserModel::class);

        return [$entityManager, $bcrypt, $userEntityClassName, $roleEntityClassName, $userModel];
    }

    /**
     * @param array $params
     * @param string $requestedName
     * @return UserManager
     */
    protected function createService(array $params, $requestedName)
    {
        return new $requestedName(...$params);
    }
}
