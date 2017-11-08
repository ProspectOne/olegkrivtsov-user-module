<?php
namespace ProspectOne\UserModule\Mapper\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use ProspectOne\UserModule\Mapper\UserMapper;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class UserMapperFactory
 * @package ProspectOne\UserModule\Mapper\Factory
 */
class UserMapperFactory implements FactoryInterface
{

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $config = $container->get('Config');
        $userEntityClassName = $config['UserModule']['userEntity'];
        $repository = $em->getRepository($userEntityClassName);
        $userMapper = new UserMapper($repository);
        return $userMapper;
    }
}
