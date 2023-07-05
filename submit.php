<?php

session_start();

require_once 'controller.php';

const MAX_FILE_SIZE = 5000000;
const ALLOWED_FILE_TYPES = array('image/png', 'image/jpeg', 'application/pdf', 'image/jpg'); // Add all allowed file types


try {


    $pdo = connectBdd();

    // Create a new form entry and get its ID
    $query = "INSERT INTO form (date_envois, session_id) VALUES (now(), :session_id)";
    $stmt = $pdo->prepare($query);
    $sessionId = session_id();
    $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
    $stmt->execute();
    $form_id = $pdo->lastInsertId();

    $types = [];
    $answers = [];

    foreach ($_POST as $key => $value) {
        $tmp = explode('_', $key);
        if ($tmp[0] === 'type') {
            $types[$tmp[1]] = $value;
        } elseif ($tmp[0] === 'answer') {
            $answers[$tmp[1]] = $value;
        }
    }

    foreach ($types as $id_question => $type) {
        $id_question = (int)$id_question;
        $answer = isset($answers[$id_question]) ? $answers[$id_question] : null;

        // Check if the question exists in the database
        $query = "SELECT * FROM question WHERE id_question = :question_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':question_id', $id_question, PDO::PARAM_INT);
        $stmt->execute();
        $questionExists = $stmt->fetch();

        if ($questionExists) {
            // Handle file upload if the question type is 'file'
            if ($type == "file" ) {
                // Check if the file input exists and if the file size is within the limit
                if (isset($_FILES['answer_'.$id_question]) && $_FILES['answer_'.$id_question]['size'] > MAX_FILE_SIZE) {
                    throw new Exception("Error, the file is too large!");
                } else {
                    // Check if there is an error in the uploaded file
                    if($_FILES['answer_'.$id_question]['error'] == UPLOAD_ERR_NO_FILE) {
                        continue;
                    } else if ($_FILES['answer_'.$id_question]['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception("Error uploading file. Error code: " . $_FILES['answer_'.$id_question]['error']);
                    }
                    
                    // Verify the file type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $file_type = finfo_file($finfo, $_FILES['answer_'.$id_question]['tmp_name']);
                    finfo_close($finfo);
                    
                    if (!in_array($file_type, ALLOWED_FILE_TYPES)) {
                        throw new Exception("Le type de fichier n'est pas autorisé.");
                    }
                    
                    // Create the 'uploads' directory if it doesn't exist
                    if (!file_exists('uploads')) {
                        mkdir('uploads', 0755, true);
                    }
                    
                    // Move the uploaded file to the 'uploads' directory
                    $file_name = uniqid() . '_' . basename($_FILES['answer_'.$id_question]['name']);
                    $target_file = 'uploads/' . $file_name;
                    $target_file = filter_var($target_file, FILTER_SANITIZE_URL);
                    
                    if (move_uploaded_file($_FILES['answer_'.$id_question]['tmp_name'], $target_file)) {
                        // Insert the file path into the 'reponse' table
                        $query = "INSERT INTO reponse (fk_form, fk_id_question, texte) VALUES (:form_id, :question_id, :reponse)";
                        $stmt = $pdo->prepare($query);
                        $stmt->bindParam(':form_id', $form_id, PDO::PARAM_INT);
                        $stmt->bindParam(':question_id', $id_question, PDO::PARAM_INT);
                        $stmt->bindParam(':reponse', $target_file, PDO::PARAM_STR);
                        $stmt->execute();
                        echo "L'envoi des images a bien été effectué !";
                    } else {
                        throw new Exception("Sorry, an error occurred while uploading your file.");
                    }
                }
            } else {
                // Handle other types of questions (checkbox, radio, select, texte, nombre)
                if (is_array($answer)) {
                    $answer = implode(', ', $answer);
                }

                // Insert the answer into the 'reponse' table
                if ($type == 'nombre'|| $type == 'range') {
                    $query = "INSERT INTO reponse (fk_form, fk_id_question, nombre) VALUES (:form_id, :question_id, :reponse)";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':form_id', $form_id, PDO::PARAM_INT);
                    $stmt->bindParam(':question_id', $id_question, PDO::PARAM_INT);
                    $stmt->bindParam(':reponse', $answer, PDO::PARAM_INT);
                    $stmt->execute();
                } else {
                    $query = "INSERT INTO reponse (fk_form, fk_id_question, texte) VALUES (:form_id, :question_id, :reponse)";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':form_id', $form_id, PDO::PARAM_INT);
                    $stmt->bindParam(':question_id', $id_question, PDO::PARAM_INT);
                    $stmt->bindParam(':reponse', $answer, PDO::PARAM_STR);
                    $stmt->execute();
                }
            }
        } else {
            throw new Exception("The question with ID $id_question does not exist.");
        }
    }

    echo "Les réponses au formulaire ont bien été prise en charge !";
} catch (Exception $e) {
    echo 'Erreur : ' . $e->getMessage();
}

exit;

?>
