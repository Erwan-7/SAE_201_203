<?php
$host = 'localhost';
$dbname = 'SAE_203';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}


$query = "
    SELECT 
        r.*, 
        s.Nom AS Nom_Salle, 
        m.Nom AS Nom_Materiel
    FROM Réservation r
    LEFT JOIN Salle s ON r.id_Salle = s.id_Salle
    LEFT JOIN Matériel m ON r.id_Matériel = m.ID_Matériel
    ORDER BY r.id_Réservation DESC
";

$reservations = $pdo->query($query);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Réservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Historique.css">
</head>
<body>
<div class="container mt-5 position-relative">
    <h1 class="text-center mb-4">Historique</h1>

    <img src="Logo_Meaux.png" alt="Université" height="50" class="logo-right">

    <div class="info-banner">
        Voici l’historique des actions effectuées sur le site
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Action effectuée</th>
                    <th>Personne</th>
                    <th>Date</th>
                    <th>Heure début</th>
                    <th>Heure fin</th>
                    <th>Détail (Salle / Matériel)</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($reservations as $r): ?>
                <tr>
                    <td>
                        <?= $r['id_Matériel'] ? 'Réservation matériel' : ($r['id_Salle'] ? 'Réservation salle' : 'Action inconnue') ?>
                    </td>
                    <td><?= htmlspecialchars($r['Prénom'] . ' ' . $r['Nom']) ?></td>
                    <td><?= htmlspecialchars($r['Date_Réservation']) ?></td>
                    <td><?= htmlspecialchars($r['Heure_début']) ?></td>
                    <td><?= htmlspecialchars($r['Heure_fin']) ?></td>
                    <td>
                        <?php
                        if ($r['id_Matériel']) {
                            echo htmlspecialchars($r['Nom_Materiel']) . " (x" . htmlspecialchars($r['Quantité']) . ")";
                        } elseif ($r['id_Salle']) {
                            echo htmlspecialchars($r['Nom_Salle']);
                        } else {
                            echo "Non spécifié";
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="Accueil_Admin.php" class="btn btn-custom text-center">Retour à l'Accueil</a>
    </div>
</div>
</body>
</html>
