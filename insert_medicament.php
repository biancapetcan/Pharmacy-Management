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

$eroare = "";

// Verificăm dacă s-a trimis formularul
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificăm existența și validitatea câmpurilor
    $numeMedicament = isset($_POST['nume_medicament']) ? trim($_POST['nume_medicament']) : null;
    $tipMedicament = isset($_POST['tip_medicament']) ? trim($_POST['tip_medicament']) : null;
    $pretMedicament = isset($_POST['pret_medicament']) ? floatval($_POST['pret_medicament']) : null;
    $cantitate = isset($_POST['cantitate']) ? intval($_POST['cantitate']) : null;
    $idFurnizor = isset($_POST['id_furnizor']) ? intval($_POST['id_furnizor']) : null;

    // Validare simplă pentru câmpuri obligatorii
    if ($numeMedicament && $tipMedicament && $pretMedicament && $cantitate && $idFurnizor) {
        // Inserare în tabelul Medicamente
        $sqlInsertMedicamente = "INSERT INTO Medicamente (Nume_Medicament, Tip_Medicament, Pret_Medicament) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sqlInsertMedicamente);
        $stmt->bind_param("ssd", $numeMedicament, $tipMedicament, $pretMedicament);

        if ($stmt->execute()) {
            $idMedicamentNou = $conn->insert_id;

            // Inserare în tabelul Cumparari
            $sqlInsertCumparari = "INSERT INTO Cumparari (ID_Medicament, Cantitate, Data_Vanzare) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($sqlInsertCumparari);
            $stmt->bind_param("ii", $idMedicamentNou, $cantitate);
            $stmt->execute();

            // Asociere cu furnizorul în tabelul Medicamente_Furnizori
            $sqlInsertMedicamenteFurnizori = "INSERT INTO Medicamente_Furnizori (ID_Medicament, ID_Furnizor) VALUES (?, ?)";
            $stmt = $conn->prepare($sqlInsertMedicamenteFurnizori);
            $stmt->bind_param("ii", $idMedicamentNou, $idFurnizor);
            $stmt->execute();

            header("Location: admin_dashboard.php");
            exit(); // Redirecționează la dashboard după succes
        } else {
            $eroare = "Eroare la adăugarea medicamentului.";
        }
    } else {
        $eroare = "Toate câmpurile sunt obligatorii. Te rugăm să completezi toate datele.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaugă Medicament</title>
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

<body>
    <h1>Adaugă Medicament</h1>

    <form method="POST">
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($eroare)): ?>
            <p class="error"><?php echo htmlspecialchars($eroare); ?></p>
        <?php endif; ?>

        <label for="nume_medicament">Nume Medicament:</label>
        <input type="text" name="nume_medicament" id="nume_medicament" placeholder="Introduceți numele medicamentului"
            required>

        <label for="tip_medicament">Tip Medicament:</label>
        <input type="text" name="tip_medicament" id="tip_medicament" placeholder="Introduceți tipul medicamentului"
            required>

        <label for="pret_medicament">Preț Medicament:</label>
        <input type="number" step="0.01" name="pret_medicament" id="pret_medicament"
            placeholder="Introduceți prețul medicamentului" required>

        <label for="cantitate">Cantitate:</label>
        <input type="number" name="cantitate" id="cantitate" placeholder="Introduceți cantitatea" required>

        <label for="id_furnizor">Furnizor:</label>
        <select name="id_furnizor" id="id_furnizor" required>
            <option value="">Selectează furnizor</option>
            <?php
            // Obținem lista de furnizori din baza de date
            $sqlFurnizori = "SELECT ID_Furnizor, Nume_Furnizor FROM Furnizori";
            $resultFurnizori = $conn->query($sqlFurnizori);
            while ($row = $resultFurnizori->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['ID_Furnizor']) . "'>" . htmlspecialchars($row['Nume_Furnizor']) . "</option>";
            }
            ?>
        </select>

        <button type="submit">Adaugă Medicament</button>
        <a href="admin_dashboard.php" class="back-link">Înapoi la dashboard</a>
    </form>
</body>

</html>