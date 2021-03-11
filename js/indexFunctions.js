const API_DIR = "api";

const RESPONSE_CODE_ACCESS_DENIED = 403;
const RESPONSE_CODE_SUCCESS = 200;

const USER_TYPE_NORMAL = 'normal';
const USER_TYPE_STAFF = 'staff';
const USER_TYPE_ADMIN = 'admin';

const CSS_HIDE = 'hide';

/// e variables represent ids of pages in the html
const E_LOGINPAGE = 'loginPage';
const E_HOMEPAGE = 'userHomePage';
const E_NEW_TICKET = 'createTicket';
const E_VIEW_MY_TICKETS = 'viewMyTickets';
const E_PREFERENCES = 'preferences';
const E_VIEW_UNATTENDED_TICKETS = 'staffViewUnattendedTickets';
const E_VIEW_TICKETS_ATTENDING_TO = 'staffViewTicketsServicing';
const E_ADD_USER = 'addUser';
const E_VIEW_USERS = 'viewUsers';
const E_EDIT_USER = 'editUser';
const E_SYSTEM_SETTINGS = '';


const ticketAPI = {
    login : API_DIR + "/login.php", // POST
    logout: API_DIR + "/logout.php", // GET/POST
    user_new_ticket: API_DIR + "/new_user_ticket.php", // POST
    user_view_tickets: API_DIR + "/view_my_tickets.php",
    view_my_ticket: API_DIR + "/view_my_ticket.php", // POST
    create_user: API_DIR + "/new_user.php", // POST
    get_user_list: API_DIR + "/user_list.php", // GET/POST
    remove_user: API_DIR + "/remove_user.php", // POST
    edit_user: API_DIR + "/edit_user.php", // POST
    cancel_ticket: API_DIR + "/cancel_ticket.php", // POST
    close_ticket: API_DIR + "/close_ticket.php", // POST
    get_category_list: API_DIR+"/get_category_list.php", // GET
    get_similar_ticket: API_DIR+"/get_similar_tickets.php", // POST
    respond_to_ticket: API_DIR+"/respond_to_ticket.php", // POST
    view_ticket:API_DIR+"/view_ticket.php", // POST
    view_tickets_working_on:API_DIR+"/view_tickets_working_on.php", // GET
    view_tickets_unresolved:API_DIR+"/view_unresolved_tickets.php", // GET
    view_user: API_DIR+ "view_user.php" // POST
};

const FETCH_GET = {
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    method: 'GET',
    credentials: "same-origin"
};

const FETCH_POST_TEMPLATE = {
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    method: 'POST',
    credentials: "same-origin",
};

function login() {

    console.log("logging in now");
    const email = document.getElementById('loginEmail').value;
    const pass = document.getElementById('loginPassword').value;

    const input = new Map([
        ['email', email],
        ['password', pass]
    ]);
    const query = create_query_string(input);
    console.log(query);
    const xhttp = new XMLHttpRequest();
    xhttp.open('POST', ticketAPI.login, true);

    xhttp.onreadystatechange = function() {
        console.log(this.readyState);
        console.log(this.status);
        if (this.readyState === 4 && this.status === 200) {
            console.log(this.responseText);
            //document.getElementById('response').value = this.responseText;
            const responseJSON = JSON.parse(this.responseText);
            if (responseJSON.response === RESPONSE_CODE_ACCESS_DENIED) {
                //output error
                document.getElementById('passwordWarning').innerText = "Error: username + password do not match";
            } else{
                document.getElementById('passwordWarning').innerText = "";
                // hide login page
                document.getElementById(E_LOGINPAGE).classList.add(CSS_HIDE);
                //move to home and update permissions
                document.getElementById(E_HOMEPAGE).classList.remove(CSS_HIDE);
            }
        }
    };
    //xhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhttp.send(query);
}

function logout(){
    const xhttp = new XMLHttpRequest();
    xhttp.open('GET', ticketAPI.logout, true);

    xhttp.onreadystatechange = function() {
        console.log(this.readyState);
        console.log(this.status);
        if (this.readyState === 4 && this.status === 200) {
            console.log(this.responseText);
            //document.getElementById('response').value = this.responseText;
            document.getElementById(E_HOMEPAGE).classList.add(CSS_HIDE);
            document.getElementById(E_LOGINPAGE).classList.remove(CSS_HIDE);
        }
        if (this.status >= 500){
            alert('Error: logout unsuccessful');
        }
    };
    //xhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhttp.send();
}

function createNewTicket(){
    const input = new Map([
        ['content', document.getElementById('newTicketBody').value]
    ]);

    fetch(ticketAPI.user_new_ticket, fetch_post_generator(input))
        .then(response => { return response.json();})
        .then(responseData => {
            console.log(responseData);
            if(responseData.response === 200) {
                alert('Success');
                document.getElementById('newTicketBody').value = '';
            }
            return responseData;
        })
        .catch(err => {
            alert('Error contacting the server, please try later');
            console.log("fetch error" + err);
        });
}


function getUsersTickets(){
    const ticketSnippet =  `
                <div class="card" id="ticketidstring">
                    <div class="card-content">
                        <div class="content">
                            Brief summary of the ticket content
                            <br/>
                            <br/>
                            Time submitted:
                            <time> 11:09 PM - 1 Jan 2016</time>
                            <br/>
                            status: In Progress
                        </div>
                    </div>
                </div>
                <br/>
    `;
    fetch(ticketAPI.user_view_tickets, FETCH_GET)
        .then(response => { return response.json();})
        .then(responseData => {
            console.log(responseData);
            if(responseData.response === 200) {
                console.log(JSON.stringify(responseData));
                // append tickets here
                let list = '';
                for (const entry of responseData.results) {
                    const time = entry.timeSubmitted.$date.$numberLong;
                    const date = new Date(parseInt(time));
                    console.log(date);
                    list +=  `
                            <div class="card" id="ticketidstring" onclick="showTicket('${entry.id.$oid}')">
                                <div class="card-content">
                                    <div class="content">
                                        ${entry.ticketContent.substr(0, 60)}
                                        <br/>
                                        <br/>
                                    Time submitted:
                                        <time>${date}</time>
                                        <br/>
                                        status: ${entry.state}
                                    </div>
                                </div>
                            </div>
                        <br/>
`;
                }
                document.getElementById('ticketsPanel').innerHTML = list;
            }
            return responseData;
        })
        .catch(err => {
            alert('Error contacting the server, please try later');
            console.log("fetch error" + err);
        });
}

function showTicket(id){
    //run request for ticket
    const input = new Map([
        ['ticketId', id.toString()]
    ]);

    fetch(ticketAPI.view_my_ticket, fetch_post_generator(input))
        .then(response => { return response.json();})
        .then(responseData => {
            console.log(responseData);
            if(responseData.response === 200) {
                //alert('Success');
                //document.getElementById('newTicketBody').value = '';
                document.getElementById('ticketViewName').innerText = responseData.result.user.name;
                document.getElementById('ticketViewEmail').innerText = responseData.result.user.email;
                document.getElementById('ticketViewTicket').innerText = responseData.result.content;
                document.getElementById('ticketViewTime').innerText = new Date(parseInt(responseData.result.time)).toDateString(); //responseData.result.time;
                document.getElementById('myTicketReplies').innerHTML = createReplyTextList(responseData.result.responses, responseData.result.staffOn, responseData.result.user);
                document.getElementById('currentTicketShownID').innerText = responseData.result.ticketId.$oid;

            }
            return responseData;
        })
        .catch(err => {
            alert('Error contacting the server, please try later');
            console.log("fetch error" + err);
        });
}

function createReplyTextList(responses, staffOn, user) {
const a = function(response){
    let name, email;

    if (response.respondeeId.$oid === user.id.$oid){
        name = user.name;
        email = user.email;
        console.log('is User')
    }else{
        const staffMember = resolveStaffObject(response.respondeeId, staffOn)
        if (staffMember === null){
            name = "unknown";
            email = "unknown";
        }else{
            name = staffMember.name;
            email = staffMember.email;
        }
    }

    return `
    <article class="media">
        <div class="media-content">
                                <div class="content">
                                    <p>
                                        <strong>${name}</strong> <small>${email}</small>
                                        <br/>
                                        <div>${response.content}</div>
                                        <br/>
                                        <time>${response.timeStamp}</time>
                                    </p>
             </div>
        </div>
    </article>
    `
    };

    const responselist = responses.map(a);
    return responselist.join('');


}

function resolveStaffObject(searchObjectId, staffOn) {
    for (const staff of staffOn) {
        if(staff.id.$oid === searchObjectId.$oid){
            return staff;
        }
    }
    return null;

}

function hideAllMenus(){
    document.getElementById(E_NEW_TICKET).classList.add(CSS_HIDE);
    document.getElementById(E_VIEW_MY_TICKETS).classList.add(CSS_HIDE);
    document.getElementById(E_VIEW_TICKETS_ATTENDING_TO).classList.add(CSS_HIDE);
    document.getElementById(E_VIEW_UNATTENDED_TICKETS).classList.add(CSS_HIDE);
    document.getElementById(E_VIEW_USERS).classList.add(CSS_HIDE);
    document.getElementById(E_EDIT_USER).classList.add(CSS_HIDE);
    document.getElementById(E_ADD_USER).classList.add(CSS_HIDE);
    // document.getElementById(E_NEW_TICKET).classList.add(CSS_HIDE);
}

function showNewTicket(){
    hideAllMenus();
    document.getElementById(E_NEW_TICKET).classList.remove(CSS_HIDE);
}

function showMyTickets(){
    hideAllMenus();
    document.getElementById(E_VIEW_MY_TICKETS).classList.remove(CSS_HIDE);
    //populate tickets list function here
    getUsersTickets();
}

function getAnchor(){
    const url = document.URL;
    const urlParts = url.split('#');
    if(urlParts.length>1){
        return urlParts[urlParts.length - 1]
    }else{
        return null;
    }
}

function fetch_post_generator(queryString){
    const fetchPost = FETCH_POST_TEMPLATE;
    fetchPost.body = create_query_string(queryString);
    return fetchPost;
}

function showAddUser(){
    hideAllMenus();
    document.getElementById(E_ADD_USER).classList.remove(CSS_HIDE);
}

function addUser(){
    // clear warnings
    document.getElementById('addUserEmailWarning').innerText = '';

    const name = document.getElementById('newName').value;
    const email = document.getElementById('newEmail').value;
    const password = document.getElementById('newPassword').value;
    const permission = document.getElementById('newUserType').value;

    console.log(name);
    console.log(email);
    console.log(password);
    console.log(permission);

    const input = new Map([
        ['user', name],
        ['email', email],
        ['password', password],
        ['permissions', permission]
    ]);

    fetch(ticketAPI.create_user, fetch_post_generator(input))
        .then(response => { return response.json();})
        .then(responseData => {
            console.log(responseData);
            if(responseData.response === RESPONSE_CODE_SUCCESS) {
                alert('Success');
            } else if (responseData.response === 500) {
                document.getElementById('addUserEmailWarning').innerText = 'A user with that email already exists';
            }
            return responseData;
        })
        .catch(err => {
            alert('Error contacting the server, please try later');
            console.log("fetch error" + err);
        });
}

function showUserList(){
    hideAllMenus();
    document.getElementById(E_VIEW_USERS).classList.remove(CSS_HIDE);
    getUserList();
}

function showEditUser(){
    hideAllMenus()
    document.getElementById(E_EDIT_USER).classList.remove(CSS_HIDE);
}

function getUserList(){
    fetch(ticketAPI.get_user_list, FETCH_GET)
        .then(response => { return response.json();})
        .then(responseData => {
            console.log(responseData);
            if(responseData.response === RESPONSE_CODE_SUCCESS) {
                //alert('Success');
                const users = responseData.result;
                const usershtml = users.map(user => {
                    return `
<tr>
    <td>${user.email}</td> 
    <td>${user.name}</td>
    <td>${(user.isActive)? 'Active': 'Blocked'}</td>
    <td>${user.permission}</td>
    <td><button class="button" type="button" onclick="removeUser('${user.id.$oid}')" id="uID_${user.id.$oid}">remove user</button></td>               
</tr>
                    `
                });
                const htmlString = usershtml.join('');
                document.getElementById('userTableRows').innerHTML = htmlString;
            }
            return responseData;
        })
        .catch(err => {
            alert('Error contacting the server, please try later');
            console.log("fetch error" + err);
        });
}

function removeUser(id){
    console.log('removing ' + id);
    const input = new Map([
        ['user', id],
    ]);
    fetch(ticketAPI.remove_user, fetch_post_generator(input))
        .then(response => { return response.json();})
        .then(responseData => {
            console.log(responseData);
            if(responseData.response === RESPONSE_CODE_SUCCESS) {
                alert('Successfully disabled user');
            }
            return responseData;
        })
        .catch(err => {
            alert('Error contacting the server, please try later');
            console.log("fetch error" + err);
        });
}

function create_query_string(key_val)
{
    function add_parameter_to_array(value, key, map)
    {
        entries.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
    }
    const entries = [];
    key_val.forEach(add_parameter_to_array);
    return entries.join('&');
}

function add_response(){
    const response = document.getElementById('ownTicketReply').value;
    const ticket = document.getElementById('currentTicketShownID').innerText;

    if(ticket == ""){
        console.log('no ticket selected');
    }
    const input = new Map([
        ['ticket', ticket],
        ['message', response]
    ]);

    console.log(input);
    fetch(ticketAPI.respond_to_ticket, fetch_post_generator(input))
        .then(response => { return response.json();})
        .then(responseData => {
            console.log(responseData);

            return responseData;
        })
        .catch(err => {
            alert('Error contacting the server, please try later');
            console.log("fetch error" + err);
        });

}