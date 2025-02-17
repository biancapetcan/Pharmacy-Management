<?php
session_start();

// Setări pentru conectare la baza de date
$servername = "localhost";
$username = "root";
$password = "";
$database = "baza_date";

// Conectarea la baza de date
$conn = new mysqli($servername, $username, $password, $database);

// Verificarea conexiunii
if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}

// Verifică dacă s-a trimis formularul
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $parola = trim($_POST['parola']);
    $rolSelectat = trim($_POST['rol']); // Rolul selectat din formular

    // Verifică dacă toate câmpurile sunt completate
    if (empty($email) || empty($parola) || empty($rolSelectat)) {
        header("Location: errornume.php");
        exit();
    }

    // Interogarea pentru verificarea utilizatorului
    $sql = "SELECT NUME, PRENUME, EMAIL, PAROLA, ROL FROM USERS WHERE EMAIL = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifică parola
        if ($parola !== $user['PAROLA']) {
            header("Location: eroareparola.php");
            exit();
        }

        // Verifică dacă rolul selectat este corect
        if ($rolSelectat !== $user['ROL']) {
            header("Location: error.php");
            exit();
        }

        // Dacă autentificarea este validă
        $_SESSION['NUME'] = $user['NUME'];
        $_SESSION['PRENUME'] = $user['PRENUME'];
        $_SESSION['EMAIL'] = $user['EMAIL'];
        $_SESSION['ROL'] = $user['ROL'];

        // Redirecționează utilizatorul
        if ($user['ROL'] === 'Administrator Farmacie') {
            header("Location: admin_dashboard.php");
        } else if ($user['ROL'] === 'Furnizor') {
            header("Location: furnizor_dashboard.php");
        }
        exit();
    } else {
        // Dacă email-ul nu există în baza de date
        header("Location: eroareemail.php");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>