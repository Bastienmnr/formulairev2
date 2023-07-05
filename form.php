<?php
session_start();

require_once 'controller.php';

// Appeler la fonction connectBdd() pour établir la connexion à la base de données
$pdo = connectBdd();

// Construire la requête SQL pour récupérer les questions
$query = "SELECT * FROM question WHERE num_formulaire=1";

// Exécuter la requête et récupérer les résultats
$result = $pdo->query($query);

// Générer le formulaire automatisé
echo "<form action='submit.php' method='post' id='myForm' enctype='multipart/form-data'>";

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $id_question = $row['id_question'];
    $type = $row['type'];
    $choix = explode(',', $row['choix']);
    $intitule = $row['intitule'];

    echo "<div class='question'>";


    // Générer les éléments du formulaire en fonction du type de question
    switch ($type) {
        case 'checkbox':
            echo "<label for='answer_{$row['id_question']}' class='answer-label'>$intitule</label><br>";
            foreach ($choix as $choice) {
                echo "<input type='checkbox' id='answer_{$row['id_question']}' name='answer_{$row['id_question']}[]' value='" . htmlspecialchars($choice) . "' > $choice<br>";
            }
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
        case 'radio':
            echo "<label for='answer_{$row['id_question']}' class='answer-label'>$intitule</label><br>";
            foreach ($choix as $choice) {
                echo "<input type='radio' id='answer_{$row['id_question']}' name='answer_{$row['id_question']}' value='" . htmlspecialchars($choice) . "' > $choice<br>";
            }
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
        case 'select':
            echo "<label for='answer_{$row['id_question']}' class='answer-label'>$intitule</label><br>";
            echo "<select id='answer_{$row['id_question']}' name='answer_{$row['id_question']}' >";
            foreach ($choix as $choice) {
                echo "<option value='" . htmlspecialchars($choice) . "'>$choice</option>";
            }
            echo "</select><br>";
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
        case 'texte':
            echo "<label for='answer_{$row['id_question']}' class='answer-label'>$intitule</label><br>";
            echo "<input type='text' id='answer_{$row['id_question']}' name='answer_{$row['id_question']}' ><br>";
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
        case 'nombre':
            echo "<label for='answer_{$row['id_question']}' class='answer-label'>$intitule</label><br>";
            echo "<input type='number' id='answer_{$row['id_question']}' name='answer_{$row['id_question']}' ><br>";
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
        case 'file':
            echo "<label for='answer_{$row['id_question']}' class='answer-label'>$intitule</label><br>";
            echo "<input type='file' id='answer_{$row['id_question']}' name='answer_{$row['id_question']}'><br>";
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
        case 'range':
            echo "<label for='answer_{$row['id_question']}' class='answer-label'>$intitule</label><br>";
            list($min, $max) = explode(',', $row['choix']);
            $default = ($min + $max) / 2;
            echo "<input type='range' min='$min' max='$max' value='$default' step='1' id='answer_{$row['id_question']}' name='answer_{$row['id_question']}'><br>";
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
               
    }
    echo "</div><br>";
}

echo "<input type='submit' value='Envoyer'>";
echo "</form>";
?>
