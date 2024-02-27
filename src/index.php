<?php

session_start();

include_once '/var/www/html/Models/hiveGameModel.php';
include_once '/var/www/html/Controllers/hiveGameController.php';
include_once '/var/www/html/Views/hiveGameView.php';
include_once '/var/www/html/Models/database.php';

$db = retrieveDatabase();
$game = new HiveGameModel($db);
$view = new HiveGameView($game);
$controller = new HiveGameController($game, $view);

try {
   $game->loadSession();
} catch (Exception $e) {
    header('Location: restart.php');
    exit(0);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Hive</title>
        <style>
            div.board {
                width: 60%;
                height: 100%;
                min-height: 500px;
                float: left;
                overflow: scroll;
                position: relative;
            }

            div.board div.tile {
                position: absolute;
            }

            div.tile {
                display: inline-block;
                width: 4em;
                height: 4em;
                border: 1px solid black;
                box-sizing: border-box;
                font-size: 50%;
                padding: 2px;
            }

            div.tile span {
                display: block;
                width: 100%;
                text-align: center;
                font-size: 200%;
            }

            div.player0 {
                color: black;
                background: white;
            }

            div.player1 {
                color: white;
                background: black
            }

            div.stacked {
                border-width: 3px;
                border-color: red;
                padding: 0;
            }
        </style>
    </head>
    <body>
        <div class="board">
            <?php
                echo $view->getBoardHtml();
            ?>
        </div>

        <?php
            for ($player = 0; $player < 2; $player++) {
                echo "<div class=\"hand\">";
                echo $controller->getPlayerName($player).": ";
                echo $view->getHandHtml($player);
                echo "</div>";
            }
        ?>

        <div class="turn">
            Turn: <?php echo $controller->getPlayerName($controller->getActivePlayer()); ?>
        </div>
        <form method="post" action="play.php">
            <select name="piece">
                <?php
                    foreach ($controller->getHand($controller->getActivePlayer()) as $tile => $ct) {
                        echo "<option value=\"$tile\">$tile</option>";
                    }
                ?>
            </select>
            <select name="to">
                <?php
                    foreach ($controller->getPossiblePlays() as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <input type="submit" value="Play">
        </form>
        <form method="post" action="move.php">
            <select name="from">
                <?php
                    foreach ($controller->getTilesToMove() as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <select name="to">
                <?php
                    foreach ($controller->getPossibleMoves() as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <input type="submit" value="Move">
        </form>
        <form method="post" action="pass.php">
            <input type="submit" value="Pass">
        </form>
        <form method="post" action="restart.php">
            <input type="submit" value="Restart">
        </form>
        <ol>
            <?php
            ?>
        </ol>
        <form method="post" action="undo.php">
            <input type="submit" value="Undo">
        </form>
    </body>
</html>