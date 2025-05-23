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

function generateContrastingColor($id) {
    $hash = md5($id);
    $r = hexdec(substr($hash, 0, 2));
    $g = hexdec(substr($hash, 2, 2));
    $b = hexdec(substr($hash, 4, 2));
    $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
    if ($brightness < 128) {
        $r = min($r + 100, 255);
        $g = min($g + 100, 255);
        $b = min($b + 100, 255);
    }
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

$events = [];
$salleColors = [];
$materielColors = [];

$reservations = $pdo->query("
    SELECT r.Date_Réservation as date, r.Heure_début, r.Heure_fin, s.Nom as salle, m.Nom as materiel, s.id_Salle, m.id_Matériel
    FROM Réservation r
    LEFT JOIN Salle s ON r.id_Salle = s.id_Salle
    LEFT JOIN Matériel m ON r.id_Matériel = m.id_Matériel
");

foreach ($reservations as $row) {
    if ($row['id_Salle']) {
        $id = 'salle_' . $row['id_Salle'];
        if (!isset($salleColors[$id])) {
            $salleColors[$id] = generateContrastingColor($id);
        }
        $events[] = [
            'title' => "Salle : " . $row['salle'] . " (" . $row['Heure_début'] . " - " . $row['Heure_fin'] . ")",
            'start' => $row['date'],
            'color' => $salleColors[$id],
            'display' => 'auto'
        ];
    } elseif ($row['id_Matériel']) {
        $id = 'materiel_' . $row['id_Matériel'];
        if (!isset($materielColors[$id])) {
            $materielColors[$id] = generateContrastingColor($id);
        }
        $events[] = [
            'title' => "Matériel : " . $row['materiel'] . " (" . $row['Heure_début'] . " - " . $row['Heure_fin'] . ")",
            'start' => $row['date'],
            'color' => $materielColors[$id],
            'display' => 'auto'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservations Effectuées</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Réservations_Effectuées.css">
</head>
<body>
<div class="container py-5">
    <h1 class="text-center mb-4">Réservations Effectuées</h1>
    <div id="calendar"></div>

    <div class="return-btn">
        <a href="Accueil_Enseignant.php">Retour à l'accueil</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/fr.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'fr',
            initialView: 'dayGridMonth',
            height: 600,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            buttonText: {
                today: 'Aujourd\'hui',
                month: 'Mois',
                week: 'Semaine',
                day: 'Jour'
            },
            events: <?= json_encode($events) ?>
        });
        calendar.render();
    });
</script>
</body>
</html>
