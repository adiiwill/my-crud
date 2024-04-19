<?php
include_once("./constants.php");

# region Client Methods

# region Client - GET

/**
 * Returns all the clients from the database
 * 
 * @param   mysqli  $conn   The MySQL database connection
 */
function getAllClients($conn)
{
    $query = "SELECT * FROM " . TABLE_clients;

    $result = mysqli_query($conn, $query);
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }

    return $users;
}

/**
 * Returns an user with the specified id
 * 
 * @param   mysqli  $conn   The MySQL database connection
 * @param   string  $id     The id of the user
 */
function getClientById($conn, $id)
{
    $query = "SELECT * FROM " . TABLE_clients . " WHERE id = " . $id . " LIMIT 1";

    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Returns an array of users based on the given attribute
 * 
 * @param   mysqli  $conn   The MySQL database connection
 * @param   string  $attr   The attribute value to search for
 */
function getClientByAttr($conn, $attr)
{
    $query = "SELECT * FROM " . TABLE_clients
        . " WHERE name         LIKE '%" . $attr . "%' OR"
        . " WHERE address      LIKE '%" . $attr . "%' OR"
        . " WHERE phone_number LIKE '%" . $attr . "%'";

    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}
#endregion

# region  Client - SET
function setClient()
{
}
#endregion

#endregion
