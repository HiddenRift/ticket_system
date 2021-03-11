<?php

namespace SupportTicketManager;

use MongoDB\BSON\ObjectId;


require_once  __DIR__ . '/ManageTickets.php';
require_once __DIR__ . '/User.php';

/** Abstraction of th Manage User class for performing poerations in the context of the logged in user
 * Class ViewTickets
 * @package SupportTicketManager
 */
class ViewTickets
{
    private $user;

    /** creates a new instance of the class for the passed in user
     * ViewTickets constructor.
     * @param User $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /** retrieves the ticket list belonging to the user
     * @return array
     */
    public function getOwnTicketList(){
        return ManageTickets::getUsersTickets($this->user->getId());
    }

    /** creates a new ticket with the given content in the users name
     * @param $content
     * @return array
     */
    public function createTicket($content){
        return ManageTickets::createTicket($this->user->getId(), $content);
    }

    /** cancels a ticket after checking the ticket belongs to the user
     * @param ObjectId $ticketId
     * @return array
     */
    public function cancelOwnTicket($ticketId){
        return ManageTickets::cancelTicket($ticketId, $this->user->getId());
    }

    /** returns the content of a ticket after checking it belongs to the logged in user
     * @param ObjectId $id
     * @return array
     */
    public function viewOwnTicket($id){
        $responseCandidate = ManageTickets::getTicket($id);
        if ($responseCandidate['response'] !== 404 &&
            ($responseCandidate['result']['user']['id'] == $this->user->getId())){
            return $responseCandidate;
        }else{
            return ["response" => 403, 'message' => 'You are not authorized to access this ticket'];
        }

    }

    /** Allows the logged in user to reply to one of their own tickets
     * @param ObjectId $ticketId
     * @param string $message
     * @return array
     */
    public function respondToOwnTicket($ticketId, $message){
        return ManageTickets::respondToTicket($ticketId, $message, $this->user->getId(), true);
    }


}