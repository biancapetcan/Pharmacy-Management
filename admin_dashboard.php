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

// Preia datele medicamentelor cumpărate
$sqlMedicamenteFurnizori = "
    SELECT M.ID_Medicament, M.Nume_Medicament, M.Tip_Medicament, M.Pret_Medicament,
           F.ID_Furnizor, F.Nume_Furnizor, F.Telefon
    FROM Medicamente M
    LEFT JOIN Medicamente_Furnizori MF ON M.ID_Medicament = MF.ID_Medicament
    LEFT JOIN Furnizori F ON MF.ID_Furnizor = F.ID_Furnizor
    HAVING M.Pret_Medicament > 12 
    ORDER BY M.ID_Medicament";
$resultMedicamenteFurnizori = $conn->query($sqlMedicamenteFurnizori);

$sqlIstoricCumparari = "
    SELECT M.ID_Medicament, M.Nume_Medicament, M.Tip_Medicament, M.Pret_Medicament,
           C.ID_Cumparare, C.Data_Vanzare, C.Cantitate
    FROM Medicamente M
    JOIN Cumparari C ON M.ID_Medicament = C.ID_Medicament
    ORDER BY C.Data_Vanzare DESC";
$resultIstoricCumparari = $conn->query($sqlIstoricCumparari);

$sqlStocuri = "
    SELECT M.ID_Medicament, M.Nume_Medicament, M.Tip_Medicament, M.Pret_Medicament, S.Cantitate
    FROM Medicamente M
    JOIN Stocuri S ON M.ID_Medicament = S.ID_Medicament
    ORDER BY M.ID_Medicament";
$resultStocuri = $conn->query($sqlStocuri);

$sqlComenzileMele = "
    SELECT 
        F.Nume_Furnizor, 
        C.Data_Comanda, 
        C.Total_Comanda, 
        M.Nume_Medicament
    FROM Comenzi C
    JOIN Medicamente M ON C.ID_Medicament = M.ID_Medicament
    JOIN Furnizori F ON C.ID_Furnizor = F.ID_Furnizor
    JOIN USERS U ON U.EMAIL = ?
    WHERE U.ROL = 'Administrator Farmacie'";
$stmt = $conn->prepare($sqlComenzileMele);
$stmt->bind_param("s", $_SESSION['EMAIL']); // Email-ul administratorului logat
$stmt->execute();
$resultComenzileMele = $stmt->get_result();

$sqlCart = "
    SELECT 
        M.ID_Medicament, 
        M.Nume_Medicament, 
        M.Pret_Medicament, 
        S.Cantitate AS Stoc_Disponibil,
        F.Nume_Furnizor
    FROM Medicamente M
    JOIN Stocuri S ON M.ID_Medicament = S.ID_Medicament
    JOIN Medicamente_Furnizori MF ON M.ID_Medicament = MF.ID_Medicament
    JOIN Furnizori F ON MF.ID_Furnizor = F.ID_Furnizor
    ORDER BY M.ID_Medicament";
$resultCart = $conn->query($sqlCart);

// Interogare pentru feedback medicamente
$sqlFeedbackMedicamente = "
    SELECT 
        F.ID_Feedback, 
        F.ID_User, 
        U.NUME AS Nume_Utilizator, 
        M.Nume_Medicament, 
        F.Rating, 
        F.Comentariu, 
        F.Data_Feedback
    FROM Feedback F
    JOIN USERS U ON F.ID_User = U.EMAIL
    JOIN Medicamente M ON F.ID_Medicament = M.ID_Medicament
    ORDER BY F.Data_Feedback DESC";
$resultFeedbackMedicamente = $conn->query($sqlFeedbackMedicamente);

// Interogare pentru feedback furnizori
$sqlFeedbackFurnizori = "
    SELECT 
        F.ID_Feedback, 
        F.ID_User, 
        U.NUME AS Nume_Utilizator, 
        FN.Nume_Furnizor, 
        F.Rating, 
        F.Comentariu, 
        F.Data_Feedback
    FROM Feedback F
    JOIN USERS U ON F.ID_User = U.EMAIL
    JOIN Furnizori FN ON F.ID_Furnizor = FN.ID_Furnizor
    ORDER BY F.Data_Feedback DESC";
$resultFeedbackFurnizori = $conn->query($sqlFeedbackFurnizori);


// Interogare pentru medicamente la promoție
$sqlPromotii = "
    SELECT 
        P.ID_Promotie, 
        M.ID_Medicament, 
        M.Nume_Medicament, 
        M.Pret_Medicament, 
        P.Discount, 
        F.Nume_Furnizor, 
        P.Data_Start
    FROM Promotii P
    JOIN Medicamente M ON P.ID_Medicament = M.ID_Medicament
    JOIN Medicamente_Furnizori MF ON M.ID_Medicament = MF.ID_Medicament
    JOIN Furnizori F ON MF.ID_Furnizor = F.ID_Furnizor
    ORDER BY P.Data_Start ASC";
$resultPromotii = $conn->query($sqlPromotii);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
                <option value="medicamenteTable">Medicamente Cumpărate</option>
                <option value="medicamenteFurnizoriTable">Lista de medicamente si furnizori</option>
                <option value="stocuriTable">Stocuri Disponibile</option>
                <option value="comenzileMeleTable">Comenzile Mele</option>
                <option value="cartTable">Cos de cumparaturi</option>
                <option value="feedbackMedicamenteTable">Feedback Medicamente</option>
                <option value="feedbackFurnizoriTable">Feedback Furnizori</option>
                <option value="promotiiTable">Medicamente la Promoție</option>
            </select>

    </div>


    <!-- Tabelul informațiilor adminului -->
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
                <th>ID Medicament</th>
                <th>Nume Medicament</th>
                <th>Tip Medicament</th>
                <th>Preț Medicament</th>
                <th>ID Cumpărare</th>
                <th>Data Vânzare</th>
                <th>Cantitate</th>
                <th>Acțiuni</th>
            </tr>
            <?php if ($resultIstoricCumparari->num_rows > 0): ?>
                <?php while ($row = $resultIstoricCumparari->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Tip_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Pret_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['ID_Cumparare']); ?></td>
                        <td><?php echo htmlspecialchars($row['Data_Vanzare']); ?></td>
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
                    <td colspan="8">Nu există date disponibile.</td>
                </tr>
            <?php endif; ?>
        </table>

        <form method="POST" action="insert_medicament.php">
            <button type="submit">Adaugă Medicament</button>
        </form>
    </div>

    <!-- Tabelul listei de medicament -->
    <div id="medicamenteFurnizoriTable" class="hidden">

        <table>
            <tr>
                <th>ID Medicament</th>
                <th>Nume Medicament</th>
                <th>Tip Medicament</th>
                <th>Preț Medicament</th>
                <th>ID Furnizor</th>
                <th>Nume Furnizor</th>
                <th>Telefon Furnizor</th>
                <th>Acțiuni</th>
            </tr>
            <?php if ($resultMedicamenteFurnizori->num_rows > 0): ?>
                <?php while ($row = $resultMedicamenteFurnizori->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Tip_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Pret_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['ID_Furnizor'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Furnizor'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['Telefon'] ?? 'N/A'); ?></td>
                        <td>
                            <form method="POST" action="delete_medicament.php" style="display:inline;">
                                <input type="hidden" name="id_medicament" value="<?php echo $row['ID_Medicament']; ?>">
                                <button type="submit">Șterge</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">Nu există date disponibile.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>


    <div id="stocuriTable" class="hidden">

        <table>
            <tr>
                <th>ID Medicament</th>
                <th>Nume Medicament</th>
                <th>Tip Medicament</th>
                <th>Preț Medicament</th>
                <th>Cantitate Disponibilă</th>
            </tr>
            <?php if ($resultStocuri->num_rows > 0): ?>
                <?php while ($row = $resultStocuri->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Tip_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Pret_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Cantitate']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nu există date disponibile.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>




    <div id="comenzileMeleTable" class="hidden">

        <table>
            <tr>
                <th>Furnizor</th>
                <th>Data Comenzii</th>
                <th>Prețul Comenzii</th>
                <th>Medicament</th>
            </tr>
            <?php if ($resultComenzileMele->num_rows > 0): ?>
                <?php while ($row = $resultComenzileMele->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Nume_Furnizor']); ?></td>
                        <td><?php echo htmlspecialchars($row['Data_Comanda']); ?></td>
                        <td><?php echo htmlspecialchars($row['Total_Comanda']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Medicament']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Nu există comenzi disponibile.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>




    <div id="cartTable" class="hidden">

        <table>
            <tr>
                <th>ID Medicament</th>
                <th>Nume Medicament</th>
                <th>Preț Medicament</th>
                <th>Stoc Disponibil</th>
                <th>Furnizor</th>

            </tr>
            <?php if ($resultCart->num_rows > 0): ?>
                <?php while ($row = $resultCart->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Pret_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Stoc_Disponibil']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Furnizor']); ?></td>

                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Nu există medicamente disponibile.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>



    <!-- Tabelul feedback medicamente -->
    <div id="feedbackMedicamenteTable" class="hidden">

        <table>
            <tr>
                <th>ID Feedback</th>
                <th>Utilizator</th>
                <th>Nume Medicament</th>
                <th>Rating</th>
                <th>Comentariu</th>
                <th>Data Feedback</th>
            </tr>
            <?php if ($resultFeedbackMedicamente->num_rows > 0): ?>
                <?php while ($row = $resultFeedbackMedicamente->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_Feedback']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Utilizator']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Rating']); ?></td>
                        <td><?php echo htmlspecialchars($row['Comentariu']); ?></td>
                        <td><?php echo htmlspecialchars($row['Data_Feedback']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Nu există feedback disponibil.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Tabelul feedback furnizori -->
    <div id="feedbackFurnizoriTable" class="hidden">

        <table>
            <tr>
                <th>ID Feedback</th>
                <th>Utilizator</th>
                <th>Nume Furnizor</th>
                <th>Rating</th>
                <th>Comentariu</th>
                <th>Data Feedback</th>
            </tr>
            <?php if ($resultFeedbackFurnizori->num_rows > 0): ?>
                <?php while ($row = $resultFeedbackFurnizori->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_Feedback']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Utilizator']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Furnizor']); ?></td>
                        <td><?php echo htmlspecialchars($row['Rating']); ?></td>
                        <td><?php echo htmlspecialchars($row['Comentariu']); ?></td>
                        <td><?php echo htmlspecialchars($row['Data_Feedback']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Nu există feedback disponibil.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Tabelul medicamentelor la promoție -->
    <div id="promotiiTable" class="hidden">

        <table>
            <tr>
                <th>ID Promoție</th>
                <th>Nume Medicament</th>
                <th>Preț Medicament</th>
                <th>Discount</th>
                <th>Furnizor</th>
                <th>Data Start</th>
                <th>Acțiuni</th>
            </tr>
            <?php if ($resultPromotii->num_rows > 0): ?>
                <?php while ($row = $resultPromotii->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_Promotie']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Pret_Medicament']); ?></td>
                        <td><?php echo htmlspecialchars($row['Discount'] . '%'); ?></td>
                        <td><?php echo htmlspecialchars($row['Nume_Furnizor']); ?></td>
                        <td><?php echo htmlspecialchars($row['Data_Start']); ?></td>


                        <td>

                            <a href="edit_promotin.php?id_promotion=<?php echo $row['ID_Promotie']; ?>">
                                <button>Modifică Promoția</button>
                            </a>
                        </td>

                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">Nu există promoții active.</td>
                </tr>


            <?php endif; ?>

        </table>

        <a href="add_promotion.php">
            <button>Adaugă o nouă promoție</button>
        </a>
    </div>


</html>