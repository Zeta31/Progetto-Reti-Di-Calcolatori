<?php
$chosenPort = readline("Inserisci la porta da utilizzare: ");
$socket = socket_create(AF_INET, SOCK_STREAM, 0);
if ($socket === false) {
    echo "socket_create() ha fallito: ragione: " . socket_strerror(socket_last_error()) . "\n";
} else {
    echo "OK.\n";
}
socket_bind($socket, "localhost", $chosenPort);

/* Conessione */
socket_connect($socket, "localhost", 8000);
try{
    writeSocket($socket, readline("Inserisci il tuo nome e cognome: "));
    $id = readSocket($socket);
    echo("Sei il giocatore: $id\n");
    echo("In attesa dell'avversario...\n");

    /* Ricezione nome */
    echo("Il tuo avversario si chiama: " . readSocket($socket) . "\n"); // Nome avversario   

    /* Scelta lunghezza parola */
    if($id === "1") {
        $lengths = readSocket($socket);
        
        $wordLength = readline("Scegli la lunghezza della parola da indovinare tra " . $lengths . ": ");
        while(!in_array($wordLength, explode(",", $lengths))) {
            $wordLength = readline("Scegli la lunghezza della parola da indovinare tra " . $lengths . ": ");
        }
        writeSocket($socket, $wordLength);

    }else{
        echo("L'avversario sta scegliendo la lunghezza delle parole da indovinare \n"); // Nome avversario
    }

    /* Scelta tra possibili parole */
    $words = readSocket($socket); // Parole possibili
    $chosenWord = readline("Scegli una parola tra " . $words . ": ");
    while(!in_array($chosenWord, explode(",", $words))) {
        $chosenWord = readline("Scegli una parola tra " . $words . ": ");
    }
    writeSocket($socket, $chosenWord);

    /* Atteesa scelta avversario */
    echo("In attesa dell'avversario...\n");
    readSocket($socket);

    /* Indovinare la parola */
    while(1){
        $scelta = readline("Indovina la parola dell'avversario: ");
        writeSocket($socket, $scelta);
        $risposta = readSocket($socket);
        
        if($risposta === ''){
            echo "Hai perso, il tuo avversario ha indovinato prima di te";
            break;
            
        }
        if(str_replace('2', '', trim($risposta)) === ''){
            echo "Hai vinto";
            break;
        }

        echo($risposta . "\n");
    }
    writeSocket($socket, "END_REQUEST");
}catch(Exception $e){
    if($e->getMessage() === "CLOSE"){
        echo "Il server ha chiuso la connessione perché l'altro giocatore si è disconnesso\n";
    }

}
socket_close($socket);
exit;



function readSocket($socket){
    $data = @socket_read($socket, 2048);
    if ($data === false) {
        throw new Exception("Error Reading Data");
    }
    if($data === "CLOSE"){
        throw new Exception("CLOSE");
    }

    return trim($data);
}

function writeSocket($socket, $data){
    if(@socket_write($socket, $data) === false){
        throw new Exception("Error Writing Data");
    }
}