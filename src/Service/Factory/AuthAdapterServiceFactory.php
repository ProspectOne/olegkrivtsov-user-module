<?php

namespace ProspectOne\UserModule\Service\Factory;

use Interop\Container\ContainerInterface;
use ProspectOne\UserModule\Service\AuthAdapterService;
use Zend\Crypt\Password\Bcrypt;
use Zend\Http\PhpEnvironment\Request;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Authentication\Storage\Session as SessionStorage;
use ProspectOne\UserModule\Service\UserManager;

/**
 * Class AuthAdapterServiceFactory
 * @package ProspectOne\UserModule\Service\Factory
 */
class AuthAdapterServiceFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return AuthAdapterService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // Get Doctrine entity manager from Service Manager.
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        /** @var Bcrypt $bcrypt */
        $bcrypt = $container->get('ProspectOne\UserModule\Bcrypt');

        $config = $container->get('Config');
        $headerEnabled = $config['ProspectOne\UserModule']['auth']['header'];
        $userEntityClassName = $config['UserModule']['userEntity'];

        /** @var Request $request */
        $request = $container->get("Request");
        if ($headerEnabled && $request instanceof Request) {
            $header = $request->getHeaders()->get($config['ProspectOne\UserModule']['auth']['header_name']);
            if ($header !== false) {
                $header = $header->getFieldValue();
            } else {
                $header = null;
            }
        } else {
            $header = null;
        }

        /** @var SessionStorage $authStorage */
        $authStorage = $container->get(SessionStorage::class);

        if (!$authStorage->isEmpty()) {
            $email = $authStorage->read();
            /** @var UserManager $userManagerService */
            $userManagerService = $container->get(UserManager::class);
            if(!$userManagerService->checkUserExists($email)){
                $email = "";
                $authStorage->clear();
            }
        } else {
            $email = "";
        }

        // Create the AuthAdapter and inject dependency to its constructor.
        return new AuthAdapterService($entityManager, $bcrypt, $headerEnabled, $header, $email, $userEntityClassName);
    }
}
