<?php
namespace ProspectOne\UserModule\Model;

use Zend\Cache\Storage\StorageInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ConfigInterface;
use Zend\Session\Exception\RuntimeException;
use Zend\Session\SaveHandler\SaveHandlerInterface;
use Zend\Session\SessionManager;

/**
 * Class DisabledSessionManager
 * @package ProspectOne\UserModule\Model
 */
class DisabledSessionManager extends SessionManager
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    private $data;

    /**
     * Constructor
     *
     * @param  ConfigInterface|null $config
     * @param  StorageInterface|null $storage
     * @param  SaveHandlerInterface|null $saveHandler
     * @param  array $validators
     * @param  array $options
     * @throws RuntimeException
     */
    public function __construct(
        ConfigInterface $config = null,
        StorageInterface $storage = null,
        SaveHandlerInterface $saveHandler = null,
        array $validators = [],
        array $options = []
    ) {
    }

    /**
     * Does a session exist and is it currently active?
     *
     * @return bool
     */
    public function sessionExists()
    {
        return false;
    }

    /**
     * @param bool $preserveStorage
     */
    public function start($preserveStorage = false)
    {
        return;
    }

    /**
     * @param array|null $options
     */
    public function destroy(array $options = null)
    {
        return;
    }

    /**
     * @param string $name
     * @return DisabledSessionManager
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (empty($this->name)) {
            $this->name = md5(rand());
        }
        return $this->name;
    }

    /**
     * @param string $id
     * @return DisabledSessionManager
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        if (empty($this->id)) {
            $this->id = md5(rand());
        }
        return $this->id;
    }

    /**
     * @param bool $deleteOldSession
     * @return DisabledSessionManager
     */
    public function regenerateId($deleteOldSession = true)
    {
        if ($deleteOldSession) {
            $this->data = [];
        }
        $this->id = md5(rand());
        return $this;
    }

    /**
     * @param null $ttl
     * @return DisabledSessionManager
     */
    public function rememberMe($ttl = null)
    {
        return $this;
    }

    /**
     * @return DisabledSessionManager
     */
    public function forgetMe()
    {
        return $this;
    }

    /**
     * @param EventManagerInterface $chain
     * @return DisabledSessionManager
     */
    public function setValidatorChain(EventManagerInterface $chain)
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getValidatorChain()
    {
        return [];
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     *
     */
    public function expireSessionCookie()
    {
        return;
    }
}
