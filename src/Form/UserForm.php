<?php
namespace ProspectOne\UserModule\Form;

use Doctrine\ORM\EntityManager;
use ProspectOne\UserModule\Interfaces\UserInterface;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use ProspectOne\UserModule\Validator\UserExistsValidator;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Hostname;

/**
 * This form is used to collect user's email, full name, password and status. The form
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, user
 * enters password, in 'update' scenario he/she doesn't enter password.
 */
class UserForm extends Form implements InputFilterProviderInterface
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
    public function __construct(
        $scenario = 'create',
        EntityManager $entityManager = null,
        UserInterface $user = null,
        $rolesselector = null,
        $rolecurrent = null
    )
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
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    public function init()
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

        // Add "full_name" field
        $this->add([
            'type' => 'text',
            'name' => 'full_name',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'John Doe'
            ],
            'options' => [
                'label' => 'Full Name',
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
    public function getInputFilterSpecification()
    {
        // Add input for "email" field
        $inputFilters = [
            [
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
            ], [
                'name' => 'full_name',
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
            ]
        ];

        if ($this->scenario == 'create') {

            // Add input for "password" field
            $inputFilters = array_merge_recursive($inputFilters, [
                [
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
                ], [
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
                ]
            ]);
        }

        // Add input for "status" field
        return array_merge_recursive($inputFilters, [
            [
                'name' => 'status',
                'required' => true,
                'filters' => [
                    ['name' => 'ToInt'],
                ],
                'validators' => [
                    ['name' => 'InArray', 'options' => ['haystack' => [1, 2]]]
                ]
            ]
        ]);
    }
}
