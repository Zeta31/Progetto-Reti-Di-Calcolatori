# Traccia fornita

"Indovina la Parola" a due giocatori
Prenotato da :
Si realizzi una applicazione on line in Node.Js o un Client/Server in linguaggio C o PHP che implementi il gioco "Indovina la parola" a due giocatori.
Il gioco consiste nell'indovinare la parola scelta dell'altro giocatore prima che l'altro indovini la propria parola scelta. Per semplificare l'esercizio si consideri un'insieme di parole predefinite raggruppate per lunghezza in modo da far scegliere.
Si realizzi l'andamento del gioco considerando i due giocatori come dei Client e il Server come banco che riceve i vari tentativi e comunica i risultati.
- I due Client una volta collegato
        -Invia una stringa "Nome e cognome" , e attendono la risposta del Server
        -Riceve una stringa dal Server con il nome del Giocatore sfidante
        -Se si e' il primo ad essersi collegato
            -sceglie tra le possibili lunghezze delle parole
        -Riceve le possibili parole e ne sceglie una inviandola al Server
        -Inizia un ciclo che termina quando una dei due indovina la parola in cui
            -si invia una parola che si pensi essere stata scelta dell'altro giocatore
            -si riceve la risposta tramite un vettore lungo la parola con 0 se il carattere corrispondente e' sbagliato, 1 se e' corretto ma non nel posto giusto, 2 se e' corretto e nel posto giusto. Ad esempio se la parola corretta e' "matto" e la parola inviata e' "sarto"la risposta sarà "02122". Se riceve "2222.." ha vinto.
- Il Server una volta collegato
        -Acquisisce il nominativo del primo giocatore e attende che un altro si collega e invia ad entrambi i nomi dell'altro.
        -Riceve dal primo la dimensione della parola
        -Invia ad entrambi la lista delle parole
        -Riceve le parole scelte dai giocatori
        -Inizia un ciclo che termina quando una dei due indovina la parola in cui
            - riceve le parole dai giocatori
            - elabora la risposta 
In caso di gruppo composta da due studenti
L'informazione (Nome,Cognome,Numero partite giocate,,Numero partite vinte) deve essere inserita in un DataBase e visualizzabile in una pagina web in php
In caso di gruppo composto da tre studenti creare un'interfaccia web in PHP o in NodeJs per i client.