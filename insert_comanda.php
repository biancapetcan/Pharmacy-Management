<?php
session_start();
ob_start(); // Prevenire output înainte de header

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['EMAIL'])) {
    header("Location: login.php");
    exit();
}

// Conexiune la baza de date
$conn = new mysqli('localhost', 'root', '', 'baza_date');
if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}

// Verifică dacă formularul a fost trimis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idFurnizor = isset($_POST['id_furnizor']) ? intval($_POST['id_furnizor']) : null;
    $idMedicament = isset($_POST['id_medicament']) ? intval($_POST['id_medicament']) : null;
    $dataComanda = isset($_POST['data_comanda']) ? trim($_POST['data_comanda']) : null;
    $totalComanda = isset($_POST['total_comanda']) ? floatval($_POST['total_comanda']) : null;

    if ($idFurnizor && $idMedicament && $dataComanda && $totalComanda) {
        $sqlInsertComanda = "INSERT INTO Comenzi (ID_Furnizor, ID_Medicament, Data_Comanda, Total_Comanda) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sqlInsertComanda);
        if ($stmt) {
            $stmt->bind_param("iisd", $idFurnizor, $idMedicament, $dataComanda, $totalComanda);
            if ($stmt->execute()) {
                header("Location: furnizor_dashboard.php");
                exit();
            } else {
                die("Eroare la salvarea comenzii: " . $stmt->error);
            }
        } else {
            die("Eroare la pregătirea interogării: " . $conn->error);
        }
    }
}

// Preia lista de medicamente și furnizori pentru selectare
$sqlMedicamente = "SELECT ID_Medicament, Nume_Medicament FROM Medicamente";
$resultMedicamente = $conn->query($sqlMedicamente);
$sqlFurnizori = "SELECT DISTINCT ID_Furnizor, Nume_Furnizor FROM Furnizori";
$resultFurnizori = $conn->query($sqlFurnizori);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaugă Comandă</title>
    <link rel="stylesheet" href="style_furnizor_admin.css">
</head>

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

    a {
        display: inline-block;
        margin-top: 15px;
        color: #4CAF50;
        text-decoration: none;
        font-size: 1em;
    }

    a:hover {
        text-decoration: underline;
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

<body>
    <h1>Adaugă o nouă comandă</h1>
    <form method="POST"><label for="id_furnizor">Furnizor:</label><select name="id_furnizor" id="id_furnizor" required>
            <option value="">Selectează furnizor</option>
            <?php while ($row = $resultFurnizori->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($row['ID_Furnizor']); ?>">
                    <?php echo htmlspecialchars($row['Nume_Furnizor']); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br><label for="id_medicament">Medicament:</label><select name="id_medicament" id="id_medicament"
            required>
            <option value="">Selectează medicament</option>
            <?php while ($row = $resultMedicamente->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($row['ID_Medicament']); ?>">
                    <?php echo htmlspecialchars($row['Nume_Medicament']); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br><label for="data_comanda">Data Comandă:</label><input type="date" name="data_comanda"
            id="data_comanda" required><br><br><label for="total_comanda">Total Comandă:</label><input type="number"
            step="0.01" name="total_comanda" id="total_comanda" required><br><br><button type="submit">Salvează
            Comanda</button><a href="furnizor_dashboard.php">Înapoi la dashboard</a></form>
</body>

</html>