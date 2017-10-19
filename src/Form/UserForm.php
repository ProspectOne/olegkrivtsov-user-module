<?php
namespace ProspectOne\UserModule\Form;

use Doctrine\ORM\EntityManager;
use ProspectOne\UserModule\Interfaces\UserInterface;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use ProspectOne\UserModule\Validator\UserExistsValidator;
use Zend\Validator\Hostname;

/**
 * This form is used to collect user's email, full name, password and status. The form
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, user
 * enters password, in 'update' scenario he/she doesn't enter password.
 */
class UserForm extends Form
{
    /**
     * Scenario ('create' or 'update').
     * @var string
     */
    private $scenario;

    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager = null;

    /**
     * Current user.
     * @var UserInterface
     */
    private $user = null;

    /**
     * @var mixed
     */
    private $rolesselector;

    /**
     * @var int
     */
    private $rolecurrent;

    /**
     * return string
     */
    protected function getScenario(){
        return $this->scenario;
    }

    /**
     * return EntityManager
     */
    protected function getEntityManager() {
        return $this->entityManager;
    }

    /**
     * @return UserInterface
     */
    protected function getUser() {
        return $this->user;
    }

    /**
     * @return int|null
     */
    protected function getRoleCurrent() {
        return $this->rolecurrent;
    }

    /**
     * @return mixed|null
     */
    protected function getRoleSelector(){
        return $this->rolesselector;
    }


    /**
     * Constructor.
     * @param string $scenario
     * @param EntityManager $entityManager
     * @param UserInterface $user
     * @param mixed $rolesselector
     * @param int $rolecurrent
     */
    public function __construct($scenario = 'create', EntityManager $entityManager = null, UserInterface $user = null, $rolesselector = null, $rolecurrent = null)
    {
        // Define form name
        parent::__construct('user-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->rolesselector = $rolesselector;
        $this->rolecurrent = $rolecurrent;

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
        // Add "email" field
        $this->add([
            'type' => 'text',
            'name' => 'email',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'name@example.com'
            ],
            'options' => [
                'label' => 'E-mail',
            ],
        ]);

        // Add "first_name" field
        $this->add([
            'type' => 'text',
            'name' => 'first_name',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'John'
            ],
            'options' => [
                'label' => 'First Name',
            ],
        ]);

        // Add "last_name" field
        $this->add([
            'type' => 'text',
            'name' => 'last_name',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Doe'
            ],
            'options' => [
                'label' => 'Last Name',
            ],
        ]);

        if ($this->scenario == 'create') {

            // Add "password" field
            $this->add([
                'type' => 'password',
                'name' => 'password',
                'attributes' => [
                    'class' => 'form-control'
                ],
                'options' => [
                    'label' => 'Password',
                ],
            ]);

            // Add "confirm_password" field
            $this->add([
                'type' => 'password',
                'name' => 'confirm_password',
                'attributes' => [
                    'class' => 'form-control'
                ],
                'options' => [
                    'label' => 'Confirm password',
                ],
            ]);
        }

        // Add role field selector here
        $this->add([
            'type' => 'select',
            'name' => 'role',
            'options' => [
                'label' => 'Role',
                'value_options' => $this->rolesselector,

            ],
            'attributes' => [
                'value' => $this->rolecurrent,
                'class' => 'form-control'
            ]
        ]);

        // Add "status" field
        $this->add([
            'type' => 'select',
            'name' => 'status',
            'attributes' => [
                'class' => 'form-control'
            ],
            'options' => [
                'label' => 'Status',
                'value_options' => [
                    1 => 'Active',
                    2 => 'Retired',
                ]
            ],
        ]);

        // Add the Submit button
        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Create',
                'class' => 'btn btn-primary'
            ],
        ]);
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    protected function addInputFilter()
    {
        // Create main input filter
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        // Add input for "email" field
        $inputFilter->add([
            'name' => 'email',
            'required' => true,
            'filters' => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 1,
                        'max' => 128
                    ],
                ],
                [
                    'name' => 'EmailAddress',
                    'options' => [
                        'allow' => Hostname::ALLOW_DNS,
                        'useMxCheck' => false,
                    ],
                ],
                [
                    'name' => UserExistsValidator::class,
                    'options' => [
                        'entityManager' => $this->entityManager,
                        'user' => $this->user
                    ],
                ],
            ],
        ]);

        // Add input for "first_name" field
        $inputFilter->add([
            'name' => 'first_name',
            'required' => true,
            'filters' => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 1,
                        'max' => 512
                    ],
                ],
            ],
        ]);

        // Add input for "last_name" field
        $inputFilter->add([
            'name' => 'last_name',
            'required' => true,
            'filters' => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 1,
                        'max' => 512
                    ],
                ],
            ],
        ]);

        if ($this->scenario == 'create') {

            // Add input for "password" field
            $inputFilter->add([
                'name' => 'password',
                'required' => true,
                'filters' => [
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 6,
                            'max' => 64
                        ],
                    ],
                ],
            ]);

            // Add input for "confirm_password" field
            $inputFilter->add([
                'name' => 'confirm_password',
                'required' => true,
                'filters' => [
                ],
                'validators' => [
                    [
                        'name' => 'Identical',
                        'options' => [
                            'token' => 'password',
                        ],
                    ],
                ],
            ]);
        }

        // Add input for "status" field
        $inputFilter->add([
            'name' => 'status',
            'required' => true,
            'filters' => [
                ['name' => 'ToInt'],
            ],
            'validators' => [
                ['name' => 'InArray', 'options' => ['haystack' => [1, 2]]]
            ],
        ]);
    }
}
