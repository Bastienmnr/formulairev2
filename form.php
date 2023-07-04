<?php
session_start();

require_once 'controller.php';

// Appeler la fonction connectBdd() pour établir la connexion à la base de données
$pdo = connectBdd();

// Insérer le nouveau formulaire dans la table form
$query = "INSERT INTO form (date_ajout) VALUES (NOW())";
$pdo->exec($query);

// Récupérer l'ID du formulaire en cours
$form_id = $pdo->lastInsertId();

// Stocker l'ID du formulaire en cours dans une variable de session
$_SESSION['form_id'] = $form_id;

// Construire la requête SQL pour récupérer les questions
$query = "SELECT * FROM question";

// Exécuter la requête et récupérer les résultats
$result = $pdo->query($query);

// Générer le formulaire automatisé
echo "<form action='submit.php' method='post' id='myForm' enctype='multipart/form-data'>";

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $id_question = $row['id_question'];
    $type = $row['type'];
    $choix = explode(',', $row['choix']);
    $intitule = $row['intitule'];

    echo "<label for='answer_{$row['id_question']}' class='answer-label'>$intitule</label><br>";

    // Générer les éléments du formulaire en fonction du type de question
    switch ($type) {
        case 'checkbox':
            foreach ($choix as $choice) {
                echo "<input type='checkbox' id='answer_{$row['id_question']}' name='answer_{$row['id_question']}[]' value='" . htmlspecialchars($choice) . "' > $choice<br>";
            }
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
        case 'radio':
            foreach ($choix as $choice) {
                echo "<input type='radio' id='answer_{$row['id_question']}' name='answer_{$row['id_question']}' value='" . htmlspecialchars($choice) . "' > $choice<br>";
            }
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
        case 'select':
            echo "<select id='answer_{$row['id_question']}' name='answer_{$row['id_question']}' >";
            foreach ($choix as $choice) {
                echo "<option value='" . htmlspecialchars($choice) . "'>$choice</option>";
            }
            echo "</select><br>";
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
        case 'texte':
            echo "<input type='text' id='answer_{$row['id_question']}' name='answer_{$row['id_question']}' ><br>";
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
        case 'nombre':
            echo "<input type='number' id='answer_{$row['id_question']}' name='answer_{$row['id_question']}' ><br>";
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
        case 'file':
            echo "<input type='file' id='answer_{$row['id_question']}' name='answer_{$row['id_question']}'><br>";
            echo "<input type='hidden' id='type_{$row['id_question']}' name='type_{$row['id_question']}' value='$type'>";
            break;
    }
    echo "<br>";
}

echo "<input type='submit' value='Envoyer'>";
echo "</form>";



?>
