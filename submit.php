
<?php


session_start();


require_once 'controller.php';

const MAX_FILE_SIZE = 5000000;
const ALLOWED_FILE_TYPES = array('image/png', 'image/jpeg', 'application/pdf'); // Add all allowed file types


$pdo = connectBdd();
$form_id = $_SESSION['form_id'];
$reponses = $_POST;

/*
echo "<PRE>";
var_dump($_POST); // , $_FILES
echo "</PRE>";
exit();*/

foreach ($reponses as $key => $reponse) {
    
    $tmp = explode('_', $key);
    if ($tmp[0] === 'type') {
        // Extract the question ID from the key
        
        $id_question = (int)$tmp[1];

        // Check if the question type is set
        if (isset($_POST['type_'.$id_question])) {
            $type = $_POST['type_'.$id_question];
        } else {
            $type = null;
        }
        
        // Check if the question exists in the database
        $query = "SELECT * FROM question WHERE id_question = :question_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':question_id', $id_question, PDO::PARAM_INT);
        $stmt->execute();
        $questionExists = $stmt->fetch();
        
        if ($questionExists) {
            
            // Handle file upload if the question type is 'file'
            
          
            //exit(); 
            
            if ($type == "file" ) {
                // Check if the file size is within the limit
                if (!empty($_FILES) && $_FILES['answer_'.$id_question]['size'] > MAX_FILE_SIZE) {
                    echo "Error, the file is too large!";
                } else {
                    // Check if there is an error in the uploaded file
                    if($_FILES['answer_'.$id_question]['error'] == UPLOAD_ERR_NO_FILE) {
                        continue;
                    } else if ($_FILES['answer_'.$id_question]['error'] !== UPLOAD_ERR_OK) {
                        echo "Error uploading file. Error code: " . $_FILES['answer_'.$id_question]['error'];
                        //var_dump($_FILES['answer_'.$id_question], UPLOAD_ERR_OK);
                        exit;
                    }
                    
                    // Verify the file type
                    $file_type = $_FILES['answer_'.$id_question]['type'];
                    
                    
                    if (!in_array($file_type, ALLOWED_FILE_TYPES)) {
                        echo "Le type de fichier n'est pas autorisé.";
                        exit;
                    }
                    
                    // Create the 'uploads' directory if it doesn't exist
                    if (!file_exists('uploads')) {
                        mkdir('uploads', 0777, true);
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
                        echo "Sorry, an error occurred while uploading your file.";
                    }
                }
            }
            else {
                
                // Handle other types of questions (checkbox, radio, select, texte, nombre)
                if (is_array($reponse)) {
                    $reponse = implode(', ', $reponse);
                }
                
                // Insert the answer into the 'reponse' table
                if ($type == 'nombre') {
                    $query = "INSERT INTO reponse (fk_form, fk_id_question, nombre) VALUES (:form_id, :question_id, :reponse)";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':form_id', $form_id, PDO::PARAM_INT);
                    $stmt->bindParam(':question_id', $id_question, PDO::PARAM_INT);
                    $stmt->bindParam(':reponse', $reponse, PDO::PARAM_INT);
                    $stmt->execute();
                } else {
                    $query = "INSERT INTO reponse (fk_form, fk_id_question, texte) VALUES (:form_id, :question_id, :reponse)";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':form_id', $form_id, PDO::PARAM_INT);
                    $stmt->bindParam(':question_id', $id_question, PDO::PARAM_INT);
                    $stmt->bindParam(':reponse', $reponse, PDO::PARAM_STR);
                    $stmt->execute();
                }
            }
        } else {
            echo "The question with ID $id_question does not exist.";
        }
    }
}

exit;
