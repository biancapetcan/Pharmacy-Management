<?php
session_start();

if (!isset($_SESSION['EMAIL'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'baza_date');
if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}

$id_promotion = $_POST['id_promotion'];
$id_medicament = $_POST['id_medicament'];
$discount = $_POST['discount'];
$data_start = $_POST['data_start'];

// Obține prețul inițial al medicamentului
$sqlPret = "SELECT Pret_Medicament FROM Medicamente WHERE ID_Medicament = ?";
$stmt = $conn->prepare($sqlPret);
$stmt->bind_param("i", $id_medicament);
$stmt->execute();
$stmt->bind_result($pret_initial);
$stmt->fetch();
$stmt->close();

// Calculează prețul redus
$pret_redus = $pret_initial - ($pret_initial * $discount / 100);

// Actualizează promoția
$sqlUpdatePromo = "
    UPDATE Promotii 
    SET Discount = ?, Data_Start = ? 
    WHERE ID_Promotie = ?";
$stmt = $conn->prepare($sqlUpdatePromo);
$stmt->bind_param("dsi", $discount, $data_start, $id_promotion);
$stmt->execute();
$stmt->close();

// Actualizează prețul medicamentului
$sqlUpdateMedicament = "UPDATE Medicamente SET Pret_Medicament = ? WHERE ID_Medicament = ?";
$stmt = $conn->prepare($sqlUpdateMedicament);
$stmt->bind_param("di", $pret_redus, $id_medicament);
$stmt->execute();
$stmt->close();

header("Location: admin_dashboard.php");
?>