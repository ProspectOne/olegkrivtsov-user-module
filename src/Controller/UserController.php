<?php
namespace ProspectOne\UserModule\Controller;

use Doctrine\ORM\EntityManager;
use ProspectOne\UserModule\Entity\Role;
use ProspectOne\UserModule\Interfaces\UserInterface;
use ProspectOne\UserModule\Service\UserManager;
use Zend\Form\FormInterface;
use Zend\Hydrator\ClassMethods;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use ProspectOne\UserModule\Form\UserForm;
use ProspectOne\UserModule\Form\PasswordChangeForm;
use ProspectOne\UserModule\Form\PasswordResetForm;

/**
 * Class UserController
 * @package ProspectOne\UserModule\Controller
 */
class UserController extends AbstractActionController
{
    const GUEST_ROLE_ID = 1;
    const ERROR_PASSWORD_LENGTH = 'Sorry, password length must be more then 6 and less then 64 digits';

    /**
     * @var mixed
     */
    public $userEntityClassName;

    /**
     * @var string
     */
    public $roleEntityClassName;

    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * User manager.
     * @var \ProspectOne\UserModule\Service\UserManager
     */
    private $userManager;

    /**
     * @var ServiceLocatorInterface
     */
    private $container;

    /**
     * @var int
     */
    private $userRoleId;

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @return UserManager
     */
    protected function getUserManager(): UserManager
    {
        return $this->userManager;
    }

    /**
     * @return ServiceLocatorInterface
     */
    protected function getContainer(): ServiceLocatorInterface
    {
        return $this->container;
    }

    /**
     * @return int
     */
    public function getUserRoleId(): int
    {
        return $this->userRoleId;
    }

    /**
     * @return string
     */
    public function getRoleEntityClassName(): string
    {
        return $this->roleEntityClassName;
    }

    /**
     * Constructor.
     * @param EntityManager $entityManager
     * @param UserManager $userManager
     * @param ServiceLocatorInterface $container
     * @param int $userRoleId
     */
    public function __construct(EntityManager $entityManager, UserManager $userManager, ServiceLocatorInterface $container, int $userRoleId)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->container = $container;
        $config = $this->container->get("Config");
        $this->userEntityClassName = $config['ProspectOne\UserModule']['userEntity'];
        $this->roleEntityClassName = $config['ProspectOne\UserModule']['roleEntity'];
        $this->userRoleId = $userRoleId;
    }

    /**
     * This is the default "index" action of the controller. It displays the
     * list of users.
     */
    public function indexAction()
    {
        $users = $this->entityManager->getRepository($this->userEntityClassName)
            ->findBy([], ['id' => 'ASC']);

        return new ViewModel([
            'users' => $users
        ]);
    }

    /**
     * This action displays a page allowing to add a new user.
     */
    public function addAction()
    {
        $rolesselector = $this->getRolesSelector();

        // Create user form
        $form = $this->container->build(UserForm::class, ['create', $this->entityManager, null, $rolesselector, $this->getUserRoleId()]);

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if ($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Add user.
                $user = $this->userManager->addUser($data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('users',
                    ['action' => 'view', 'id' => $user->getId()]);
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    /**
     * The "view" action displays a page allowing to view user's details.
     */
    public function viewAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id < 1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Find a user with such ID.
        $user = $this->getUserById($id);

        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new ViewModel([
            'user' => $user
        ]);
    }

    /**
     * The "edit" action displays a page allowing to edit user.
     */
    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id < 1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        /** @var UserInterface $user */
        $user = $this->getUserById($id);

        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $rolesselector = $this->getRolesSelector();

        $rolecurrent = $this->getUserRole($user);

        // Create user form
        $form = $this->container->build(UserForm::class, ['update', $this->entityManager, $user, $rolesselector, $rolecurrent]);

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if ($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Update the user.
                $this->userManager->updateUser($user, $data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('users',
                    ['action' => 'view', 'id' => $user->getId()]);
            }
        } else {
            $this->setFormData($form, $user);
        }

        return new ViewModel(array(
            'user' => $user,
            'form' => $form
        ));
    }

    /**
     * @param $id
     * @return null|object
     */
    protected function getUserById($id)
    {
        $user = $this->entityManager->getRepository($this->userEntityClassName)->find($id);
        return $user;
    }

    /**
     * This action displays a page allowing to change user's password.
     */
    public function changePasswordAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id < 1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $user = $this->getUserById($id);

        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Create "change password" form
        $form = new PasswordChangeForm('change');

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if ($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Try to change password.
                if (!$this->userManager->changePassword($user, $data)) {
                    $this->flashMessenger()->addErrorMessage(self::ERROR_PASSWORD_LENGTH);
                } else {
                    $this->flashMessenger()->addSuccessMessage(
                        'Changed the password successfully.');
                }

                // Redirect to "view" page
                return $this->redirect()->toRoute('users',
                    ['action' => 'view', 'id' => $user->getId()]);
            }
        }

        return new ViewModel([
            'user' => $user,
            'form' => $form
        ]);
    }

    /**
     * This action displays the "Reset Password" page.
     */
    public function resetPasswordAction()
    {
        // Create form
        $form = new PasswordResetForm();

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if ($form->isValid()) {

                // Look for the user with such email.
                $user = $this->entityManager->getRepository($this->userEntityClassName)
                    ->findOneByEmail($data['email']);
                if ($user != null) {
                    // Generate a new password for user and send an E-mail 
                    // notification about that.
                    $this->userManager->generatePasswordResetToken($user);

                    // Redirect to "message" page
                    return $this->redirect()->toRoute('users',
                        ['action' => 'message', 'id' => 'sent']);
                } else {
                    return $this->redirect()->toRoute('users',
                        ['action' => 'message', 'id' => 'invalid-email']);
                }
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    /**
     * This action displays an informational message page.
     * For example "Your password has been resetted" and so on.
     */
    public function messageAction()
    {
        // Get message ID from route.
        $id = (string)$this->params()->fromRoute('id');

        // Validate input argument.
        if ($id != 'invalid-email' && $id != 'sent' && $id != 'set' && $id != 'failed') {
            throw new \Exception('Invalid message ID specified');
        }

        return new ViewModel([
            'id' => $id
        ]);
    }

    /**
     * This action displays the "Reset Password" page.
     */
    public function setPasswordAction()
    {
        $token = $this->params()->fromRoute('token', null);

        // Validate token length
        if ($token != null && (!is_string($token) || strlen($token) != 32)) {
            throw new \Exception('Invalid token type or length');
        }

        if ($token === null || !$this->userManager->validatePasswordResetToken($token) ) {
            return $this->redirect()->toRoute('users',
                ['action' => 'message', 'id' => 'failed']);
        }
        $user = $this->userManager->getUserByPasswordResetToken($token);
        // Create form
        $form = new PasswordChangeForm('reset');

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if ($form->isValid()) {

                $data = $form->getData();

                // Set new password for the user.
                if ($this->userManager->setNewPasswordByToken($token, $data['new_password'])) {

                    // Redirect to "message" page
                    return $this->redirect()->toRoute('users',
                        ['action' => 'message', 'id' => 'set']);
                } else {
                    // Redirect to "message" page
                    return $this->redirect()->toRoute('users',
                        ['action' => 'message', 'id' => 'failed']);
                }
            }
        }

        return new ViewModel([
            'form' => $form,
            'user' => $user
        ]);
    }

    /**
     * @return mixed
     */
    public function getRolesSelector()
    {
        $roles = $this->entityManager->getRepository($this->getRoleEntityClassName())->findAll();
        $hydrator = new ClassMethods();
        $rolesselector = [];
        foreach ($roles as $role) {
            $rolesarr = $hydrator->extract($role);
            $rolesselector[$rolesarr['role_id']] = $rolesarr['role_name'];
        }
        ksort($rolesselector);

        return $rolesselector;
    }

    /**
     * @param UserInterface $user
     * @return int
     */
    public function getUserRole($user)
    {
        // checking for existing role if editing mode
        $rolecurrent['role_id'] = $this->getUserRoleId();
        $hydrator = new ClassMethods();
        $role = $user->getRole();
        if (!empty($role)) {
            $rolecurrent = $hydrator->extract($role);
        }

        return $rolecurrent['role_id'];
    }

    /**
     * @return JsonModel
     */
    public function userTokenAction()
    {
        $result = $this->userManager->generateToken();
        return new JsonModel(['result' => $result]);
    }

    /**
     * Used to override form fields
     *
     * @param FormInterface $form
     * @param UserInterface $user
     */
    protected function setFormData(FormInterface $form, UserInterface $user)
    {
        $form->setData(array(
            'full_name' => $user->getFullName(),
            'email' => $user->getEmail(),
            'status' => $user->getStatus(),
        ));
    }

    /**
     * @return \Zend\Http\Response
     */
    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        $user = $this->getUserById($id);
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return $this->redirect()->toRoute("users");
    }
}


