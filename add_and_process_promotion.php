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

// Preia datele din formular
$id_medicament = $_POST['id_medicament'];
$discount = $_POST['discount'];
$data_start = $_POST['data_start'];

// Verifică validitatea datelor primite
if (empty($id_medicament) || empty($discount) || empty($data_start)) {
    die("Toate câmpurile sunt obligatorii!");
}

// Preia prețul inițial al medicamentului
$sqlPret = "SELECT Pret_Medicament FROM Medicamente WHERE ID_Medicament = ?";
$stmt = $conn->prepare($sqlPret);
if (!$stmt) {
    die("Eroare la pregătirea interogării: " . $conn->error);
}
$stmt->bind_param("i", $id_medicament);
$stmt->execute();
$stmt->bind_result($pret_initial);
$stmt->fetch();
$stmt->close();

if ($pret_initial === null) {
    die("Medicamentul nu a fost găsit.");
}

// Calculează prețul redus
$pret_redus = $pret_initial - ($pret_initial * $discount / 100);

// Adaugă promoția în tabela Promotii
$sqlInsert = "INSERT INTO Promotii (ID_Medicament, Discount, Data_Start) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sqlInsert);
if (!$stmt) {
    die("Eroare la pregătirea interogării: " . $conn->error);
}
$stmt->bind_param("ids", $id_medicament, $discount, $data_start);
if (!$stmt->execute()) {
    die("Eroare la inserarea promoției: " . $stmt->error);
}
$stmt->close();

// Actualizează prețul medicamentului
$sqlUpdate = "UPDATE Medicamente SET Pret_Medicament = ? WHERE ID_Medicament = ?";
$stmt = $conn->prepare($sqlUpdate);
if (!$stmt) {
    die("Eroare la pregătirea interogării: " . $conn->error);
}
$stmt->bind_param("di", $pret_redus, $id_medicament);
if (!$stmt->execute()) {
    die("Eroare la actualizarea prețului: " . $stmt->error);
}
$stmt->close();

header("Location: admin_dashboard.php");
?>