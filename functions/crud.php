<?php
include_once("functions/constants.php");
include_once("functions/connection.php");

#region Client Methods

#region CREATE

/**
 * Creates a new client in the database.
 *
 * This function inserts a new client record into the table specified by TABLE_CLIENTS constant.
 *
 * @param   mysqli              $conn An active connection to the MySQL database.
 * @param   string              $name The name of the client.
 * @param   string              $address The address of the client.
 * @param   string              $phone_number The phone number of the client.
 * @return  int|false           The ID of the newly created client on success, or false on failure.
 * @throws  RuntimeException    If there is an error inserting the client data into the database.
 */
function createClient(mysqli $conn, string $name, string $address, string $phone_number): int|false
{
    $query = "INSERT INTO " . TABLE_clients . " (name, address, phone_number) VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        throw new RuntimeException("Failed to prepare statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "sss", $name, $address, $phone_number);

    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException("Failed to create client: " . mysqli_stmt_error($stmt));
    }

    $insertedId = mysqli_stmt_insert_id($stmt);

    mysqli_stmt_close($stmt);

    return $insertedId;
}

#endregion

#region READ

/**
 * Fetches all clients from the database.
 *
 * This function retrieves all client records from the table specified by TABLE_CLIENT constant.
 *
 * @param   mysqli              $conn An active connection to the MySQL database.
 * @return  array               An associative array containing all client data, or an empty array if no clients are found.
 * @throws  RuntimeException    If there is an error fetching clients from the database.
 */
function getAllClients($conn): array
{
    $query = "SELECT * FROM " . TABLE_clients;

    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new RuntimeException("Failed to retrieve clients: " . mysqli_error($conn));
    }

    $clients = [];
    while ($client = mysqli_fetch_assoc($result)) {
        $clients[] = $client;
    }

    mysqli_free_result($result);

    return $clients;
}

/**
 * Fetches a client by their ID from the database.
 *
 * This function retrieves a single client record based on the provided ID.
 *
 * @param   mysqli              $conn An active connection to the MySQL database.
 * @param   int                 $id The unique identifier of the client to retrieve.
 * @return  array|null          An associative array containing the client data if found, or null if no client with the provided ID exists.
 * @throws  RuntimeException    If there is an error preparing or executing the statement.
 */
function getClientById($conn, $id): ?array
{
    $query = "SELECT * FROM " . TABLE_clients . " WHERE id = ?";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        throw new RuntimeException("Failed to prepare statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException("Failed to execute statement: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);

    $client = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_free_result($result);

    return $client;
}

/**
 * Searches for clients based on a given attribute.
 *
 * This function searches for client records where the provided attribute partially matches any of the specified fields (name, address, phone number).
 *
 * @param   mysqli              $conn An active connection to the MySQL database.
 * @param   string              $attr The attribute value to search for (partial match with wildcards).
 * @return  array|null          An associative array containing the first matching client data, or null if no clients are found.
 * @throws  RuntimeException    If there is an error preparing or executing the statement.
 */
function searchClientsByAttribute(mysqli $conn, string $attr): ?array
{
    $query = "SELECT * FROM " . TABLE_clients . " 
            WHERE name LIKE ? OR address LIKE ? OR phone_number LIKE ?";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        throw new RuntimeException("Failed to prepare statement: " . mysqli_error($conn));
    }

    $likeParam = "%" . $attr . "%";

    mysqli_stmt_bind_param($stmt, "sss", $likeParam, $likeParam, $likeParam);

    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException("Failed to execute statement: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    $clients = [];

    // Loop through each row in the result set
    while ($client = mysqli_fetch_assoc($result)) {
        $clients[] = $client;
    }

    mysqli_stmt_close($stmt);
    mysqli_free_result($result);

    return $clients;
}

#endregion

#region UPDATE

/**
 * Updates a client in the database.
 *
 * This function updates an existing client record based on a specified identifier and provided data.
 *
 * @param   mysqli              $conn An active connection to the MySQL database.
 * @param   int                 $clientId The unique identifier of the client to update.
 * @param   array               $updateData An associative array containing the data to be updated (e.g., ['name' => 'New Name', 'email' => 'new@example.com']).
 * @return  bool                True on successful update, false on failure.
 * @throws  RuntimeException    If there is an error updating the client data in the database.
 */
function updateClient(mysqli $conn, int $clientId, array $updateData): bool
{
    $updateFields = [];
    foreach ($updateData as $field => $value) {
        $updateFields[] = "$field = ?";
    }
    $query = "UPDATE " . TABLE_clients . " SET " . implode(', ', $updateFields) . " WHERE id = ?";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        throw new RuntimeException("Failed to prepare statement: " . mysqli_error($conn));
    }

    $paramTypes = str_repeat("s", count($updateData)) . "i";
    $paramValues = [];
    foreach ($updateData as $value) {
        $paramValues[] = &$value;
    }
    $paramValues[] = &$clientId;

    mysqli_stmt_bind_param($stmt, $paramTypes, ...$paramValues);

    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException("Failed to update client: " . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    return true;
}

#endregion

#region DELETE

/**
 * Deletes a client from the database.
 *
 * This function removes a client record based on a specified identifier.
 *
 * @param   mysqli              $conn An active connection to the MySQL database.
 * @param   int                 $clientId The unique identifier of the client to delete.
 * @return  bool                True on successful deletion, false on failure.
 * @throws  RuntimeException    If there is an error deleting the client from the database.
 */
function deleteClient(mysqli $conn, int $clientId): bool
{
    $query = "DELETE FROM " . TABLE_clients . " WHERE id = ?";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        throw new RuntimeException("Failed to prepare statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $clientId);

    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException("Failed to delete client: " . mysqli_stmt_error($stmt));
    }

    $affectedRows = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt);

    return $affectedRows > 0;
}

#endregion

#endregion
