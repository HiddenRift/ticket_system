<?php


namespace SupportTicketManager;
use MongoDB\BSON\ObjectId;

include_once __DIR__ . '/ViewTickets.php';
include_once __DIR__ . '/Ticket.php';
include_once  __DIR__ . '/PhpComparer.php';

/**Class to manage access to the tickets collection
 * Class ManageTickets
 * @package SupportTicketManager
 */
class ManageTickets
{
    /**
     * Defines the threshold required for the comparer to register a successful match
     * The maning of this value changes with the Comparer used
     */
    const TICKET_MATCH_THRESHOLD = 80;
    /**Create a new ticket  and append it to the collection
     * @param ObjectId $id
     * @param string $content
     * @return array
     */
    public static function createTicket($id, $content) {
        $ticket = Ticket::createTicket($id,$content);
        if($ticket !== false){
            //return success
            return [
                'response' => 200,
                'message' => 'Ticket created',
                'result' => [
                    'content' => $ticket->getTicketContent(),
                    'status' => $ticket->getTicketState(),
                    'time' => (string)$ticket->getTimeStamp()
                ]
            ];
        }else{
            //return error
            return ['response' => 403, 'message' => 'an error has occurred ticket not created'];
        }
    }

    /** Retrieve all unsolved tickets from the ticket collection
     * @return array
     */
    public static function getUnsolvedTickets() {
        $result = Ticket::getUnattendedTickets();
        return [
            'response' => 200,
            'message' => 'Unsolved tickets in system',
            'result' => $result
        ];
    }

    /** Retrieve all the tickets the user is working on
     * @param ObjectId $id
     * @return array
     */
    public static function getTicketsWorkingOn($id){
        $result = Ticket::getTicketsAttendingTo($id);
        return [
            'response' => 200,
            'message' => 'Tickets user is working on',
            'result' => $result
        ];
    }

    /** Close the specified ticket, marking it as resolved
     * @param ObjectId $ticketId
     * @param string $category
     * @return array
     */
    public static function closeTicket($ticketId, $category) {
        $ticket = Ticket::getTicket($ticketId);
        if($ticket === false){
            return ["response" => 404, 'message' => 'Ticket does not exist'];
        }

        $categories = Ticket::getTicketCategories();
        $categoryFound = false;
        foreach ($categories as $row){
            if ($row['category'] == $category){
                $categoryFound = true;
                break;
            }
        }

        if ($categoryFound === false){
            // return category not found error
            return ["response" => 404, 'message' => 'Category does not exist'];
        }

        $ticket->setTicketCategory($category);
        $ticket->setTicketState(Ticket::TICKET_STATE_CLOSED);

        $isInserted = $ticket->updateDb();

        if ($isInserted){
            return [
                'response' => 200,
                'message' => 'Tickets is closed',
                'result' => ['category' => $category]
            ];
        } else{
            return ["response" => 500, 'message' => 'Ticket could not be updated'];
        }

    }

    /**Retrieve the possible categories of all tickets in the system
     * @return array
     */
    public static function ticketCategories(){
        $result = Ticket::getTicketCategories();
        return [
            'response' => 200,
            'message' => 'categories successfully retrieved',
            'result' => $result
        ];
    }

    /**Cancel the ticket specified
     *
     *if the user Id field is specified, checks that the user owns the ticket before deleting it
     * @param ObjectId $id
     * @param ObjectId $userId
     * @return array
     */
    public static function cancelTicket($id, $userId = null) {
        $ticket = Ticket::getTicket($id);

        if($ticket === false){
            return ["response" => 404, 'message' => 'Ticket does not exist'];
        }

        // if need to check user
        if ($userId !== null){
            // assert user matches one on ticket
            if ($ticket->getOwnerId() != $userId)
            {
                return ["response" => 403, 'message' => 'you cannot cancel this ticket'];
            }
        }

        $ticket->setTicketState(Ticket::TICKET_STATE_CANCELLED);
        if($ticket->updateDb()){
            return ["response" => 200, 'message' => 'ticket has been cancelled'];
        }else{
            return ["response" => 500, 'message' => 'Ticket could not be updated'];
        }
    }

    /**Append a new response to a ticket
     * @param ObjectId $ticketId
     * @param string $message
     * @param ObjectId $responderId
     * @param bool $assertRespondeeIsOwner
     * @return array
     */
    public static function respondToTicket($ticketId, $message, $responderId, $assertRespondeeIsOwner = false) {
        $ticket = Ticket::getTicket($ticketId);
        if($ticket === false){
            return ["response" => 404, 'message' => 'Ticket does not exist'];
        }

        if ($assertRespondeeIsOwner === true){
            if ($ticket->getOwnerId() != $responderId){
                return ["response" => 403, 'message' => 'you cannot respond to this ticket'];
            }
        }else{
            // the response is being added by staff so check if theyre already on ticket
            $found = false;
            $staffOnTicket = $ticket->getStaffOnTicketIds();
            foreach ($staffOnTicket as $staff){
                if ($staff == $responderId){
                    $found = true;
                }
            }
            if ($found === false){
                // if found is still false add responder to list
                $ticket->addStaffMember($responderId);
            }
        }

        $ticket->addResponse($responderId, $message);

        $insertResult = $ticket->updateDb();

        if ($insertResult === true){
            return [
                'response' => 200,
                'message' => 'Response successfully added',
                'result' => [
                    'responder' => $responderId,
                    'message' => $message
                ]
            ];
        }else{
            return ['response' => 500, 'message' => 'could not append response, no response added'];
        }
    }

    /**Retrieves a list of similar tickets in the system
     * @param ObjectId $id
     * @return array
     */
    public static function getSimilarTickets($id) {
        $ticket = Ticket::getTicket($id);

        if($ticket === false){
            return ["response" => 404, 'message' => 'Ticket does not exist'];
        }

        //now fetch other tickets;
        $ticketList = Ticket::getAllTicketsExcept($id);
        $similarTickets = [];
        foreach ($ticketList as $compTicket){
            if (PhpComparer::compareStrings($ticket->getTicketContentParsed(), $compTicket['ticketContentParsed']) >= 80){
                $similarTickets[] = $compTicket;
            }
        }

        return [
            'response' => 200,
            'message' => 'Similar tickets retrieved',
            'result' => $similarTickets
        ];
    }


    /**Retrieves details of the ticket associated with the ID provided
     * @param ObjectId $id
     * @return array
     */
    public static function getTicket($id){
        $ticket = Ticket::getTicket($id);
        if($ticket === false){
            return ["response" => 404, 'message' => 'Ticket does not exist'];
        }

        $user = $ticket->getOwner();

        $userRes = [
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'permission' => $user->getUserType(),
            'id' => $user->getId()
        ];

        /** Inline function to transform staff ID's into their details
         * @param User $staff
         * @return array|bool
         */
        $func = function($staff){
            if($staff !== false){
                return [
                    'name' => $staff->getName(),
                    'email' => $staff->getEmail(),
                    'id' => $staff->getId()

                ];
            }else{
                return false;
            }
        };

        $staff = $ticket->getStaff();
        $staffList = array_map($func, $staff);
        $result = [
            'response' => 200,
            'message' => 'Ticket retrieved',
            'result' => [
                'user' => $userRes,
                'content' => $ticket->getTicketContent(),
                'status' => $ticket->getTicketState(),
                'time' => (string)$ticket->getTimeStamp(),
                'responses' => (array) $ticket->getResponses(),
                'staffOn' => $staffList,
                'ticketId' => $ticket->getTicketId()
            ]
        ];
        return $result;
    }

    /**Returns the list of tickets that were created by a user.
     * @param ObjectId $id
     * @return mixed
     */
    public static function getUsersTickets($id) {
        $results  = Ticket::getUsersTickets($id);
        return [
            'response' => 200,
            'message' => 'Ticket list retrieved',
            'results' => $results
        ];
    }
}