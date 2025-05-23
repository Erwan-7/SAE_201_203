<?php 
session_start();


$host = 'localhost';
$dbname = 'SAE_203';
$user = 'root';
$pass = '';

$message = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $mdp = $_POST['password'] ?? '';

    if (!$email || !$mdp) {
        $message = 'Veuillez remplir tous les champs.';
    } else {
        $sql = "SELECT * FROM Utilisateur WHERE Email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($mdp, $user['MDP'])) {
            
            $_SESSION['user'] = [
                'Email' => $user['Email'],
                'Nom' => $user['Nom'],
                'Prénom' => $user['Prénom'],
                'Role' => $user['Role']
            ];

            switch ($user['Role']) {
                case 'etudiant':
                    header('Location: Accueil_Etudiant.php');
                    exit();
                case 'enseignant':
                    header('Location: Accueil_Enseignant.php');
                    exit();
                case 'admin':
                    header('Location: Accueil_Admin.php');
                    exit();
                case 'agent':
                    header('Location: Accueil_Agent.php');
                    exit();
                default:
                    $message = "Rôle inconnu.";
            }
        } else {
            $message = "Identifiants incorrects.";
        }
    }
}
?>

<!DOCTYPE html> 
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="Connexion.css">
</head>
<body>

    <form class="form-container" method="POST" action="">
        <h1>Connexion</h1>

        <label for="email">Adresse email</label>
        <input type="email" id="email" name="email" placeholder="exemple@iut.fr" required>

        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>

        <button type="submit">Se connecter</button>

        <?php if ($message): ?>
            <div style="color: red; text-align: center; margin-top: 10px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </form>

</body>
</html>
