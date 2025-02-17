<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'baza_date');
if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_comanda = $_POST['id_comanda'];

    $stmt = $conn->prepare("DELETE FROM Comenzi WHERE ID_Comanda = ?");
    $stmt->bind_param("i", $id_comanda);
    if ($stmt->execute()) {
        header("Location: furnizor_dashboard.php");
    } else {
        echo "Eroare la ștergere: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>