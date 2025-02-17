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

// Preia datele medicamentelor vandute
$sqlMedicamente = "
    SELECT C.ID_Cumparare, M.ID_Medicament, M.Nume_Medicament, M.Tip_Medicament, M.Pret_Medicament, C.Cantitate
    FROM Cumparari C
    JOIN Medicamente M ON C.ID_Medicament = M.ID_Medicament
    ORDER BY C.ID_Cumparare DESC"; // Afișăm cele mai recente cumpărături primele
$resultMedicamente = $conn->query($sqlMedicamente);



$sqlComenzi = "
    SELECT C.ID_Comanda, C.Data_Comanda, C.Total_Comanda, M.Nume_Medicament, M.Tip_Medicament, C.ID_Medicament
    FROM Comenzi C
    JOIN Medicamente M ON C.ID_Medicament = M.ID_Medicament
    ORDER BY C.ID_Comanda DESC";


$resultComenzi = $conn->query($sqlComenzi);


// Interogare pentru cele mai bine vândute medicamente
$sqlBestSellingMedications = "
    SELECT M.Nume_Medicament, 
           M.Tip_Medicament, 
           SUM(C.Cantitate) AS Total_Cantitate
    FROM Medicamente M
    JOIN Cumparari C ON M.ID_Medicament = C.ID_Medicament
    WHERE C.Cantitate = (
        SELECT MAX(Total_Cantitate)
        FROM (
            SELECT SUM(C1.Cantitate) AS Total_Cantitate
            FROM Cumparari C1
            GROUP BY C1.ID_Medicament
        ) AS Subquery
    )
    GROUP BY M.ID_Medicament, M.Nume_Medicament, M.Tip_Medicament";
$resultBestSellingMedications = $conn->query($sqlBestSellingMedications);

// Interogare pentru furnizorii cu cel mai mic preț mediu
$sqlLowestAveragePrice = "
    SELECT T.Nume_Furnizor, T.Pret_Mediu
    FROM (
        SELECT F.Nume_Furnizor, AVG(M.Pret_Medicament) AS Pret_Mediu
        FROM Furnizori F
        JOIN Medicamente_Furnizori MF ON F.ID_Furnizor = MF.ID_Furnizor
        JOIN Medicamente M ON MF.ID_Medicament = M.ID_Medicament
        GROUP BY F.Nume_Furnizor
    ) AS T
    WHERE T.Pret_Mediu = (
        SELECT MIN(Pret_Mediu)
        FROM (
            SELECT AVG(M.Pret_Medicament) AS Pret_Mediu
            FROM Furnizori F
            JOIN Medicamente_Furnizori MF ON F.ID_Furnizor = MF.ID_Furnizor
            JOIN Medicamente M ON MF.ID_Medicament = M.ID_Medicament
            GROUP BY F.Nume_Furnizor
        ) AS Subquery
    )";
$resultLowestAveragePrice = $conn->query($sqlLowestAveragePrice);

// Interogare pentru discountul maxim pentru medicamentele distribuite de furnizorul cu cel mai mare număr de comenzi
$sqlMaxDiscount = "
     SELECT 
        F.Nume_Furnizor, 
        M.Nume_Medicament, 
        MAX(P.Discount) AS Discount_Maxim
    FROM Furnizori F
    JOIN Medicamente_Furnizori MF ON F.ID_Furnizor = MF.ID_Furnizor
    JOIN Medicamente M ON MF.ID_Medicament = M.ID_Medicament
    JOIN Promotii P ON M.ID_Medicament = P.ID_Medicament
    WHERE F.ID_Furnizor = (
        SELECT ID_Furnizor 
        FROM (
            SELECT ID_Furnizor, COUNT(ID_Comanda) AS Nr_Comenzi
            FROM Comenzi
            GROUP BY ID_Furnizor
            ORDER BY Nr_Comenzi DESC
            LIMIT 1
        ) AS SubqueryFurnizor
    )
    GROUP BY F.Nume_Furnizor, M.Nume_Medicament
    ORDER BY Discount_Maxim DESC;";


$resultMaxDiscount = $conn->query($sqlMaxDiscount);


// Interogare pentru utilizatorul cu cele mai multe feedback-uri pozitive
$sqlUserMostPositiveFeedback = "
    SELECT 
        U.NUME, 
        U.PRENUME, 
        COUNT(F.ID_Feedback) AS Nr_Feedbackuri
    FROM USERS U
    JOIN Feedback F ON U.EMAIL = F.ID_User
    WHERE F.Rating >= 4
    GROUP BY U.EMAIL, U.NUME, U.PRENUME
    HAVING Nr_Feedbackuri = (
        SELECT MAX(Nr_Feedbackuri)
        FROM (
            SELECT COUNT(FB.ID_Feedback) AS Nr_Feedbackuri
            FROM Feedback FB
            WHERE FB.Rating >= 4
            GROUP BY FB.ID_User
        ) AS Subquery
    );
";

$resultUserMostPositiveFeedback = $conn->query($sqlUserMostPositiveFeedback);
?>
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furnizor Dashboard</title>
    <link rel="stylesheet" href="style_furnizor_admin.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
            padding: 8px 12px;
        }

        button:hover {
            background-color: #45a049;
        }

        .dropdown-container {
            margin-bottom: 20px;
            text-align: center;
        }

        select {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
        }

        .hidden {
            display: none;
        }
    </style>
    <script>
        function toggleTables() {
            const selectedValue = document.getElementById("tableDropdown").value;

            // Ascundem toate tabelele
            const tables = document.querySelectorAll(".hidden");
            tables.forEach(table => {
                table.style.display = "none"; // Ascundere tabel
            });

            // Afișăm tabela selectată
            if (selectedValue) {
                const selectedTable = document.getElementById(selectedValue);
                if (selectedTable) {
                    selectedTable.style.display = "block"; // Afișare tabel
                }
            }
        }

    </script>
</head>

<div>
    <h1>Bun venit, <?php echo htmlspecialchars($_SESSION['NUME'] . " " . $_SESSION['PRENUME']); ?>!</h1>

    <div class="dropdown-container">
        <label for="tableDropdown" label>
            <select id="tableDropdown" onchange="toggleTables()">
                <br>
                <option value="">Selectează</option>
                <option value="adminTable">Informații user</option>
                <option value="medicamenteTable">Medicamente de vanzare</option>
                <option value="ordersTable">Comenzi</option>
                <option value="BestSellingMedicationsTable">Cele mai bine vandute medicamente</option>
                <option value="lowestPriceTable">Furnizori cu cel mai mic preț mediu</option>
                <option value="MaxDiscountTable">Discountul maxim</option>
                <option value="UserMostPositiveFeedbackTable">Cele mai bune feedback-uri</option>
            </select>
    </div>

    <!-- Tabelul informațiilor userului -->
    <div id="adminTable" class="hidden">

        <table>
            <tr>
                <th>Nume</th>
                <td><?php echo htmlspecialchars($_SESSION['NUME']); ?></td>
            </tr>
            <tr>
                <th>Prenume</th>
                <td><?php echo htmlspecialchars($_SESSION['PRENUME']); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($_SESSION['EMAIL']); ?></td>
            </tr>
            <tr>
                <th>Rol</th>
                <td><?php echo htmlspecialchars($_SESSION['ROL']); ?></td>
            </tr>
        </table>
    </div>

    <!-- Tabelul medicamentelor cumpărate -->
    <div id="medicamenteTable" class="hidden">

        <table>
            <tr>
                <th>ID Cumpărare</th>
                <th>ID Medicament</th>
                <th>Nume Medicament</th>
                <th>Tip Medicament</th>
                <th>Preț Medicament</th>
                <th>Cantitate</th>
                <th>Acțiuni</th>
            </tr>
            <?php if ($resultMedicamente->num_rows > 0): ?>
                <?php while ($row = $resultMedicamente->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_Cumparare']); ?></td>
                        <td><?php echo htmlspecialchars($row['ID_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Tip_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Pret_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Cantitate']); ?></td>
                        <td>
                            <form method="POST" action="delete_medicament.php" style="display:inline;">
                                <input type="hidden" name="id_medicament" value="<?php echo $row['ID_Medicament']; ?>">
                                <button type="submit">Șterge</button>
                            </form>
                            <form method="POST" action="update_medicament.php" style="display:inline;">
                                <input type="hidden" name="id_medicament" value="<?php echo $row['ID_Medicament']; ?>">
                                <button type="submit">Actualizează Cantitatea</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Nu există date disponibile.</td>
                </tr>
            <?php endif; ?>
        </table>


        <form method="POST" action="insert_medicament.php">
            <button type="submit">Adaugă Medicament</button>
        </form>
    </div>

    <!-- Tabelul comenzilor -->
    <div id="ordersTable" class="hidden">

        <table>
            <tr>
                <th>ID Comandă</th>
                <th>Data Comandă</th>
                <th>Total Comandă</th>
                <th>Medicamente</th>
                <th>Acțiuni</th>
            </tr>
            <?php
            $currentComanda = null;
            if ($resultComenzi->num_rows > 0): ?>
                <?php while ($row = $resultComenzi->fetch_assoc()): ?>
                    <?php if ($currentComanda != $row['ID_Comanda']): ?>
                        <?php $currentComanda = $row['ID_Comanda']; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['ID_Comanda']); ?></td>
                            <td><?php echo htmlspecialchars($row['Data_Comanda']); ?></td>
                            <td><?php echo htmlspecialchars($row['Total_Comanda']); ?></td>
                            <td><?php echo htmlspecialchars($row['Nume_Medicament']); ?> -
                                <?php echo htmlspecialchars($row['Tip_Medicament']); ?>
                            </td>
                            <td>
                                <form method="POST" action="delete_comanda.php" style="display:inline;">
                                    <input type="hidden" name="id_comanda" value="<?php echo $row['ID_Comanda']; ?>">
                                    <button type="submit">Șterge</button>
                                </form>
                                <form method="GET" action="update_comanda.php" style="display:inline;">
                                    <input type="hidden" name="id_comanda" value="<?php echo $row['ID_Comanda']; ?>">
                                    <button type="submit">Actualizează</button>
                                </form>

                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="3"></td>
                            <td><?php echo htmlspecialchars($row['Nume_Medicament']); ?> -
                                <?php echo htmlspecialchars($row['Tip_Medicament']); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nu există comenzi disponibile.</td>
                </tr>
            <?php endif; ?>
        </table>
        <form method="POST" action="insert_comanda.php">
            <button type="submit">Comanda noua</button>
        </form>

    </div>



    <!-- Tabelul celor mai bine vandute medicamente -->
    <div id="BestSellingMedicationsTable" class="hidden">

        <table border="1" style="width: 100%; text-align: center;">
            <thead>
                <tr>
                    <th>Nume Medicament</th>
                    <th>Tip Medicament</th>
                    <th>Total Cantitate Vândută</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultBestSellingMedications->num_rows > 0): ?>
                    <?php while ($row = $resultBestSellingMedications->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Nume_Medicament']); ?></td>
                            <td><?php echo htmlspecialchars($row['Tip_Medicament']); ?></td>
                            <td><?php echo htmlspecialchars($row['Total_Cantitate']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Nu există date disponibile.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Tabelul furnizorilor cu cel mai mic preț mediu -->
    <div id="lowestPriceTable" class="hidden">

        <table>
            <tr>
                <th>Nume Furnizor</th>
                <th>Preț Mediu Minim</th>
            </tr>
            <?php if ($resultLowestAveragePrice->num_rows > 0): ?>
                <?php while ($row = $resultLowestAveragePrice->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Nume_Furnizor']); ?></td>
                        <td><?php echo htmlspecialchars($row['Pret_Mediu']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">Nu există date disponibile.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <div id="MaxDiscountTable" class="hidden">

        <table>
            <thead>
                <tr>
                    <th>Nume Furnizor</th>
                    <th>Nume Medicament</th>
                    <th>Discount Maxim (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultMaxDiscount->num_rows > 0): ?>
                    <?php while ($row = $resultMaxDiscount->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Nume_Furnizor']); ?></td>
                            <td><?php echo htmlspecialchars($row['Nume_Medicament']); ?></td>
                            <td><?php echo htmlspecialchars($row['Discount_Maxim']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Nu există date disponibile.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>


    <div id="UserMostPositiveFeedbackTable" class="hidden">

        <table>
            <thead>
                <tr>
                    <th>Nume</th>
                    <th>Prenume</th>
                    <th>Număr Feedback-uri Pozitive</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultUserMostPositiveFeedback->num_rows > 0): ?>
                    <?php while ($row = $resultUserMostPositiveFeedback->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['NUME']); ?></td>
                            <td><?php echo htmlspecialchars($row['PRENUME']); ?></td>
                            <td><?php echo htmlspecialchars($row['Nr_Feedbackuri']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Nu există date disponibile.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>



</html>