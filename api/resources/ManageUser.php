<?php


namespace SupportTicketManager;
use MongoDB\BSON\ObjectId;

include_once  __DIR__ . '/User.php';

/** Manages the communication between endpoint API files and the User interface class
 * Provides a layer of abstraction that returns results as arrays for each query
 * Class ManageUser
 * @package SupportTicketManager
 */
class ManageUser
{
    /**Creates a new user and appends them to the database
     * values passed in should already be valid and must be of correct type
     * @param string $email
     * @param string $name
     * @param string $password
     * @param string $permission
     * @param boolean $isActive
     * @return array
     */
    public static function addUser($email, $name, $password, $permission, $isActive): array {
        $user = User::createUser($name, $permission, $email,$password, $isActive);

        if ($user === false){
            return [
                'response' => 500,
                'message' => 'No User Created',
            ];
        } else {
            $result = ['name' => $user->getName(),
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'permission' => $user->getUserType()];

            return [
                'response' => 200,
                'message' => 'User created',
                'result' => $result
            ];
        }
    }

    /** Changes the isActive property of a user to false
     * Denies a user access to the system in performing this action
     * @param ObjectId $uid
     * @return array
     */
    public static function removeUser($uid){
        $user = User::getUserById($uid);
        if ($user === false){
            return [
                'response' => 404,
                'message' => 'User does not exist',
            ];
        }else {
            $user->disableAccount();
            $result = $user->updateDb();
            if($result === true){
                return [
                    'response' => 200,
                    'message' => 'User Account Disabled',
                ];
            }else{
                return [
                    'response' => 500,
                    'message' => 'User Account could not be disabled/or is already disabled',
                ];
            }

        }
    }

    /** Updates the password of the user to a new one
     * @param ObjectId $uid
     * @param string $newPassword
     * @return array
     */
    public static function changePassword($uid, $newPassword){
        $user = User::getUserById($uid);
        if ($user === false){
            return [
                'response' => 404,
                'message' => 'User does not exist',
            ];
        }else{
            $user->setPasswordHash($newPassword);
            $result =  $user->updateDb();
            if($result === true){
                return [
                    'response' => 200,
                    'message' => 'User password updated',
                ];
            }else{
                return [
                    'response' => 500,
                    'message' => 'Updated password could not be added',
                ];
            }
        }
    }

    /** Retrieves a list of all users currently in the system
     * @return array
     */
    public static function getUserList(){
        $result = User::userList();
        return [
            'response' => 200,
            'message' => 'User list',
            'result' => $result
        ];
    }

    /** Edits the user specified by the User ID
     * Any fields that are not specified to be changed should be set to null
     * @param ObjectId $uid
     * @param string $name
     * @param string $email
     * @param bool $permission
     * @param string $password
     * @param bool $isActive
     * @return array
     */
    public static function editUserDetails($uid, $name, $email, $permission, $password, $isActive){
        $user = User::getUserById($uid);
        if ($user === false){
            return [
                'response' => 404,
                'message' => 'User does not exist',
            ];
        }
        // create empty list
        $result = [];
        // append to it any change fields
        if($name!== null){
            $user->setName($name);
            $result[] = ['name' => $name];
        }
        if ($email!== null){
            $user->setEmail($email);
            $result[] = ['email' => $email];
        }
        if ($permission!== null){
            $user->setUserType($permission);
            $result[] = ['permission' => $permission];
        }
        if ($password!== null){
            $user->setPasswordHash($password);
            $result[] = ['passwordChanged' => true];
        }
        if ($isActive!==null){
            $isActive? $user->enableAccount():$user->disableAccount() ;
            $result[] = ['isActive' => $isActive];
        }
        $queryResult = $user->updateDb();
        if ($queryResult === false){
            // must be a fault updating email
            return [
                'response' => 500,
                'message' => 'An error occurred updating the user',
            ];
        }else{
            // must be true
            return [
                'response' => 200,
                'message' => 'user successfully updated',
                'result' => $result
            ];
        }
    }

    /** Retrieves the details of a user by their ID
     * @param ObjectId $userId
     * @return array
     */
    public static function getUserDetails($userId){
        $user = User::getUserById($userId);
        if ($user === false){
            return [
                'response' => 404,
                'message' => 'User does not exist',
            ];
        }
        return[
            'response' => 200,
            'message' => 'user successfully updated',
            'result' => [
                'user' => $user->getName(),
                'email' => $user->getEmail(),
                'permissions' => $user->getUserType(),
                'isActive' => $user->isActive(),
                'isRecivingEmails' => $user->getUserPrefs()
            ]
        ];
    }

    /** Retrieves the details of a user by their email
     * @param string $email
     * @return array
     */
    public static function getUserDetailsByEmail($email){
        $user = User::getUserByEmail($email);
        if ($user === false){
            return [
                'response' => 404,
                'message' => 'User does not exist',
            ];
        }
        return[
            'response' => 200,
            'message' => 'user successfully updated',
            'result' => [
                'user' => $user->getName(),
                'email' => $user->getEmail(),
                'permissions' => $user->getUserType(),
                'isActive' => $user->isActive(),
                'isRecivingEmails' => $user->getUserPrefs()
            ]
        ];
    }
}