<?php

namespace ProspectOne\UserModule\Service\Factory;

use Interop\Container\ContainerInterface;
use ProspectOne\UserModule\Model\DisabledSessionManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Session\SessionManager;
use RuntimeException;
use Zend\Authentication\Storage\Session as SessionStorage;

/**
 * Class SessionStorageFactory
 * @package ProspectOne\UserModule\Service\Factory
 */
class SessionStorageFactory implements FactoryInterface
{
    /**
     * This method creates the Zend\Authentication\AuthenticationService service
     * and returns its instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return SessionStorage
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get("config");
        $useSessions = $config["ProspectOne\UserModule"]["sessionsEnabled"];
        // Instantiate dependencies.
        if ($useSessions) {
            $sessionManagerClass = SessionManager::class;
        } else {
            $sessionManagerClass = DisabledSessionManager::class;
        }


        try {
            /** @var SessionManager $sessionManager */
            $sessionManager = $container->get($sessionManagerClass);
            $sessionManager->start();
        } catch (RuntimeException $e) {
            session_unset();
            $sessionManager = $container->get($sessionManagerClass);
            $sessionManager->start();
        }
        try {
            $authStorage = new SessionStorage('Zend_Auth', 'session', $sessionManager);
        } catch (ServiceNotCreatedException $e) {
            session_unset();
            $authStorage = new SessionStorage('Zend_Auth', 'session', $sessionManager);
        }
        return $authStorage;
    }
}
