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
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <?php
    require 'connect.php';
    //--------------------------

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['email']) && isset($_SESSION['idUtente'])) {
        $email = $_SESSION['email'];
        $idUtente = $_SESSION['idUtente'];

        // Ottieni categorie dal database
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
            // Nessuna categoria nel database
            $categorieOptions = "<option value=''>Nessuna categoria disponibile</option>";
        }

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
        $noteMod = array('title' => '', 'text' => '', 'category_id' => ''); 

        $stmtNote->close();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['salva'])) {
                if (
                    isset($_POST['title']) && !empty($_POST['title'])
                    && isset($_POST['text']) && !empty($_POST['text'])
                    && isset($_POST['category']) && !empty($_POST['category'])
                ) {

                    $title = $_POST['title'];
                    $text = $_POST['text'];
                    $category = $_POST['category'];

                    $query = "INSERT INTO notes (user_id, title, content) VALUES (?,?,?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("iss", $idUtente, $title, $text);
                    $resPrenotazione = $stmt->execute();

                    if ($resPrenotazione) {
                        $lastInsertedId = $stmt->insert_id;
                    }

                    $stmt->close();

                    $query = "INSERT INTO note_category (note_id, category_id) VALUES (?,?)";
                    $stmt = $conn->prepare($query);

                    if ($stmt) {
                        $stmt->bind_param("ii", $lastInsertedId, $category);
                        $resPrenotazione = $stmt->execute();
                    }
                    $stmt->close();

                    header("Location: {$_SERVER['PHP_SELF']}?added=true");
                    exit;
                }
            } elseif (isset($_POST['modifica'])) {
                $note_id = $_POST['modificaNota'];

                $queryGetNote = "SELECT n.title, n.content, c.id FROM notes n
                    join note_category nc on nc.note_id = n.id
                    join categories c on c.id = nc.category_id
                    WHERE user_id = ?";
                $stmtGetNote = $conn->prepare($queryGetNote);
                $stmtGetNote->bind_param("i", $note_id);
                $stmtGetNote->execute();
                $resultGetNote = $stmtGetNote->get_result();

                if ($resultGetNote && $resultGetNote->num_rows > 0) {
                    $rowNote = $resultGetNote->fetch_assoc();
                    echo $rowNote['title'];
                    $noteMod = array(
                        'title' => $rowNote['title'],
                        'text' => $rowNote['content'],
                        'category_id' => $rowNote['id']
                    );
                }
                $stmtGetNote->close();
            } elseif (isset($_POST['elimina'])) {
                $note_id = $_POST['eliminaNota'];

                $queryDeleteNoteCategory = "DELETE FROM note_category WHERE note_id = ?";
                $stmtDeleteNoteCategory = $conn->prepare($queryDeleteNoteCategory);
                $stmtDeleteNoteCategory->bind_param("i", $note_id);
                $stmtDeleteNoteCategory->execute();
                $stmtDeleteNoteCategory->close();

                $queryDeleteNote = "DELETE FROM notes WHERE id = ?";
                $stmtDeleteNote = $conn->prepare($queryDeleteNote);
                $stmtDeleteNote->bind_param("i", $note_id);
                $stmtDeleteNote->execute();
                $stmtDeleteNote->close();

                // Redirect alla stessa pagina dopo l'eliminazione
                header("Location: {$_SERVER['PHP_SELF']}?deleted=true");
                exit;
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


    ?>

    <header>

        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#">Notes App</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <!-- ... -->
                    </li>
                    <li class="nav-item dropdown">
                        <!-- ... -->
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="noteHome.php"><img src="ico/home.png" alt="Icona" width="30" height="30"></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="noteHome.php?logout=1"><img src="ico/logout.png" alt="Icona" width="30" height="30"></a>
                    </li>
                </ul>
            </div>
        </nav>

    </header>

    <div class="main-container">
        <!-- Lato sinistro - Scheda nota -->
        <div class="note-form">
            <div class="note-card">
                <h5><?php echo isset($_POST['modifica']) ? 'Modifica nota' : 'Aggiungi una nuova nota'; ?></h5>
                <form method="post" action="noteHome.php">
                    <div class="form-group">
                        <label for="noteTitle">Titolo</label>
                        <input type="text" class="form-control" id="noteTitle" name="title" value="<?php echo isset($_POST['modifica']) ? $noteMod['title'] : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="noteText">Testo</label>
                        <textarea class="form-control" id="noteText" name="text" rows="4" required><?php echo isset($_POST['modifica']) ? $noteMod['text'] : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="categorySelect">Categoria</label>
                        <select class="form-control" id="categorySelect" name="category" required>
                            <option value="">Seleziona una categoria</option>
                            <?php
                            foreach ($resultCategorie as $rowCategoria) {
                                $idCategoria = $rowCategoria['id'];
                                $nomeCategoria = $rowCategoria['name'];
                                $selected = ($idCategoria == $selectedCategoryId) ? 'selected' : '';
                                echo "<option value='$idCategoria' $selected>$nomeCategoria</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <input type="hidden" name="<?php echo isset($_POST['modifica']) ? 'modificaId' : 'salva'; ?>">
                    <button type="submit" class="btn btn-primary"><?php echo isset($_POST['modifica']) ? 'Modifica' : 'Salva'; ?></button>
                </form>
            </div>
        </div>



        <!-- Lato destro - Elenco delle note -->
        <div class="note-list">
            <div class="category-filter">
                <form method="GET">
                    <label for="filterByCategory">Filtra per Categoria:</label>
                    <select class="form-control" name="filterByCategory" id="filterByCategory">
                        <option value="all">Tutte le categorie</option>
                        <?php echo $categorieOptions; ?>
                    </select>
                    <button type="submit">Filtra</button>
                </form>
            </div>
            <h5>Le tue Note</h5>
            <div class="row">
                <?php
                // Ottenere la categoria selezionata o impostarla su "all" di default
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

                // Mostrare le note in base alla categoria
                foreach ($noteList as $note) {
                    if ($selectedCategory == 'all' || $note['category'] == $selectedCategoryName) {
                ?>

                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <?php echo $note['title']; ?>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><?php echo $note['text']; ?></li>
                                    <li class="list-group-item"><?php echo $note['category']; ?></li>
                                    <div class="btn-group" role="group" aria-label="Basic outlined example">
                                        <form method="post" action="noteHome.php">
                                            <input type="hidden" name="modificaNota" value="<?php echo $note['id']; ?>">
                                            <button type="submit" name="modifica" class="btn btn-outline-primary">Modifica</button>
                                            <input type="hidden" name="eliminaNota" value="<?php echo $note['id']; ?>">
                                            <button type="submit" class="btn btn-outline-primary" name="elimina">Elimina</button>
                                        </form>
                                    </div>
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

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>