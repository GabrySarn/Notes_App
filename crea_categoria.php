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

        .categories-list {
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

        $categoryMod = array('category_id' => '', 'category' => '');


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['salva'])) {
                $categoryTitle = $_POST['title'];
                $insertQuery = "INSERT INTO categories (name) VALUES ('$categoryTitle')";
                $insertResult = $conn->query($insertQuery);
            } elseif (isset($_POST['modifica'])) {
                $cat_id = $_POST['modificaCat'];
                updateCategory($conn, $cat_id);
            } elseif (isset($_POST['elimina'])) {
                $cat_id = $_POST['eliminaCat'];
                deleteCategory($conn, $cat_id);
                header("Location: {$_SERVER['PHP_SELF']}?deleted=true");
                exit;
            } elseif (isset($_POST['salva_mod'])) {
                $cat_id = $_POST['id'];
                deleteCategory($conn,  $cat_id);
                $categoryTitle = $_POST['title'];
                $insertQuery = "INSERT INTO categories (name) VALUES ('$categoryTitle')";
                $insertResult = $conn->query($insertQuery);
                header("Location: {$_SERVER['PHP_SELF']}?modded=true");
                exit;
            }

        }

        $selectQuery = "SELECT * FROM categories";
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



    function deleteCategory($conn, $CatId)
    {
        $queryDeleteCatCategory = "DELETE FROM note_category WHERE category_id = ?";
        $stmtDeleteCatCategory = $conn->prepare($queryDeleteCatCategory);
        $stmtDeleteCatCategory->bind_param("i", $CatId);
        $resDeleteCatCategory = $stmtDeleteCatCategory->execute();
        $stmtDeleteCatCategory->close();

        $queryDeleteCat = "DELETE FROM categories WHERE id = ?";
        $stmtDeleteCat = $conn->prepare($queryDeleteCat);
        $stmtDeleteCat->bind_param("i", $CatId);
        $resDeleteCat = $stmtDeleteCat->execute();
        $stmtDeleteCat->close();

        return $resDeleteCat && $resDeleteCatCategory;
    }

    function updateCategory($conn, $cat_id)
    {
        global $categoryMod;

        $queryGetCat = "SELECT c.id, c.name FROM categories c
         where c.id = ?";
        $stmtGetCat = $conn->prepare($queryGetCat);
        $stmtGetCat->bind_param("i", $cat_id);
        $stmtGetCat->execute();
        $resultGetCat = $stmtGetCat->get_result();

        if ($resultGetCat && $resultGetCat->num_rows > 0) {
            $rowCat = $resultGetCat->fetch_assoc();
            $categoryMod = array(
                'category_id' => $rowCat['id'],
                'category_name' => $rowCat['name']
            );
        }
        $stmtGetCat->close();

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
                        <a class="nav-link" href="crea_categoria.php?logout=1"><img src="ico/logout.png" alt="Icona"
                                width="30" height="30"></a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <div class="main-container">
        <div class="note-form">
            <!-- Form per creare una nuova categoria -->
            <div class="note-card">
                <h5>
                    <?php echo isset($_POST['modifica']) ? 'Modifica categoria' : 'Aggiungi una nuova categoria'; ?>
                </h5>
                <form method="post" action="crea_categoria.php">
                    <div class="form-group">
                        <label for="noteTitle">Titolo</label>
                        <input type="text" class="form-control" id="noteTitle" name="title"
                            value="<?php echo isset($_POST['modifica']) ? $categoryMod['category_name'] : ''; ?>" required>
                    </div>
                    <input type="hidden" name="id"
                        value="<?php echo isset($_POST['modifica']) ? $categoryMod['category_id'] : ''; ?>">
                    <input type="hidden" name="<?php echo isset($_POST['modifica']) ? 'salva_mod' : 'salva'; ?>">
                    <button type="submit" class="btn btn-primary">
                        <?php echo isset($_POST['modifica']) ? 'Modifica' : 'Salva'; ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Mostra tutte le categorie -->
        <div class="categories-list">
            <?php
            $result = $conn->query($selectQuery);
            while ($row = $result->fetch_assoc()) {
                $categoriaId = $row['id'];
                $categoriaNome = $row['name'];
                ?>

                <div class="card">
                    <div class="card-header">
                        <?php echo $categoriaNome ?>
                    </div>

                    <form method="post" action="crea_categoria.php">
                        <div class="btn-group" role="group" aria-label="Basic outlined example">
                            <input type="hidden" name="modificaCat" value="<?php echo $categoriaId; ?>">
                            <button type="submit" name="modifica" class="btn btn-outline-primary">Modifica</button>
                            <input type="hidden" name="eliminaCat" value="<?php echo $categoriaId; ?>">
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