<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 24/03/19
 * Time: 18:01
 */

namespace SupportTicketManager;
use MongoDB\BSON\ObjectId;

require_once __DIR__ . "/../../vendor/autoload.php";

/** Manages the interaction between the User collection in the database and the rest of the application
 * Class User
 * @package SupportTicketManager
 */
class User{
    public const USER_TYPE_NORMAL = 'normal';
    public const USER_TYPE_STAFF = 'staff';
    public const USER_TYPE_ADMIN = 'admin';
    private $id;
    private $name;
    private $userType;
    private $email;
    private $passwordHash;
    private $isActive;
    private $isRecivingEmails;
    private $userPrefs;


    /** retrieves a connection object and points it towards the Users collection
     * @return \MongoDB\Collection
     */
    private static function dbConn(){
        return (new \MongoDB\Client)->TicketDB->Users;
    }

    /** retrieves a list of Users
     * @return array
     */
    public static function userList(){
        $users = self::dbConn();
        $cursor = $users->find();
        $result = [];
        foreach ($cursor as $doc){
            $row = ['id'=>$doc['_id'],
                'name'=>$doc['name'],
                'email'=>$doc['email'],
                'permission'=>$doc['userType'],
                'isActive'=>$doc['isActive']];
            $result[]= $row;
        }
        return $result;
    }

    /** Creates a user with the given permissions and attributes,
     * Returns a user object if the user could be appended successfully or false if this was not possible
     * Not possible could be due to failing to meet email unique constraint or connection
     * @param string $name
     * @param string $userType
     * @param string $email
     * @param string $rawPassword
     * @param bool $isActive
     * @return bool|User
     */
    public static function createUser($name, $userType, $email, $rawPassword, $isActive){
        $user = (new \MongoDB\Client)->TicketDB->Users;
        $data = ['name'=>$name,
            'email'=>$email,
            'userType'=>$userType,
            'passwordHash'=>self::hashPassword($rawPassword),
            'isActive'=>$isActive,
            'isReceivingEmails'=>true,
            'userPrefs'=>''];
        try {
            $result = $user->insertOne($data);
            if ($result->getInsertedCount() === 1) {
                return self::getUser($email, $rawPassword);
            }
        }catch (\Exception $ex){
            return false;
        }
        return false;
    }

    /** Retrieves a user given by their uniwue email,
     *  returns false on failure or a user object on success
     * @param string $userEmail
     * @return bool|User
     */
    public static  function getUserByEmail($userEmail){
        $user = (new \MongoDB\Client)->TicketDB->Users;
        $query = ['email' => $userEmail];
        $cursor =  $user->findOne($query);
        if($cursor != null){

            return new self($cursor['_id'],
                $cursor['name'],
                $cursor['userType'],
                $cursor['email'],
                $cursor['passwordHash'],
                $cursor['isActive'],
                $cursor['isReceivingEmails'],
                $cursor['userPrefs']
            );
        }
        return false;
    }

    /** Hashes the plaintext password given before it is stored. uses the BCRYPT algorithm
     * returns false on failure
     * @param string $password
     * @return bool|string
     */
    private static function hashPassword($password){
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /** retrieves a user when provided with a email and password.
     * Will return false on failure to find a match or if the found account
     * is deactivated
     * @param $userEmail
     * @param $userPassword
     * @return bool|User
     */
    public static function getUser($userEmail, $userPassword) {
        $user = (new \MongoDB\Client)->TicketDB->Users;
        $query = ['email' => $userEmail];
        $cursor =  $user->findOne($query);
        if($cursor != null){
            //var_dump($cursor->count());
            if ($cursor['isActive'] === true) {
                $pw = $cursor['passwordHash'];
                return password_verify($userPassword, $pw) ? new self($cursor['_id'],
                    $cursor['name'],
                    $cursor['userType'],
                    $cursor['email'],
                    $cursor['passwordHash'],
                    $cursor['isActive'],
                    $cursor['isReceivingEmails'],
                    $cursor['userPrefs']) : false;
            }
        }
        return false;
    }

    /** retrieves a user by their unique ID
     * @param ObjectId $_oid
     * @return bool|User
     */
    public static function getUserById($_oid){
        $user = (new \MongoDB\Client)->TicketDB->Users;
        $query = ['_id' => $_oid];
        $cursor =  $user->findOne($query);
        if($cursor != null) {
            //var_dump($cursor->count());
            return new self($cursor['_id'],
                $cursor['name'],
                $cursor['userType'],
                $cursor['email'],
                $cursor['passwordHash'],
                $cursor['isActive'],
                $cursor['isReceivingEmails'],
                $cursor['userPrefs']);
        }
        return false;
    }

    /**
     * User constructor.
     * this should never be called directly
     * @param ObjectId $id
     * @param string $name
     * @param string $userType
     * @param string $email
     * @param string $passwordHash
     * @param boolean $isActive
     * @param boolean $isRecivingEmails
     * @param string $userPrefs
     */
    private function __construct($id, $name, $userType, $email, $passwordHash, $isActive, $isRecivingEmails, $userPrefs)
    {
        $this->id = $id;
        $this->name = $name;
        $this->userType = $userType;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->isActive = $isActive;
        $this->isRecivingEmails = $isRecivingEmails;
        $this->userPrefs=$userPrefs;
    }


    /** getter for the users ID
     * @return ObjectId
     */
    public function getId() {
        return $this->id;
    }

    /** getter for users name
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /** getter for the users type
     * @return string
     */
    public function getUserType() {
        return $this->userType;
    }

    /**
     * @return string getter for the users email
     */
    public function getEmail() {
        return $this->email;
    }

    /** getter for the hashed version of the password
     * @return string
     */
    public function getPasswordHash() {
        return $this->passwordHash;
    }

    /**
     * Disables the currenly inked account
     */
    public function disableAccount() {
        $this->isActive = false;
    }

    /**
     * enables the currently linked account
     */
    public function enableAccount() {
        $this->isActive = true;
    }

    /**updates the database and returns whether or not the operation has been successful
     * @return bool
     */
    public function updateDb() {
        $user = (new \MongoDB\Client)->TicketDB->Users;
        $recordToUpdate = ['_id'=>$this->id];
        $data = ['$set'=>['name'=>$this->name,
            'email'=>$this->email,
            'userType'=>$this->userType,
            'passwordHash'=>$this->passwordHash,
            'isActive'=>$this->isActive,
            'isReceivingEmails'=>$this->isRecivingEmails,
            'userPrefs'=>$this->userPrefs]];
        try {
            $result = $user->updateOne($recordToUpdate, $data);

            if ($result->getModifiedCount() === 1) {
                return true;
            }
        }catch (\Exception $ex) {
            return false;
        }
        return false;
    }

    /** sets a new name for the user
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /** sets a new user type for the permissions
     * @param string $userType Enum from class
     */
    public function setUserType($userType): void
    {
        $this->userType = $userType;
    }

    /** sets a new email for the user
     * @param string $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**hashes new password ans stores ready for update
     * @param string $passwordHash
     */
    public function setPasswordHash($passwordHash): void
    {
        $this->passwordHash = self::hashPassword($passwordHash);
    }

    /** returns the user preferences string
     * @return string
     */
    public function getUserPrefs()
    {
        return $this->userPrefs;
    }

    /** Setter for the user preferences string
     * @param string $userPrefs should be a json string that can be utilized by front end interface where applicable
     */
    public function setUserPrefs($userPrefs): void
    {
        $this->userPrefs = $userPrefs;
    }

    /** Returns the active status of the associated account
     * @return bool
     */
    public function isActive():bool {
        return $this->isActive;
    }

};