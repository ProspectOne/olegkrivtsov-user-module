<?php
namespace ProspectOne\UserModule\Mapper;

use Doctrine\ORM\EntityRepository;
use ProspectOne\UserModule\Entity\User;
use Zend\Hydrator\AbstractHydrator;

/**
 * Class UserMapper
 * @package ProspectOne\UserModule\Mapper
 */
class UserMapper
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var AbstractHydrator
     */
    private $hydrator;

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * @return AbstractHydrator
     */
    public function getHydrator(): AbstractHydrator
    {
        return $this->hydrator;
    }

    /**
     * UserMapper constructor.
     * @param EntityRepository $repository
     * @param AbstractHydrator $hydrator
     */
    public function __construct(EntityRepository $repository, AbstractHydrator $hydrator)
    {
        $this->repository = $repository;
        $this->hydrator = $hydrator;
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        $entities = $this->getRepository()->findBy([], ['id' => 'ASC']);
        $result = array_map([$this->hydrator, "extract"], $entities);
        return $result;
    }

    /**
     * @param $email
     * @return User
     * @throws \Doctrine\ORM\ORMException
     */
    public function findByEmail($email)
    {
        $entity = $this->getRepository()->findOneByEmail($email);
        return $entity;
    }
}
