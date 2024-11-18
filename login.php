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

    $sql = "SELECT NUME, PRENUME, EMAIL, PAROLA, ROL FROM USERS WHERE EMAIL = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifică parola
        if ($parola === $user['PAROLA']) {
            // Verifică dacă rolul selectat se potrivește cu cel din baza de date
            if ($rolSelectat === $user['ROL']) {
                // Salvează datele utilizatorului în sesiune
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
                // Redirecționează la o pagină de eroare dacă rolul nu se potrivește
                header("Location: error.php");
                exit();
            }
        } else {
            echo "<p style='color:red;'>Parola este incorectă.</p>";
        }
    } else {
        echo "<p style='color:red;'>Utilizatorul cu acest email nu există.</p>";
    }

    $stmt->close();
}

$conn->close();

?>