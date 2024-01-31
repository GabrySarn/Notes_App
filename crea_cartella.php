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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salva'])) {
            $folderTitle = $_POST['title'];

            $insertQuery = "INSERT INTO folders (user_id,name) VALUES ('$idUtente','$folderTitle')";
            $insertResult = $conn->query($insertQuery);
        }
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
            <div class="note-card">
                <h5>Crea Cartella</h5>
                <form method="post" action="crea_cartella.php">
                    <div class="form-group">
                        <label for="Title">Titolo</label>
                        <input type="text" class="form-control" id="Title" name="title" required>
                    </div>
                    <input type="hidden" name="salva">
                    <button type="submit" class="btn btn-primary">
                        Salva
                    </button>
                </form>
            </div>
        </div>
        <?php
        $conn->close();
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
