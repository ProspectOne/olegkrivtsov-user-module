<?php
namespace ProspectOne\UserModule\Service;

use ProspectOne\UserModule\Interfaces\RoleInterface;
use ProspectOne\UserModule\Interfaces\UserInterface;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;
use Doctrine\ORM\EntityManager;

/**
 * This service is responsible for adding/editing users
 * and changing user password.
 */
class UserManager
{
    const ADMIN_ROLE_ID = 2;
    const ADMIN_EMAIL = 'admin@example.com';
    const ADMIN_NAME = 'Admin';
    const ADMIN_PASSWORD = 'Secur1ty';
    const TOKEN_SIZE = 16;

    /**
     * @var string
     */
    public $userEntityClassName;

    /**
     * @var string
     */
    public $roleEntityClassName;

    /**
     * Doctrine entity manager.
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Bcrypt
     */
    private $bcrypt;

    /**
	  * @return EntityManager
	  */
    public function getEntityManager(): EntityManager
	{
	    return $this->entityManager;
	}

	/**
	* @return Bcrypt
	*/
	public function getBcrypt(): Bcrypt
	{
 	    return $this->bcrypt;
	}

    /**
     * @return string
     */
	public function getRoleEntityClassName()
    {
        return $this->roleEntityClassName;
    }

    /**
     * UserManager constructor.
     * @param EntityManager $entityManager
     * @param Bcrypt $bcrypt
     * @param string $userEntityClassName
     * @param string $roleEntityClassName
     */
    public function __construct(EntityManager $entityManager, Bcrypt $bcrypt, $userEntityClassName, $roleEntityClassName)
    {
        $this->entityManager = $entityManager;
        $this->bcrypt = $bcrypt;
        $this->userEntityClassName = $userEntityClassName;
        $this->roleEntityClassName = $roleEntityClassName;
    }

    /**
     * This method adds a new user.
     * @param mixed $data
     * @return UserInterface
     * @throws \Exception
     */
    public function addUser($data) 
    {
        // Do not allow several users with the same email address.
        if($this->checkUserExists($data['email'])) {
            throw new \Exception("User with email address " . $data['$email'] . " already exists");
        }
        
        // Create new User entity.
        /** @var UserInterface $user */
        $user = new $this->userEntityClassName();
        $user->setEmail($data['email']);
        $user->setFullName($data['full_name']);

        // Get role object based on role Id from form
        /** @var RoleInterface $role */
        $role = $this->entityManager->find($this->getRoleEntityClassName(), $data['role']);
        // Set role to user
        $user->addRole($role);

        // Encrypt password and store the password in encrypted state.
        $passwordHash = $this->bcrypt->create($data['password']);
        $user->setPassword($passwordHash);
        
        $user->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $user->setDateCreated($currentDate);        

        // Add the entity to the entity manager.
        $this->entityManager->persist($user);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $user;
    }

    /**
     * This method updates data of an existing user.
     * @param UserInterface $user
     * @param mixed $data
     * @return bool
     * @throws \Exception
     */
    public function updateUser(UserInterface $user, $data)
    {
        // Do not allow to change user email if another user with such email already exits.
        if($user->getEmail()!=$data['email'] && $this->checkUserExists($data['email'])) {
            throw new \Exception("Another user with email address " . $data['email'] . " already exists");
        }

        if (!($user instanceof UserInterface)) {
            throw new \LogicException("Only instances of UserInterface should be passed");
        }

        $user->setEmail($data['email']);
        $user->setFullName($data['full_name']);
        $user->setStatus($data['status']);

        // Get role object based on role Id from form
        /** @var RoleInterface $role */
        $role = $this->entityManager->find($this->getRoleEntityClassName(), $data['role']);
        // Set role to user
        $user->addRole($role);

        // Apply changes to database.
        $this->entityManager->flush();

        return true;
    }
    
    /**
     * This method checks if at least one user presents, and if not, creates 
     * 'Admin' user with email 'admin@example.com' and password 'Secur1ty'. 
     */
    public function createAdminUserIfNotExists()
    {
        $user = $this->entityManager->getRepository($this->userEntityClassName)->findOneBy([]);
        if ($user==null) {
            /** @var UserInterface $user */
            $user = new $this->userEntityClassName();
            $user->setEmail(self::ADMIN_EMAIL);
            $user->setFullName(static::ADMIN_NAME);
            $passwordHash = $this->bcrypt->create(self::ADMIN_PASSWORD);
            $user->setPassword($passwordHash);
            $user->setStatus($user->getStatusActive());
            $user->setDateCreated(date('Y-m-d H:i:s'));
            // Get role object based on role Id from form
            /** @var RoleInterface $role */
            $role = $this->entityManager->find($this->getRoleEntityClassName(), self::ADMIN_ROLE_ID);
            // Set role to user
            $user->addRole($role);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    /**
     * @param string $email
     * @param string[] $roles
     * @return bool
     */
    public function hasRole($email, $roles)
    {
        /** @var UserInterface $user */
        $user = $this->getUserByEmail($email);

        return in_array($user->getRoleName(),$roles, true);
    }

    /**
     * Checks whether an active user with given email address already exists in the database.
     * @param string $email
     * @return bool
     */
    public function checkUserExists(string $email)
    {
        return !empty($this->getUserByEmail($email));
    }

    /**
     * Checks that the given password is correct.
     * @param UserInterface $user
     * @param $password
     * @return bool
     */
    public function validatePassword(UserInterface $user, $password)
    {
        $passwordHash = $user->getPassword();
        
        if ($this->bcrypt->verify($password, $passwordHash)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Generates a password reset token for the user. This token is then stored in database and 
     * sent to the user's E-mail address. When the user clicks the link in E-mail message, he is 
     * directed to the Set Password page.
     * @param UserInterface $user
     */
    public function generatePasswordResetToken(UserInterface $user)
    {
        // Generate a token.
        $token = Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz');
        $user->setPasswordResetToken($token);
        
        $currentDate = date('Y-m-d H:i:s');
        $user->setPasswordResetTokenCreationDate($currentDate);  
        
        $this->entityManager->flush();

        $httpHost = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
        $passwordResetUrl = 'http://' . $httpHost . '/set-password/' . $token;

        $this->sendMail($user->getEmail(),$passwordResetUrl);
    }

    /**
     * @param string $usermail
     * @param string $passwordResetUrl
     */
    protected function sendMail($usermail, $passwordResetUrl)
    {
        $subject = 'Password Reset';
        $body = 'Please follow the link below to reset your password:\n';
        $body .= "$passwordResetUrl\n";
        $body .= "If you haven't asked to reset your password, please ignore this message.\n";
        mail($usermail, $subject, $body);
    }

    /**
     * Checks whether the given password reset token is a valid one.
     * @param string $passwordResetToken
     * @return bool
     */
    public function validatePasswordResetToken($passwordResetToken)
    {
        /** @var UserInterface $user */
        $user = $this->getUserByPasswordResetToken($passwordResetToken);
        
        if($user==null) {
            return false;
        }
        
        $tokenCreationDate = $user->getPasswordResetTokenCreationDate();
        $tokenCreationDate = strtotime($tokenCreationDate);
        
        $currentDate = strtotime('now');
        
        if ($currentDate - $tokenCreationDate > 24*60*60) {
            return false; // expired
        }
        
        return true;
    }

    /**
     * Find user by password reset token
     * @param string $passwordResetToken
     * @return mixed
     */
    public function getUserByPasswordResetToken(string $passwordResetToken)
    {
        return $this->entityManager->getRepository($this->userEntityClassName)
            ->findOneByPasswordResetToken($passwordResetToken);
    }

    /**
     * Find user by Email
     * @param string $email
     * @return mixed
     */
    public function getUserByEmail(string $email)
    {
        return $this->entityManager->getRepository($this->userEntityClassName)
            ->findOneByEmail($email);
    }

    /**
     * This method sets new password by password reset token.
     * @param string $passwordResetToken
     * @param string $newPassword
     * @return bool
     */
    public function setNewPasswordByToken($passwordResetToken, $newPassword)
    {
        if (!$this->validatePasswordResetToken($passwordResetToken)) {
           return false; 
        }
        
        $user = $this->getUserByPasswordResetToken($passwordResetToken);
        
        if ($user===null) {
            return false;
        }
                
        // Set new password for user
        $passwordHash = $this->bcrypt->create($newPassword);
        $user->setPassword($passwordHash);
                
        // Remove password reset token
        $user->setPasswordResetToken(null);
        $user->setPasswordResetTokenCreationDate(null);
        
        $this->entityManager->flush();
        
        return true;
    }
    
    /**
     * This method is used to change the password for the given user. To change the password,
     * one must know the old password.
     *
     * @param UserInterface $user
     * @param $data
     * @return bool
     */
    public function changePassword(UserInterface $user, $data)
    {
        $newPassword = $data['new_password'];
        
        // Check password length
        if (strlen($newPassword)<6 || strlen($newPassword)>64) {
            return false;
        }
        
        // Set new password for user
        $passwordHash = $this->bcrypt->create($newPassword);
        $user->setPassword($passwordHash);
        
        // Apply changes
        $this->entityManager->flush();

        return true;
    }

    /**
     * @param UserInterface $user
     * @param string $token
     */
    public function updateToken(UserInterface $user, string $token)
    {
        $user->setToken($token);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @return string
     */
    public function generateToken()
    {
        $token = bin2hex(random_bytes(self::TOKEN_SIZE));
        return $token;
    }
}
