<?php
namespace ProspectOne\UserModule\Mapper\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use ProspectOne\UserModule\Mapper\UserMapper;
use Zend\Hydrator\AbstractHydrator;
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
     * @return UserMapper
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $config = $container->get('Config');
        $userEntityClassName = $config['ProspectOne\UserModule']['userEntity'];
        $repository = $em->getRepository($userEntityClassName);

        /** @var AbstractHydrator $hydrator */
        $hydrator = $container->get('ProspectOne\UserModule\UserHydrator');
        $userMapper = new UserMapper($repository, $hydrator);
        return $userMapper;
    }
}
