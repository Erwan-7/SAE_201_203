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
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $dateNaissance = $_POST['date-naissance'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $role = $_POST['role'] ?? '';
    $numeroEtudiant = $_POST['numero-etudiant'] ?? null;
    $tp = $_POST['tp'] ?? null;
    $mdp = $_POST['password'] ?? '';

    if (!$nom || !$prenom || !$email || !$telephone || !$dateNaissance || !$adresse || !$role || !$mdp) {
        $message = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        $mdpHash = password_hash($mdp, PASSWORD_DEFAULT);

        $sql = "INSERT INTO Utilisateur
                (Nom, Prénom, Email, Téléphone, Date_de_Naissance, Adresse, Role, Numéro_étudiant, TP, MDP)
                VALUES
                (:nom, :prenom, :email, :telephone, :date_naissance, :adresse, :role, :numero_etudiant, :tp, :mdp)";
        
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':telephone' => $telephone,
                ':date_naissance' => $dateNaissance,
                ':adresse' => $adresse,
                ':role' => $role,
                ':numero_etudiant' => $numeroEtudiant,
                ':tp' => $tp,
                ':mdp' => $mdpHash
            ]);

            
            $_SESSION['user'] = [
                'Email' => $email,
                'Nom' => $nom,
                'Prénom' => $prenom,
                'Role' => $role
            ];

            
            switch ($role) {
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
                    $message = "Compte créé avec succès, mais rôle inconnu.";
            }

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "Erreur : cet email est déjà utilisé.";
            } else {
                $message = "Erreur : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Inscription</title>
  <link rel="stylesheet" href="Inscription.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

  <div class="form-container">
    <form method="POST" action="">
      <h1>Inscription</h1>

      <label for="prenom">Prénom</label>
      <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" required />

      <label for="nom">Nom</label>
      <input type="text" id="nom" name="nom" placeholder="Votre nom" required />

      <label for="email">Adresse email</label>
      <input type="email" id="email" name="email" placeholder="exemple@iut.fr" required />

      <label for="role">Rôle</label>
      <select id="role" name="role" required onchange="handleRoleChange()">
        <option value="">-- Sélectionner --</option>
        <option value="etudiant">Étudiant</option>
        <option value="admin">Administrateur</option>
        <option value="agent">Agent</option>
        <option value="enseignant">Enseignant</option>
      </select>

      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" placeholder="••••••••" required />

      <label for="date-naissance">Date de naissance</label>
      <input type="date" id="date-naissance" name="date-naissance" required />

      <div id="studentFields" class="d-none">
        <label for="numero-etudiant">N° étudiant</label>
        <input type="text" id="numero-etudiant" name="numero-etudiant" placeholder="Votre N° étudiant" />

        <label for="tp">TP</label>
        <input type="text" id="tp" name="tp" placeholder="TP" />
      </div>

      <label for="adresse">Adresse</label>
      <input type="text" id="adresse" name="adresse" placeholder="Votre Adresse" required />

      <label for="telephone">Téléphone</label>
      <input type="text" id="telephone" name="telephone" placeholder="Téléphone" required /><br /><br />

      <button type="submit">S'inscrire</button>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $message): ?>
      <div style="color: white; margin-top: 15px; font-weight: bold; text-align: center;">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>
  </div>

  <script>
    function handleRoleChange() {
      const role = document.getElementById("role").value;
      const studentFields = document.getElementById("studentFields");
      if (role === "etudiant") {
        studentFields.classList.remove("d-none");
      } else {
        studentFields.classList.add("d-none");
      }
    }
    window.onload = handleRoleChange;
  </script>

</body>


</html>


