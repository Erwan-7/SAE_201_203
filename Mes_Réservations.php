<?php
session_start();

if (!isset($_SESSION['user']['Nom']) || !isset($_SESSION['user']['Prénom'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$db   = 'SAE_203';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

$nom    = $_SESSION['user']['Nom'];
$prenom = $_SESSION['user']['Prénom'];

$stmt = $pdo->prepare("SELECT * FROM Réservation WHERE Nom = ? AND Prénom = ?");
$stmt->execute([$nom, $prenom]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];
foreach ($reservations as $r) {
    if (!empty($r['id_Salle'])) {
        $stmtSalle = $pdo->prepare("SELECT Nom FROM Salle WHERE id_salle = ?");
        $stmtSalle->execute([$r['id_Salle']]);
        $nomItem = $stmtSalle->fetchColumn() ?: "Salle inconnue";
        $title   = "Salle : $nomItem\n{$r['Heure_début']} → {$r['Heure_fin']}";
        $backgroundColor = '#ff6b6b'; // rouge vif
    } else {
        $stmtMat = $pdo->prepare("SELECT Nom FROM Matériel WHERE ID_Matériel = ?");
        $stmtMat->execute([$r['id_Matériel']]);
        $nomItem = $stmtMat->fetchColumn() ?: "Matériel inconnu";
        $title   = "Matériel : $nomItem x{$r['Quantité']}\n{$r['Heure_début']} → {$r['Heure_fin']}";
        $backgroundColor = '#ffd93d'; // jaune vif
    }

    $events[] = [
        'title' => $title,
        'start' => $r['Date_Réservation'] . 'T' . $r['Heure_début'],
        'end'   => $r['Date_Réservation'] . 'T' . $r['Heure_fin'],
        'backgroundColor' => $backgroundColor,
        'borderColor' => $backgroundColor
    ];
}
$events_json = json_encode($events);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Réservations effectuées</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <link rel="stylesheet" href="Mes_Réservations.css">
</head>
<body>

<div class="header">
  <h1>Réservations effectuées</h1>
</div>

<div class="container calendar-container">
  <div id="calendar"></div>

  <div class="legend">
    <span class="salle">Réservation Salle</span>
    <span class="materiel">Réservation Matériel</span>
  </div>
</div>

<a href="Accueil_Etudiant.php" class="btn-accueil">Retour à l'Accueil</a>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  let calendarEl = document.getElementById('calendar');
  let calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'fr',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    buttonText: {
      today: "Aujourd'hui",
      month: "Mois",
      week: "Semaine",
      day: "Jour"
    },
    events: <?php echo $events_json ?: '[]'; ?>
  });
  calendar.render();
});
</script>

</body>
</html>
