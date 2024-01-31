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
        }

        header {
            margin-bottom: 20px;
        }

        .main-container {
            display: flex;
            justify-content: space-between;
            margin: 20px;
        }

        .note-form {
            width: 40%;
            margin-right: 20px;
        }

        .note-list {
            width: 55%;
        }

        .category-filter {
            margin-bottom: 20px;
        }

        .filter-container {
            display: flex;
            align-items: center;
        }

        .filter-container label {
            margin-right: 10px;
        }

        .filter-controls {
            display: flex;
            align-items: center;
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

        $queryCategorie = "SELECT id, name FROM categories";
        $resultCategorie = $conn->query($queryCategorie);

        if ($resultCategorie && $resultCategorie->num_rows > 0) {
            $categorieOptions = "";

            while ($rowCategoria = $resultCategorie->fetch_assoc()) {
                $idCategoria = $rowCategoria['id'];
                $nomeCategoria = $rowCategoria['name'];

                $categorieOptions .= "<option value='$idCategoria'>$nomeCategoria</option>";
            }
        } else {
            $categorieOptions = "<option value=''>Nessuna categoria disponibile</option>";
        }

        $queryFolders = "SELECT id, name FROM folders WHERE user_id = ?";
        $stmtFolders = $conn->prepare($queryFolders);
        $stmtFolders->bind_param("i", $idUtente);
        $stmtFolders->execute();
        $resultFolders = $stmtFolders->get_result();

        $foldersOptions = "<option value=''>Nessuna cartella</option>";
        if ($resultFolders && $resultFolders->num_rows > 0) {
            while ($rowFolder = $resultFolders->fetch_assoc()) {
                $idFolder = $rowFolder['id'];
                $nameFolder = $rowFolder['name'];
                $foldersOptions .= "<option value='$idFolder'>$nameFolder</option>";
            }
        }

        $stmtFolders->close();

        $queryNote = "SELECT n.id, n.title, n.content, c.name FROM notes n
        join note_category nc on nc.note_id = n.id
        join categories c on c.id = nc.category_id
        WHERE user_id = ?";
        $stmtNote = $conn->prepare($queryNote);
        $stmtNote->bind_param("i", $idUtente);
        $stmtNote->execute();
        $resultNote = $stmtNote->get_result();

        $noteList = array();

        while ($rowNote = $resultNote->fetch_assoc()) {
            $noteList[] = array(
                'title' => $rowNote['title'],
                'text' => $rowNote['content'],
                'category' => $rowNote['name'],
                'id' => $rowNote['id']
            );
        }
        $noteMod = array('title' => '', 'text' => '', 'category_id' => '', 'category' => '');

        $stmtNote->close();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['salva'])) {
                $title = $_POST['title'];
                $text = $_POST['text'];
                $category = $_POST['category'];
                $folder = isset($_POST['cartella']) ? $_POST['cartella'] : '';
                saveNote($conn, $idUtente, $title, $text, $category, $folder);
                header("Location: {$_SERVER['PHP_SELF']}?added=true");
                exit;
            } elseif (isset($_POST['modifica'])) {
                $note_id = $_POST['modificaNota'];
                updateNote($conn, $idUtente, $note_id);
            } elseif (isset($_POST['elimina'])) {
                $note_id = $_POST['eliminaNota'];
                deleteNote($conn, $note_id);
                header("Location: {$_SERVER['PHP_SELF']}?deleted=true");
                exit;
            } elseif (isset($_POST['salva_mod'])) {
                $note_id = $_POST['id'];
                deleteNote($conn, $note_id);
                $title = $_POST['title'];
                $text = $_POST['text'];
                $category = $_POST['category'];
                saveNote($conn, $idUtente, $title, $text, $category, $folder);
                header("Location: {$_SERVER['PHP_SELF']}?modded=true");
                exit;
            } elseif (isset($_POST['cartella'])) {
                $note_id = $_POST['currentNoteId'];
                $selectedFolder = $_POST['cartella'];
                updateNoteFolder($conn, $note_id, $selectedFolder);
            }
        }


        if (isset($_GET['logout']) && $_GET['logout'] == 1) {
            session_start();
            $_SESSION = array();
            session_destroy();
            header("Location: Login.html");
            exit;
        }
    } else {
        echo '<script>alert("Errore: Utente non definito nella sessione.");</script>';
    }


    function saveNote($conn, $idUtente, $title, $text, $category, $folder)
    {
        $queryInsertNote = "INSERT INTO notes (user_id, title, content) VALUES (?,?,?)";
        $stmtInsertNote = $conn->prepare($queryInsertNote);
        $stmtInsertNote->bind_param("iss", $idUtente, $title, $text);
        $resInsertNote = $stmtInsertNote->execute();

        if ($resInsertNote) {
            $lastInsertedId = $stmtInsertNote->insert_id;

            $queryInsertNoteCategory = "INSERT INTO note_category (note_id, category_id) VALUES (?,?)";
            $stmtInsertNoteCategory = $conn->prepare($queryInsertNoteCategory);
            $stmtInsertNoteCategory->bind_param("ii", $lastInsertedId, $category);
            $resInsertNoteCategory = $stmtInsertNoteCategory->execute();

            $stmtInsertNoteCategory->close();
        }

        $stmtInsertNote->close();

        if (!empty($folder)) {
            $queryInsertNoteFolder = "INSERT INTO note_folder (note_id, folder_id) VALUES (?,?)";
            $stmtInsertNoteFolder = $conn->prepare($queryInsertNoteFolder);
            $stmtInsertNoteFolder->bind_param("ii", $lastInsertedId, $folder);
            $resInsertNoteFolder = $stmtInsertNoteFolder->execute();

            $stmtInsertNoteFolder->close();
        }

        return $resInsertNote && $resInsertNoteCategory && (empty($folder) || $resInsertNoteFolder);
    }

    function updateNote($conn, $idUtente, $note_id)
    {
        global $noteMod;

        $queryGetNote = "SELECT n.title, n.content, c.id, c.name FROM notes n
            join note_category nc on nc.note_id = n.id
            join categories c on c.id = nc.category_id
            WHERE n.user_id = ? and n.id = ?";
        $stmtGetNote = $conn->prepare($queryGetNote);
        $stmtGetNote->bind_param("ii", $idUtente, $note_id);
        $stmtGetNote->execute();
        $resultGetNote = $stmtGetNote->get_result();

        if ($resultGetNote && $resultGetNote->num_rows > 0) {
            $rowNote = $resultGetNote->fetch_assoc();
            $noteMod = array(
                'id' => $note_id,
                'title' => $rowNote['title'],
                'text' => $rowNote['content'],
                'category_id' => $rowNote['id'],
                'category_name' => $rowNote['name']
            );
        }
        $stmtGetNote->close();

    }

    function deleteNote($conn, $noteId)
    {
        $queryDeleteNoteCategory = "DELETE FROM note_category WHERE note_id = ?";
        $stmtDeleteNoteCategory = $conn->prepare($queryDeleteNoteCategory);
        $stmtDeleteNoteCategory->bind_param("i", $noteId);
        $resDeleteNoteCategory = $stmtDeleteNoteCategory->execute();
        $stmtDeleteNoteCategory->close();

        $queryDeleteNoteFolder = "DELETE FROM note_folder WHERE note_id = ?";
        $stmtDeleteNoteFolder = $conn->prepare($queryDeleteNoteFolder);
        $stmtDeleteNoteFolder->bind_param("i", $noteId);
        $resDeleteNoteFolder = $stmtDeleteNoteFolder->execute();
        $stmtDeleteNoteFolder->close();

        $queryDeleteNote = "DELETE FROM notes WHERE id = ?";
        $stmtDeleteNote = $conn->prepare($queryDeleteNote);
        $stmtDeleteNote->bind_param("i", $noteId);
        $resDeleteNote = $stmtDeleteNote->execute();
        $stmtDeleteNote->close();

        return $resDeleteNote && $resDeleteNoteCategory;
    }

    function getFoldersNavbar($conn, $selectedFolder)
    {
        $query = "SELECT id, name FROM folders";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            echo '<div>';
            while ($row = $result->fetch_assoc()) {
                $idFolder = $row['id'];
                $nameFolder = $row['name'];
                $selected = ($idFolder == $selectedFolder) ? 'selected' : '';
                echo "<a class='dropdown-item' href='noteHome.php?folder_id={$row['id']}' data-folder-id='$idFolder' $selected>$nameFolder</a>";
            }
            echo '</div>';
        }
    }


    function updateNoteFolder($conn, $note_id, $folder_id)
    {
        $queryCheckFolder = "SELECT * FROM note_folder WHERE note_id = ?";
        $stmtCheckFolder = $conn->prepare($queryCheckFolder);
        $stmtCheckFolder->bind_param("i", $note_id);
        $stmtCheckFolder->execute();
        $resultCheckFolder = $stmtCheckFolder->get_result();

        if ($resultCheckFolder->num_rows > 0) {
            $queryUpdateFolder = "UPDATE note_folder SET folder_id = ? WHERE note_id = ?";
            $stmtUpdateFolder = $conn->prepare($queryUpdateFolder);
            $stmtUpdateFolder->bind_param("ii", $folder_id, $note_id);
            $resUpdateFolder = $stmtUpdateFolder->execute();
            $stmtUpdateFolder->close();
        } else {
            $queryInsertFolder = "INSERT INTO note_folder (folder_id, note_id) VALUES (?, ?)";
            $stmtInsertFolder = $conn->prepare($queryInsertFolder);
            $stmtInsertFolder->bind_param("ii", $folder_id, $note_id);
            $resInsertFolder = $stmtInsertFolder->execute();
            $stmtInsertFolder->close();
        }

        $stmtCheckFolder->close();

        if (isset($resInsertFolder)) {
            return $resInsertFolder;
        } elseif (isset($resUpdateFolder)) {
            return $resUpdateFolder;
        }

        return false;
    }

    ?>

    <header>

        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#">Notes App</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Cartelle
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <?php
                            getFoldersNavbar($conn,$selectedFolder);
                            ?>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="noteHome.php"><img src="ico/home.png" alt="Icona" width="30"
                                height="30"></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="noteHome.php?logout=1"><img src="ico/logout.png" alt="Icona"
                                width="30" height="30"></a>
                    </li>
                </ul>
            </div>
        </nav>

    </header>

    <div class="main-container">
        <!-- Lato sinistro - Scheda nota -->
        <div class="note-form">
            <div class="note-card">
                <h5>
                    <?php echo isset($_POST['modifica']) ? 'Modifica nota' : 'Aggiungi una nuova nota'; ?>
                </h5>
                <form method="post" action="noteHome.php">
                    <div class="form-group">
                        <label for="noteTitle">Titolo</label>
                        <input type="text" class="form-control" id="noteTitle" name="title"
                            value="<?php echo isset($_POST['modifica']) ? $noteMod['title'] : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="noteText">Testo</label>
                        <textarea class="form-control" id="noteText" name="text" rows="4"
                            required><?php echo isset($_POST['modifica']) ? $noteMod['text'] : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="categorySelect">Categoria</label>
                        <select class="form-control" id="categorySelect" name="category" required>
                            <?php
                            $cid = $noteMod['category_id'];
                            $cna = $noteMod['category_name'];

                            echo "<option value='$cid' $selected>$cna</option>";
                            foreach ($resultCategorie as $rowCategoria) {
                                $idCategoria = $rowCategoria['id'];
                                if ($idCategoria != $noteMod['category_id']) {
                                    $nomeCategoria = $rowCategoria['name'];
                                    $selected = ($idCategoria == $selectedCategoryId) ? 'selected' : '';
                                    echo "<option value='$idCategoria' $selected>$nomeCategoria</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <input type="hidden" name="id"
                        value="<?php echo isset($_POST['modifica']) ? $noteMod['id'] : ''; ?>">
                    <input type="hidden" name="<?php echo isset($_POST['modifica']) ? 'salva_mod' : 'salva'; ?>">
                    <button type="submit" class="btn btn-primary">
                        <?php echo isset($_POST['modifica']) ? 'Modifica' : 'Salva'; ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Lato destro - Elenco delle note -->
        <div class="note-list">
            <div class="category-filter">
                <form method="GET">
                    <div class="filter-container">
                        <label for="filterByCategory">Filtra per Categoria:</label>
                        <div class="filter-controls">
                            <select class="form-control" name="filterByCategory" id="filterByCategory">
                                <option value="all">Tutte le categorie</option>
                                <?php echo $categorieOptions; ?>
                            </select>
                            <button type="submit" class="btn btn-primary" style="margin-left: 10px;">Filtra</button>
                        </div>
                    </div>
                </form>
            </div>
            <h5>Le tue Note</h5>
            <div class="row">
                <?php
                $selectedCategory = isset($_GET['filterByCategory']) ? $_GET['filterByCategory'] : 'all';

                if ($selectedCategory != 'all') {
                    $query = "SELECT name FROM categories WHERE id = ?";
                    $stmtCat = $conn->prepare($query);
                    $stmtCat->bind_param("i", $selectedCategory);
                    $stmtCat->execute();
                    $resultCat = $stmtCat->get_result();

                    if ($resultCat && $resultCat->num_rows > 0) {
                        $rowCategory = $resultCat->fetch_assoc();
                        $selectedCategoryName = $rowCategory['name'];
                    }
                }

                foreach ($noteList as $note) {
                    if ($selectedCategory == 'all' || $note['category'] == $selectedCategoryName) {
                        ?>

                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <?php echo $note['title']; ?>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <?php echo $note['text']; ?>
                                    </li>
                                    <li class="list-group-item">
                                        [
                                        <?php echo $note['category']; ?>
                                        ]
                                    </li>
                                    <form method="post" action="noteHome.php">
                                        <li class="list-group-item">
                                            <label for="cartella">Seleziona una cartella:</label>
                                            <input type="hidden" name="currentNoteId" value="<?php echo $note['id']; ?>">
                                            <select id="cartella" name="cartella" class="form-control"
                                                onchange="this.form.submit()" style="height: 50px;">
                                                <?php echo $foldersOptions; ?>
                                            </select>
                                        </li>
                                        <div class="btn-group" role="group" aria-label="Basic outlined example">
                                            <input type="hidden" name="modificaNota" value="<?php echo $note['id']; ?>">
                                            <button type="submit" name="modifica"
                                                class="btn btn-outline-primary">Modifica</button>
                                            <input type="hidden" name="eliminaNota" value="<?php echo $note['id']; ?>">
                                            <button type="submit" class="btn btn-outline-primary"
                                                name="elimina">Elimina</button>
                                        </div>
                                    </form>
                                </ul>
                            </div>
                        </div>

                        <?php
                    }
                }
                $conn->close();
                ?>
            </div>
        </div>


    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Ottieni il parametro folder_id dalla query string dell'URL
            const urlParams = new URLSearchParams(window.location.search);
            const selectedFolderId = urlParams.get('folder_id');

            // Seleziona dinamicamente la cartella nel menu a discesa
            if (selectedFolderId) {
                const folderSelect = document.getElementById('cartella');
                if (folderSelect) {
                    folderSelect.value = selectedFolderId;
                }
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>