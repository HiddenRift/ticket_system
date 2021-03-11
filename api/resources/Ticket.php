<?php


namespace SupportTicketManager;




use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . '/PorterStemmer.php';


/** Manages the interaction between the ticket collection in the database and the rest of the application
 * Class Ticket
 * @package SupportTicketManager
 */
class Ticket
{
    const TICKET_STATE_UNATTENDED = 'unattended';
    const TICKET_STATE_IN_PROGRESS = 'in_progress';
    const TICKET_STATE_CANCELLED = 'cancelled';
    const TICKET_STATE_CLOSED = 'closed';

    private $ticketId;
    private $ownerId;
    private $ticketContent;
    private $ticketContentParsed;
    private $timeStamp;
    private $staffOnTicket;
    private $responses;
    private $ticketState;
    private $staffToAppend;
    private $responseToAppend;
    private $ticketCategory;

    /** Retrieves a connection to the database
     * @return \MongoDB\Collection
     */
    private static function dbConn(){
        return (new \MongoDB\Client)->TicketDB->Tickets;
    }

    /** Retrieves all tickets belonging to user with uIs
     * @param ObjectId $uid users primary key ID
     * @return array
     */
    public static function getUsersTickets($uid){
        $limits = ['ownerId' => $uid];
        return self::getSimpleTicketList($limits);
    }

    /** Retrieves tickets that are being attended to by the specified user
     * @param ObjectId $uid
     * @return array
     */
    public static function getTicketsAttendingTo($uid){
        $limits = ['staffOnTicket' => $uid];
        return self::getSimpleTicketList($limits);
    }

    /** Retrieves all tickets with the status Unattended in the system
     * @return array
     */
    public static function getUnattendedTickets(){
        $limits = ['ticketState' => self::TICKET_STATE_UNATTENDED];
        return self::getSimpleTicketList($limits);
    }

    /**
     * Retrieves all tickets in the database for comparison
     * Warning: this function is unused and unimplemented
     */
    public static function getTicketsForComparison(){
        $tickets = self::dbConn();

    }

    /** This function returns a simple ticket list based on varying constraints that can be passed to it.
     * if no constaints are specified retrieves all tickets.
     * @param array $limits
     * @return array
     */
    private static function getSimpleTicketList($limits = null) {
        $tickets = self::dbConn();
        if($limits === null) {
            $cursor = $tickets->find();
        } else {
            $cursor = $tickets->find($limits);
        }
        $result = [];
        foreach ($cursor as $doc) {
            $row = ['id'=> $doc['_id'],
                'ticketContent'=> $doc['ticketContent'],
                'ownerId'=> $doc['ownerId'],
                'timeSubmitted' => $doc['timeStamp'],
                'state' => $doc['ticketState']];
            $result[]= $row;
         }
        return $result;
    }

    /** Stems a string given to create the ticketContentParsed field
     * uses the porter stemmer to accomplish this
     * @param $string
     * @return string
     */
    public static function stemString($string){
        $wordList = explode(' ', $string);
        $exec = function ($word){
            return \PorterStemmer\PorterStemmer::Stem($word);
        };
        $stemmed = array_map($exec, $wordList);
        $string1 = implode(' ', $stemmed);
        return $string1;
    }


    /** Attempts to create a new ticket, returning bool false on failure
     * On succeding returns a new Ticket object that can be manipulated.
     * @param ObjectId $ownerId
     * @param string $ticketContent
     * @return bool|Ticket
     */
    public static function createTicket($ownerId, $ticketContent){
        $data = ['_id'=> new \MongoDB\BSON\ObjectId(),
            'ownerId'=>$ownerId,
            'timeStamp'=>new \MongoDB\BSON\UTCDateTime(),
            'ticketContent'=>$ticketContent,
            'ticketContentParsed'=>self::stemString($ticketContent),
            'staffOnTicket'=>[],
            'ticketState'=>self::TICKET_STATE_UNATTENDED,
            'responses'=>[],
        ];
        $tickets = (new \MongoDB\Client)->TicketDB->Tickets;
        $result = $tickets->insertOne($data);
        if($result->isAcknowledged()) {
            return new self($data['_id'],
                $data['ownerId'],
                $data['ticketContent'],
                $data['ticketContentParsed'],
                $data['timeStamp'],
                $data['staffOnTicket'],
                $data['responses'],
                $data['ticketState']);
        }
        return false;
    }

    /** Retrieves a ticket from the database, returning false on failure
     * @param ObjectId $id
     * @return bool|Ticket
     */
    public static function getTicket($id){
        $tickets = self::dbConn();
        $limits = ['_id' => $id];
        $data = $tickets->findOne($limits);
        if ($data != null){
            $ticketCategory = (isset($data['category']))? $data['category'] : null;
            return new self($data['_id'],
                $data['ownerId'],
                $data['ticketContent'],
                $data['ticketContentParsed'],
                $data['timeStamp'],
                $data['staffOnTicket'],
                $data['responses'],
                $data['ticketState'],
                $ticketCategory);
        }
        return false;
    }


    /**
     * Ticket constructor. to create a ticket,
     * This method should NEVER be called directly
     * @param ObjectId $ticketId
     * @param ObjectId $ownerId
     * @param string $ticketContent
     * @param string $ticketContentParsed
     * @param UTCDateTime $timeStamp
     * @param array $staffOnTicket
     * @param array $responses
     * @param string $ticketState enumeration
     * @param string $ticketCategory
     */
    public function __construct($ticketId, $ownerId, $ticketContent, $ticketContentParsed, $timeStamp, $staffOnTicket, $responses, $ticketState, $ticketCategory = null) {
        $this->ticketId = $ticketId;
        $this->ownerId=$ownerId;
        $this->ticketContent = $ticketContent;
        $this->ticketContentParsed = $ticketContentParsed;
        $this->timeStamp = $timeStamp;
        $this->staffOnTicket = $staffOnTicket;
        $this->responses = $responses;
        $this->ticketState = $ticketState;
        $this->staffToAppend = [];
        $this->responseToAppend = [];
        $this->ticketCategory = $ticketCategory;
    }

    /** Getter for TicketId
     * @return ObjectId
     */
    public function getTicketId()
    {
        return $this->ticketId;
    }

    /** getter for OwnerId
     * @return ObjectId
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /** getter for TicketContent
     * @return string
     */
    public function getTicketContent()
    {
        return $this->ticketContent;
    }

    /** getter for Parsed ticket content
     * @return string
     */
    public function getTicketContentParsed()
    {
        return $this->ticketContentParsed;
    }

    /** getter for time ticket was submitted
     * @return UTCDateTime
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /** setter for the staff members on a project
     * @param ObjectId $staffId
     */
    public function addStaffMember($staffId){
        array_push($this->staffToAppend, $staffId);
    }

    /** appender for a new response
     * @param $staffId
     * @param $responseText
     */
    public function addResponse($staffId, $responseText){
        array_push($this->responseToAppend,(object)['respondeeId'=>$staffId, 'content'=>$responseText, 'timeStamp'=>new \MongoDB\BSON\UTCDateTime()]);
    }

    /** setter for updating ticketState
     * @param $newTicketState
     */
    public function setTicketState($newTicketState):void{
        $this->ticketState = $newTicketState;
    }

    /** setter for TicketCategory
     * @param $ticketCategory
     */
    public function setTicketCategory($ticketCategory):void {
        $this->ticketCategory = $ticketCategory;
    }

    /** attempts to update the database with new information in the class.
     * returns true if changes were made or false if no changes were made or the update failed
     * @return bool
     */
    public function updateDb(){
        $filter = ['_id'=>$this->ticketId];
        $tickets = (new \MongoDB\Client)->TicketDB->Tickets;

        $set = ['ticketState'=>$this->ticketState];

        if ($this->ticketCategory !== null){
            $set['category'] = $this->ticketCategory;
        }

        $data = [
            '$set'=> $set,
            '$push'=>['staffOnTicket'=>['$each'=>$this->staffToAppend],
                      'responses'=>['$each'=>$this->responseToAppend]
            ],
        ];

        $result = $tickets->updateOne($filter,$data);
        if ($result != null && $result->getModifiedCount() === 1){
            $this->staffToAppend = [];
            $this->responseToAppend=[];
            return true;
        }
        return false;
    }

    /** retrieves the state of a ticket
     * @return string
     */
    public function getTicketState(): string
    {
        return $this->ticketState;
    }

    /** retrieves the user object belonging to the owner of the ticket
     * @return bool|User
     */
    public function getOwner(){
        return User::getUserById($this->getOwnerId());
    }

    /** retrieves the staff members of a project as their user objects
     * @return array
     */
    public function getStaff(){
        $callable = function ($_id){
            return User::getUserById($_id);
        };

        $result = array_map($callable,(array) $this->staffOnTicket);
        return $result;
    }

    /** retrieves a list of responses
     * @return array
     */
    public function getResponses(): array
    {
        return (array)$this->responses;
    }

    /** Retrieves a list of all ticket categories from the categories collection
     * @return array
     */
    public static function getTicketCategories(): array {
        $categories = (new \MongoDB\Client)->TicketDB->categories;
        $iter = $categories->find();
        $results = [];
        foreach ($iter as $doc) {
            $results[] = [
                'id' => $doc['_id'],
                'category' => $doc['category']
            ];
        }
        return $results;
    }

    /** Retrieves all tickets except one specified,
     * This is used for the comparison function in manage tickets
     * @param ObjectId $ticketId
     * @return array
     */
    public static function getAllTicketsExcept($ticketId) {
        $limits = ['_id' => ['$ne' => $ticketId]];

        $tickets = self::dbConn();

        $cursor = $tickets->find($limits);

        $result = [];
        foreach ($cursor as $doc) {
            $row = [
                'id'=> $doc['_id'],
                'ticketContent'=> $doc['ticketContent'],
                'ticketContentParsed' => $doc['ticketContentParsed'],
                'ownerId'=> $doc['ownerId'],
                'timeSubmitted' => $doc['timeStamp'],
                'state' => $doc['ticketState']
            ];
            $result[]= $row;
        }
        return $result;
    }

    /** Retrieves a list of staff members attending to the ticket as their ID
     * @return array
     */
    public function getStaffOnTicketIds() :array {
        return (array) $this->staffOnTicket;
    }
}