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

// Verificăm dacă formularul a fost trimis
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_medicament'])) {
    $idMedicament = intval($_POST['id_medicament']);

    // Ștergere medicament
    $sqlDelete = "DELETE FROM Medicamente WHERE ID_Medicament = ?";
    $stmt = $conn->prepare($sqlDelete);
    $stmt->bind_param("i", $idMedicament);

    if ($stmt->execute()) {
        echo "Medicament șters cu succes.";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Eroare la ștergerea medicamentului.";
    }

    $stmt->close();
} else {
    echo "ID-ul medicamentului nu este definit.";
}

$conn->close();
?>