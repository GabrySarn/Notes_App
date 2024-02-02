<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        header {
            margin-bottom: 20px;
        }

        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 20px;
        }

        .note-form {
            width: 40%;
            margin-top: 20px;
        }

        .foldegories-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-evenly;
            margin-top: 50px;
        }

        .card {
            width: 162px;
            margin: 20px;
        }
    </style>
</head>

<body>

    <?php
    require 'connect.php';

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['email']) && isset($_SESSION['idUtente'])) {
        $email = $_SESSION['email'];
        $idUtente = $_SESSION['idUtente'];

        $folderMod = array('folder_id' => '', 'user_id' => '', 'folder_name' => '');


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['salva'])) {
                $folderTitle = $_POST['title'];
                $insertQuery = "INSERT INTO folders (user_id,name) VALUES ('$idUtente','$folderTitle')";
                $insertResult = $conn->query($insertQuery);
            } elseif (isset($_POST['modifica'])) {
                $fold_id = $_POST['modificafold'];
                updatefolder($conn, $fold_id, $idUtente);
            } elseif (isset($_POST['elimina'])) {
                $fold_id = $_POST['eliminafold'];
                deletefolder($conn, $fold_id);
                header("Location: {$_SERVER['PHP_SELF']}?deleted=true");
                exit;
            } elseif (isset($_POST['salva_mod'])) {
                $fold_id = $_POST['id'];
                deletefolder($conn, $fold_id);
                $folderTitle = $_POST['title'];
                $insertQuery = "INSERT INTO folders (user_id,name) VALUES ('$idUtente','$folderTitle')";
                $insertResult = $conn->query($insertQuery);
                header("Location: {$_SERVER['PHP_SELF']}?modded=true");
                exit;
            }

        }

        $selectQuery = "SELECT * FROM folders where user_id=$idUtente";
        $result = $conn->query($selectQuery);
    } else {
        echo '<script>alert("Errore: Utente non definito nella sessione.");</script>';
    }

    if (isset($_GET['logout']) && $_GET['logout'] == 1) {
        session_start();
        $_SESSION = array();
        session_destroy();
        header("Location: Login.html");
        exit;
    }



    function deletefolder($conn, $foldId)
    {
        $queryDeletefoldfolder = "DELETE FROM note_folder WHERE folder_id = ?";
        $stmtDeletefoldfolder = $conn->prepare($queryDeletefoldfolder);
        $stmtDeletefoldfolder->bind_param("i", $foldId);
        $resDeletefoldfolder = $stmtDeletefoldfolder->execute();
        $stmtDeletefoldfolder->close();

        $queryDeletefold = "DELETE FROM folders WHERE id = ?";
        $stmtDeletefold = $conn->prepare($queryDeletefold);
        $stmtDeletefold->bind_param("i", $foldId);
        $resDeletefold = $stmtDeletefold->execute();
        $stmtDeletefold->close();

        return $resDeletefold && $resDeletefoldfolder;
    }

    function updatefolder($conn, $fold_id, $idUtente)
    {
        global $folderMod;

        $queryGetfold = "SELECT f.id, f.name FROM folders f
         where f.id = ? and user_id=?";
        $stmtGetfold = $conn->prepare($queryGetfold);
        $stmtGetfold->bind_param("ii", $fold_id, $idUtente);
        $stmtGetfold->execute();
        $resultGetfold = $stmtGetfold->get_result();

        if ($resultGetfold && $resultGetfold->num_rows > 0) {
            $rowfold = $resultGetfold->fetch_assoc();

            $folderMod = array(
                'folder_id' => $rowfold['id'],
                'folder_name' => $rowfold['name']
            );

        }
        $stmtGetfold->close();

    }
    ?>

    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#">Notes App</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="noteHome.php"><img src="ico/home.png" alt="Icona" width="30"
                                height="30"></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="crea_cartella.php?logout=1"><img src="ico/logout.png" alt="Icona"
                                width="30" height="30"></a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <div class="main-container">
        <div class="note-form">
            <!-- Form per creare una nuova Folder -->
            <div class="note-card">
                <h5>
                    <?php echo isset($_POST['modifica']) ? 'Modifica Cartella' : 'Aggiungi una nuova Cartella'; ?>
                </h5>
                <form method="post" action="crea_cartella.php">
                    <div class="form-group">
                        <label for="noteTitle">Titolo</label>
                        <input type="text" class="form-control" id="noteTitle" name="title"
                            value="<?php echo isset($_POST['modifica']) ? $folderMod['folder_name'] : ''; ?>" required>
                    </div>
                    <input type="hidden" name="id"
                        value="<?php echo isset($_POST['modifica']) ? $folderMod['folder_id'] : ''; ?>">
                    <input type="hidden" name="<?php echo isset($_POST['modifica']) ? 'salva_mod' : 'salva'; ?>">
                    <button type="submit" class="btn btn-primary">
                        <?php echo isset($_POST['modifica']) ? 'Modifica' : 'Salva'; ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Mostra tutte le foldegorie -->
        <div class="foldegories-list">
            <?php
            $result = $conn->query($selectQuery);
            while ($row = $result->fetch_assoc()) {
                $FolderId = $row['id'];
                $FolderNome = $row['name'];
                                    ?>

                    <div class="card">
                        <div class="card-header">
                            <?php echo $FolderNome ?>
                        </div>

                        <form method="post" action="crea_cartella.php">
                            <div class="btn-group" role="group" aria-label="Basic outlined example">
                                <input type="hidden" name="modificafold" value="<?php echo $FolderId; ?>">
                                <button type="submit" name="modifica" class="btn btn-outline-primary">Modifica</button>
                                <input type="hidden" name="eliminafold" value="<?php echo $FolderId; ?>">
                                <button type="submit" class="btn btn-outline-primary" name="elimina">Elimina</button>
                            </div>
                        </form>
                    </div>

                    <?php
                }
            
            $conn->close();
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>