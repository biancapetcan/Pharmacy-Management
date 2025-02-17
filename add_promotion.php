<?php
session_start();

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

// Preia lista medicamentelor disponibile
$sqlMedicamente = "SELECT ID_Medicament, Nume_Medicament FROM Medicamente";
$resultMedicamente = $conn->query($sqlMedicamente);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaugă Promoție</title>
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
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        label {
            font-size: 1.1em;
            color: #333;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
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

        button,
        .back-link {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 1em;
            padding: 10px 1px;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover,
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
    <h1>Adaugă o nouă promoție</h1>

    <form method="POST" action="add_and_process_promotion.php">
        <label for="id_medicament">Medicament:</label>
        <select name="id_medicament" required>
            <option value="">Selectează Medicamentul</option>
            <?php while ($row = $resultMedicamente->fetch_assoc()): ?>
                <option value="<?php echo $row['ID_Medicament']; ?>">
                    <?php echo htmlspecialchars($row['Nume_Medicament']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="discount">Discount (%):</label>
        <input type="number" step="0.01" name="discount" required>

        <label for="data_start">Data Start:</label>
        <input type="date" name="data_start" required>

        <button type="submit">Adaugă Promoția</button>
        <a href="admin_dashboard.php" class="back-link">Înapoi la dashboard</a>
    </form>
</body>

</html>