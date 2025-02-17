<?php
session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['EMAIL'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'baza_date');
if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}

// Verificăm dacă s-a trimis formularul
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cantitate']) && isset($_POST['id_medicament'])) {
    $idMedicament = intval($_POST['id_medicament']); // ID-ul medicamentului
    $cantitateNoua = intval($_POST['cantitate']); // Cantitatea actualizată

    // Actualizare doar a cantității
    $sqlUpdate = "UPDATE Cumparari SET Cantitate = ? WHERE ID_Medicament = ?";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("ii", $cantitateNoua, $idMedicament);

    if ($stmt->execute()) {
        // Dacă actualizarea a reușit, redirecționează la dashboard
        header("Location: admin_dashboard.php");
        exit(); // Este important să încheiem scriptul după redirecționare
    } else {
        echo "Eroare la actualizarea cantității.";
    }

    $stmt->close();
}

$conn->close();
?>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        padding: 0;
        background-color: #f9f9f9;
        color: #333;
        text-align: center;
        background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('background.jpg');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    h1 {
        color: #4CAF50;
        font-size: 2em;
        margin-bottom: 20px;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }

    form {
        background-color: white;
        padding: 20px 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        width: 400px;
    }

    .error {
        color: red;
        margin-bottom: 20px;
    }

    label {
        display: block;
        font-size: 1.1em;
        margin-bottom: 10px;
        color: #333;
    }

    input,
    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1em;
    }

    input:focus,
    select:focus {
        outline: none;
        border-color: #4CAF50;
        box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
    }

    button {
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        font-size: 1em;
        padding: 10px 20px;
        width: 100%;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #45a049;
    }

    .back-link {
        margin-top: 10px;
        background-color: #4CAF50;
        color: white;
        text-align: center;
        text-decoration: none;
        display: block;
        padding: 10px 20px;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    .back-link:hover {
        background-color: #45a049;
    }

    @media (max-width: 768px) {
        form {
            width: 90%;
        }

        h1 {
            font-size: 1.5em;
        }
    }
</style>
</head>
<form method="POST">
    <input type="hidden" name="id_medicament"
        value="<?php echo isset($_POST['id_medicament']) ? $_POST['id_medicament'] : ''; ?>">
    <label for="cantitate">Cantitate nouă:</label>
    <input type="number" name="cantitate" id="cantitate" placeholder="Introduceți cantitatea" required>
    <button type="submit">Actualizează Cantitatea</button>
</form>
<br>
<a href="admin_dashboard.php" class="back-link">Înapoi la dashboard</a>