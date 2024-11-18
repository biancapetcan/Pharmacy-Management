<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formular Utilizator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Adăugare Utilizator</h1>
        <form action="login.php" method="POST">
            <!-- Nume -->
            <div class="form-group">
                <label for="nume">Nume</label>
                <input type="text" id="nume" name="nume" placeholder="Introdu numele" required>
            </div>

            <!-- Prenume -->
            <div class="form-group">
                <label for="prenume">Prenume</label>
                <input type="text" id="prenume" name="prenume" placeholder="Introdu prenumele" required>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Introdu adresa de email" required>
            </div>

            <!-- Parolă -->
            <div class="form-group">
                <label for="parola">Parolă</label>
                <input type="password" id="parola" name="parola" placeholder="Introdu parola" required minlength="6">
            </div>

            <!-- Rol -->
            <div class="form-group">
                <label for="rol">Rol</label>
                <select id="rol" name="rol" required>
                    <option value="Furnizor">Furnizor</option>
                    <option value="Administrator Farmacie">Administrator Farmacie</option>
                </select>
            </div>

            <!-- Submit -->
            <button type="submit">Salvează Utilizator</button>
        </form>
    </div>
</body>

</html>