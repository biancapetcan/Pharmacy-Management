<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        table {
            border-collapse: collapse;
            width: 50%;
            margin: 20px auto;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <?php
    session_start();

    // Verifică dacă utilizatorul este autentificat
    if (!isset($_SESSION['EMAIL'])) {
        header("Location: login.php"); // Redirecționează la pagina de login dacă nu este autentificat
        exit();
    }
    ?>

    <h1>Bun venit, <?php echo htmlspecialchars($_SESSION['NUME'] . " " . $_SESSION['PRENUME']); ?>!</h1>

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
</body>

</html>