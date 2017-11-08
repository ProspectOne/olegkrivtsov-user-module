<?php
namespace ProspectOne\UserModule\Mapper;

use Doctrine\ORM\EntityRepository;
use Zend\Hydrator\ClassMethods;

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
     * @var ClassMethods
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
     * @return ClassMethods
     */
    public function getHydrator(): ClassMethods
    {
        return $this->hydrator;
    }

    /**
     * UserMapper constructor.
     * @param EntityRepository $repository
     */
    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
        $this->hydrator = new ClassMethods();
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
}
