<?php
$host = "localhost";
$port = 8000;
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "socket_create() ha fallito: ragione: " . socket_strerror(socket_last_error()) . "\n";
} else {
    echo "OK.\n";
}
$username = "root";
$password = "tafex";
$dbname = "indovina_parola_game";
$dbport = 49154;
$conn = new mysqli($host, $username, $password, $dbname, $dbport);
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

$words = [
    4 => ["casa", "cane", "palo", "asse", "auto", "moto", "gola"],
    5 => ["pollo", "gatto". "pasta", "tazza", "volpe", "poker", "virus"],
    6 => ["tavolo", "dimora", "slitta", "giorno", "chiaro", "doppio", "figlio"],
];

socket_bind($socket, $host, $port);
socket_listen($socket, 2);

while(true){
    try{
        echo "In attesa di connessioni...\n";

        /* Conessione player1 */
        $player1Socket = socket_accept($socket);
        $player1 = acceptPlayer($player1Socket);
        echo "Player1: " . $player1['nome'] . " " . $player1['cognome'] . "\n";
        $player1['socket'] = $player1Socket;
        echo "Player1 connesso\n";
        writeSocket($player1Socket, "1");

        /* Conessione player2 */
        $player2Socket = socket_accept($socket);

        $player2 = acceptPlayer($player2Socket);
        echo "Player2 connesso\n";
        $player2['socket'] = $player2Socket;

        writeSocket($player2Socket, "2");

        /* Invio dei nomi */
        echo "Mando i nomi...\n";
        sendOpponentNames($player1, $player2);

        /* Scelta delle lunghezze */
        echo "Faccio scegliere la lunghezza...\n";
        $chosenLength = chooseLength($player1, $words);

        /* Scelta delle parole */
        echo "Faccio scegliere le parole...\n";
        $player1['word'] = chooseWord($player1, $chosenLength, $words);
        $player2['word'] = chooseWord($player2, $chosenLength, $words);

        /* Inizio gioco */
        echo "Iniziano ad indovinare...\n";
        writeSocket($player1Socket, "Via");
        writeSocket($player2Socket, "Via");

        $sockets = [$player1Socket, $player2Socket];
        $write = [];
        $except = [];

        $winner = null;
        while (!$winner) {
            $read = $sockets;
            if (socket_select($read, $write, $except, 0) > 0) {
                foreach ($read as $sock) {
                    $guess = trim(readSocket($sock));
                    switch ($sock) {
                        case $player1Socket:
                            $response = handleGuess($guess, $player2['word']);
                            writeSocket($player1Socket, $response);
                            break;
                        case $player2Socket:
                            $response = handleGuess($guess, $player1['word']);
                            writeSocket($player2Socket, $response);
                            break;    
                    }

                    if(str_replace('2', '', $response) === '')
                        $winner = $sock === $player1Socket ?  1 : 2;
                }
            }
        }

        /* Salvataggio informazioni partita */
        echo "Inserisco risultati sul database...\n";
        $queryPlayer1 = "INSERT INTO giocatori (nome, cognome, num_giocate, num_vinte) VALUES('". $player1['nome'] . "', '". $player1['cognome'] . "', 1, ". (($winner == 1) ? "1" : "0") .") ON DUPLICATE KEY UPDATE
                        num_giocate = num_giocate + 1, num_vinte = num_vinte + ". (($winner == 1) ? "1" : "0");

        $queryPlayer2 = "INSERT INTO giocatori (nome, cognome, num_giocate, num_vinte) VALUES('". $player2['nome'] . "', '". $player2['cognome'] . "', 1, ". (($winner == 2) ? "1" : "0") .") ON DUPLICATE KEY UPDATE
                        num_giocate = num_giocate + 1, num_vinte = num_vinte + ". (($winner == 2) ? "1" : "0");

        $resultPlayer1 = $conn->query($queryPlayer1);
        $resultPlayer2 = $conn->query($queryPlayer2);

        if($resultPlayer1 === false || $resultPlayer2 === false){
            echo "Errore nell'aggiornamento del database";
        }
        socket_shutdown($player1Socket, 1);
        readSocket($player1Socket);
        socket_close($player1Socket);

        socket_shutdown($player2Socket, 1);
        readSocket($player2Socket);
        socket_close($player2Socket);
    }catch(Exception $e){
        echo $e->getMessage();
        if(isset($player1Socket) && socket_write($player1Socket, "CLOSE")){    
            socket_close($player1Socket);
        }
        if(isset($player2Socket) && socket_write($player2Socket, "CLOSE")){
            socket_close($player2Socket);
        }
    }
}



function handleGuess($guess, $word){ 
    $response = "";
    for ($i = 0; $i < strlen($guess); $i++) {
        if ($word[$i] == $guess[$i]) {
            $response .= "2";
        } elseif (strpos($word, $guess[$i]) !== false) {
            $response .= "1";
        } else {
            $response .= "0";
        }
    }
    while(strlen($response) < strlen($word)){
        $response .= "0";
    }   
    return $response;
}

function acceptPlayer($playerSocket){
    $player = array();
    try {
        $nomeCompleto = explode(" ", trim(readSocket($playerSocket)));
        $player['nome'] = $nomeCompleto[0];
        $player['cognome'] = $nomeCompleto[1];
    } catch (Exception $e) {
        throw new Exception("Error accepting player: " . $e->getMessage(), 1);
    }
    return $player;
}

function sendOpponentNames($player1, $player2){
    try {
        writeSocket($player1['socket'], $player2['nome'] . " " . $player2['cognome']);
        writeSocket($player2['socket'], $player1['nome'] . " " . $player1['cognome']);
    } catch (Exception $e) {
        throw new Exception("Error sending opponent names: " . $e->getMessage(), 1);
    }
}

function chooseLength($player1, $words){
    try {
        $wordLengths = array_keys($words);
        writeSocket($player1['socket'], implode(",", $wordLengths));
        $wordLength = trim(readSocket($player1['socket']));
        return $wordLength;
    } catch (Exception $e) {
        throw new Exception("Error choosing word length: " . $e->getMessage(), 1);
    }
}

function chooseWord($player, $length,  $words){
    try {
        $possibleWords = $words[$length];
        writeSocket($player['socket'], implode(",", $possibleWords));
        $word = trim(readSocket($player['socket']));
        return $word;
    } catch (Exception $e) {
        throw new Exception("Error choosing word: " . $e->getMessage(), 1);
    }
}

function readSocket($socket){
    $data = @socket_read($socket, 2048);
    if ($data === false) {
        throw new Exception("Error Reading Data", 1);
    }

    return trim($data);
}

function writeSocket($socket, $data){
    if(@socket_write($socket, $data) === false){
        throw new Exception("Error Writing Data", 1);
    }
}