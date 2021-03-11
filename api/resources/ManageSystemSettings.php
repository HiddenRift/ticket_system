<?php


namespace SupportTicketManager;
require_once __DIR__ . '/User.php';

use DOMDocument;

/**Class to manage the interaction between the user and the XML file used to store data
 * Class ManageSystemSettings
 * @package SupportTicketManager
 */
class ManageSystemSettings
{
    private $xmlFh = null;
    const CONFIG_FILE = __DIR__ . '/configFile.xml';
    const CONFIG_FILE_SCHEMA = __DIR__ . '/config_schema.xsd';
    const USER_FORM_SCHEMA = __DIR__ . '/user_form_schema.xsd';

    /**
     * ManageSystemSettings constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $dom = new DOMDocument();
        $dom->load(self::CONFIG_FILE);
        $result = $dom->schemaValidate(self::CONFIG_FILE_SCHEMA);
        if($result === false){
            //$ex = new \Exception("XML provided is not valid", 500);
            throw new \Exception("XML provided is not valid", 500);
        }
        $this->xmlFh = simplexml_import_dom($dom);
    }

    /** Sets the values for the  email account to be used to contact users in the system
     * @param string $email
     * @param string $SMTPSvr
     * @param integer $port
     * @param boolean $useAuthentication
     * @param string $encryption
     * @param string $userName
     * @param string $password
     * @return array
     */
    function setEmailAccountDetails($email, $SMTPSvr, $port, $useAuthentication, $encryption, $userName, $password){
        $emailNode = $this->xmlFh->email_settings;
        if($email!==null){
            $emailNode->email_address = $email;
        }
        if($SMTPSvr!==null){
            $emailNode->smtp_server = $SMTPSvr;
        }
        if($port!==null){
            $emailNode->port = $port;
        }
        if($useAuthentication!==null){
            $emailNode->authentication = $useAuthentication;
        }
        if($encryption!==null){
            $emailNode->encryption = $encryption;
        }
        if($userName!==null){
            $emailNode->username = $userName;
        }
        if($password!==null){
            $emailNode->password = $password;
        }

        $dom = dom_import_simplexml($this->xmlFh);

        $domDoc = $dom->ownerDocument;

        $result = $domDoc->schemaValidate(self::CONFIG_FILE_SCHEMA);

        if ($result === false){
            return ["response" => 500, 'message' => 'xml no longer passes schema validation, please check and try again'];
        }

        try {
            $this->xmlFh->saveXML(self::CONFIG_FILE);
            return ["response" => 200, 'message' => 'xml config successfully updated'];

        }catch (\Exception $ex){
            return ["response" => 500, 'message' => 'Unable to save the new xml file, update file permissions and try again'];
        }
    }

    /**
     * @return array
     */
    function getEmailAccountDetails(){
        $emailNode = $this->xmlFh->email_settings;
        $result =  [
            'email' => (string)$emailNode->email_address,
            'SMTPServer' => (string)$emailNode->smtp_server,
            'port' => (integer)$emailNode->port,
            'useAuthentication' => (boolean)$emailNode->authentication,
            'encryption' => (string)$emailNode->encryption,
            'username' => (string)$emailNode->username,
        ];
        return [
            'response' => 200,
            'message' => 'Ticket created',
            'result' => $result
        ];
    }

    /**
     *
     */
    function setDefaultEmailPreferences(){

    }

    /**
     *
     */
    function getDefaultEmailPreferences(){

    }

    /**inserts users described by an valid XML string
     * @param $XMLString
     * @return array
     */
    function bulkInsertUsers($XMLString){
        $dom = new DOMDocument();
        $dom->loadXML($XMLString);
        $result = $dom->schemaValidate(self::USER_FORM_SCHEMA);
        if($result === false){
            //$ex = new \Exception("XML provided is not valid", 500);
           return ["response" => 500, 'message' => 'Unable to save the new xml file, update file permissions and try again'];
        }

        $simpleXMLHandler = simplexml_import_dom($dom);

        $result = $simpleXMLHandler->xpath('//user');
        $count = 0;
        foreach ($result as $aUser){
            $name = $aUser->name;
            $email = $aUser->email;
            $permissions = $aUser->permission;
            $password = $aUser->password;
            $isActive = $aUser->isActive;
            $insertion = User::createUser($name, $permissions, $email, $password, $isActive);

            if ($insertion !== false){
                $count++;
            }
        }
        return [
            'response' => 200,
            'message' => 'Ticket created',
            'result' => ['noInserted' => $count]
        ];
    }

}