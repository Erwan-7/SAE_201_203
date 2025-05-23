<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=SAE_203;charset=utf8", "root", "");

if (!isset($_SESSION['user'])) {
    header('Location: Connexion.php');
    exit;
}

$user = [];

if (isset($_SESSION['user']['Email'])) {
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE Email = ?");
    $stmt->execute([$_SESSION['user']['Email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $champ = $_POST['champ'] ?? null;
    $valeur = $_POST['valeur'] ?? null;

    if ($champ && in_array($champ, ['Nom', 'Prénom', 'Email', 'Téléphone', 'Date_de_Naissance', 'Adresse', 'Numéro_étudiant', 'TP'])) {
        $stmt = $pdo->prepare("UPDATE utilisateur SET `$champ` = :valeur WHERE Email = :email");
        $stmt->execute(['valeur' => $valeur, 'email' => $_SESSION['user']['Email']]);
        echo 'success';
        exit;
    } elseif ($champ === 'MDP') {
        $ancien = $_POST['ancien'] ?? '';
        $nouveau = $_POST['nouveau'] ?? '';
        $stmt = $pdo->prepare("SELECT MDP FROM utilisateur WHERE Email = :email");
        $stmt->execute(['email' => $_SESSION['user']['Email']]);
        $mdpActuel = $stmt->fetchColumn();

        if (password_verify($ancien, $mdpActuel)) {
            $hash = password_hash($nouveau, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateur SET MDP = :hash WHERE Email = :email");
            $stmt->execute(['hash' => $hash, 'email' => $_SESSION['user']['Email']]);
            echo 'success';
        } else {
            echo 'erreur_mdp';
        }
        exit;
    }
    echo 'champ_non_autorise';
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon Compte</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="Modifier_Profil.css">
</head>
<body>

<div class="container-profil">
  <h2 class="profil-title">Mon Profil</h2>
  <form method="post" onsubmit="return false;">
    <div class="row g-4">
      <?php foreach ($user as $champ => $valeur): ?>
        <?php if (in_array($champ, ['id', 'Role'])) continue; ?>
        <div class="col-md-6">
          <label class="field-label" for="input-<?= $champ ?>"><?= $champ ?></label>
          <div class="field-container">
            <?php if ($champ === 'MDP'): ?>
              <input type="password" class="editable-input" id="input-<?= $champ ?>" placeholder="••••••••" readonly onclick="togglePasswordEdit()">
              <button class="modifier-btn" type="button" onclick="togglePasswordEdit()">Modifier</button>
            <?php else: ?>
              <input type="text" class="editable-input" id="input-<?= $champ ?>" value="<?= htmlspecialchars($valeur) ?>">
              <button class="modifier-btn" type="button" onclick="modifier('<?= $champ ?>')">Modifier</button>
            <?php endif; ?>
            <span class="check-container" id="check-<?= $champ ?>"></span>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="col-md-6">
        <label class="field-label">Role</label>
        <input class="editable-input" type="text" value="<?= htmlspecialchars($user['Role']) ?>" readonly>
      </div>

      <div class="col-md-12 password-fields" id="password-fields">
        <label class="field-label" for="ancien">Ancien mot de passe</label>
        <input class="editable-input mb-2" type="password" id="ancien" placeholder="Ancien mot de passe">
        <label class="field-label" for="nouveau">Nouveau mot de passe</label>
        <input class="editable-input mb-2" type="password" id="nouveau" placeholder="Nouveau mot de passe">
        <button class="modifier-btn" type="button" onclick="changerMotDePasse()">Enregistrer</button>
      </div>
    </div>
  </form>
  <button class="btn btn-retour" onclick="window.location.href='Accueil_Etudiant.php'">Retour à l'Accueil</button>
</div>

<script>
function modifier(champ) {
  const input = document.getElementById("input-" + champ);
  const valeur = input.value;
  fetch("", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `champ=${encodeURIComponent(champ)}&valeur=${encodeURIComponent(valeur)}`
  })
  .then(res => res.text())
  .then(rep => {
    const check = document.getElementById("check-" + champ);
    if (rep === "success") {
      check.innerHTML = '<span class="success-icon">✔️</span>';
    } else {
      alert("Erreur lors de la mise à jour : " + rep);
    }
  });
}

function togglePasswordEdit() {
  document.getElementById("password-fields").style.display = 'block';
}

function changerMotDePasse() {
  const ancien = document.getElementById("ancien").value;
  const nouveau = document.getElementById("nouveau").value;
  fetch("", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `champ=MDP&ancien=${encodeURIComponent(ancien)}&nouveau=${encodeURIComponent(nouveau)}`
  })
  .then(res => res.text())
  .then(rep => {
    const check = document.getElementById("check-MDP");
    if (rep === "success") {
      check.innerHTML = '<span class="success-icon">✔️</span>';
      document.getElementById("password-fields").style.display = 'none';
    } else if (rep === "erreur_mdp") {
      alert("Mot de passe actuel incorrect.");
    } else {
      alert("Erreur : " + rep);
    }
  });
}
</script>

</body>
</html>
