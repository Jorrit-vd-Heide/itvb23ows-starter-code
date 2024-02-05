<?php

trait MoveHistory{
    // This method returns an HTML string representing the move history of a game
    public function getMoveHistory() {
        // Prepare a SQL statement to select all moves from the 'moves' table where the 'game\_id' matches the game ID of the current object
        $stmt = $this->database->prepare('SELECT * FROM moves WHERE game_id = ?');

        // Bind the game ID of the current object to the SQL statement as a parameter
        $stmt->bind_param('i', $this->game_id);

        // Execute the SQL statement
        $stmt->execute();

        // Get the result of the SQL statement
        $result = $stmt->get_result();

        // Initialize an empty HTML string
        $html = "";

        // Loop through each row of the result
        while ($row = $result->fetch_array()) {
            // Add a new list item to the HTML string, containing a string representation of the move
            $html .= '<li>' . $row[2] . ' ' . $row[3] . ' ' . $row[4] . '</li>';
        }

        // Return the HTML string
        return $html;
    }
}