<!DOCTYPE html>
<html>
<head>
    <title>Visualizzazione Giocatori</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Elenco Giocatori</h2>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Partite Giocate</th>
                    <th>Partite Vinte</th>
                </tr>
            </thead>
            <tbody>

            <?php
            $servername = "localhost";
            $username = "root";
            $password = "tafex";
            $dbname = "indovina_parola_game";
            $port = 49154;

            $conn = new mysqli($servername, $username, $password, $dbname, $port);

            if ($conn->connect_error) {
                echo "<tr><td colspan='4'>Errore di connessione al database</td></tr>";
            } else {
                // Esegue la query solo se la connessione ha avuto successo
                $sql = "SELECT nome, cognome, num_giocate, num_vinte FROM giocatori";
                $result = $conn->query($sql);
    
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row["nome"]."</td>";
                        echo "<td>".$row["cognome"]."</td>";
                        echo "<td>".$row["num_giocate"]."</td>";
                        echo "<td>".$row["num_vinte"]."</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Nessun risultato trovato</td></tr>";
                }
                $conn->close();
            }
            ?>

            </tbody>
        </table>
    </div>

</body>
</html>