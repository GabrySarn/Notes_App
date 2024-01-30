<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folders</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Notes App</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <!-- ... il tuo codice per la cartella ... -->
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="noteHome.php"><img src="ico/home.png" alt="Icona" width="30"
                            height="30"></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="noteHome.php?logout=1"><img src="ico/logout.png" alt="Icona" width="30"
                            height="30"></a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <?php
        require 'connect.php';

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['email']) && isset($_SESSION['idUtente'])) {
            $email = $_SESSION['email'];
            $idUtente = $_SESSION['idUtente'];

            if (isset($_GET['folder_id'])) {
                $folder_id = $_GET['folder_id'];

                $queryFolder = "SELECT name FROM folders WHERE id = ?";
                $stmtFolder = $conn->prepare($queryFolder);
                $stmtFolder->bind_param("i", $folder_id);
                $stmtFolder->execute();
                $resultFolder = $stmtFolder->get_result();
            }
            $queryNote = "SELECT n.id, n.title, n.content, c.name 
                        FROM notes n
                        JOIN note_folder nf ON n.id = nf.note_id
                        JOIN folders f ON nf.folder_id = f.id
                        join note_category nc on nc.note_id = n.id
                         join categories c on c.id = nc.category_id
                         WHERE n.user_id = ? AND nf.folder_id = ?";
            $stmtNote = $conn->prepare($queryNote);
            $stmtNote->bind_param("ii", $idUtente, $folder_id);
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
            if ($resultFolder->num_rows > 0) {
                $folder_row = $resultFolder->fetch_assoc();
                $folder_name = $folder_row['name'];
                ?>
                <h5>
                    <?php echo $folder_name; ?>
                </h5>
                <div class="row">
                    <?php
                    foreach ($noteList as $note) {
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
                                        <?php echo $note['category']; ?>
                                    </li>
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
                    ?>
                </div>
                <?php
            }
        }
        ?>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>