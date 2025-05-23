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
    $eventData = [
        'start' => $row['date'],
        'display' => 'auto',
        'extendedProps' => [
            'date' => $row['date'],
            'heure_debut' => $row['Heure_début'],
            'heure_fin' => $row['Heure_fin'],
            'type' => $row['id_Salle'] ? 'Salle' : 'Matériel',
            'nom' => $row['id_Salle'] ? $row['salle'] : $row['materiel']
        ]
    ];
    if ($row['id_Salle']) {
        $id = 'salle_' . $row['id_Salle'];
        if (!isset($salleColors[$id])) {
            $salleColors[$id] = generateContrastingColor($id);
        }
        $eventData['title'] = "Salle : " . $row['salle'] . " (" . $row['Heure_début'] . " - " . $row['Heure_fin'] . ")";
        $eventData['color'] = $salleColors[$id];
    } elseif ($row['id_Matériel']) {
        $id = 'materiel_' . $row['id_Matériel'];
        if (!isset($materielColors[$id])) {
            $materielColors[$id] = generateContrastingColor($id);
        }
        $eventData['title'] = "Matériel : " . $row['materiel'] . " (" . $row['Heure_début'] . " - " . $row['Heure_fin'] . ")";
        $eventData['color'] = $materielColors[$id];
    }
    $events[] = $eventData;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Voir les Réservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Réservations_Effectuées.css">
</head>
<body>
<div class="container py-5">
    <h1 class="text-center mb-4">Réservations Effectuées</h1>
    <div id="calendar"></div>
    <div class="return-btn mt-4">
        <a href="Accueil_Agent.php">Retour à l'accueil</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/fr.global.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

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
            events: <?= json_encode($events) ?>,
            eventDidMount: function(info) {
                
                const titleText = info.event.title;
                info.el.innerHTML = '';

                
                const contentDiv = document.createElement('div');
                contentDiv.style.display = 'flex';
                contentDiv.style.flexDirection = 'column';
                contentDiv.style.alignItems = 'flex-start';
                contentDiv.style.justifyContent = 'center';
                contentDiv.style.width = '100%';
                contentDiv.style.height = '100%';
                contentDiv.style.overflow = 'visible';

                
                const titleDiv = document.createElement('div');
                titleDiv.textContent = titleText;
                titleDiv.style.fontSize = '12px';
                titleDiv.style.paddingBottom = '4px';
                titleDiv.style.whiteSpace = 'normal';
                titleDiv.style.wordBreak = 'break-word';
                titleDiv.style.width = '100%';

                
                const btn = document.createElement('button');
                btn.textContent = '📄 PDF';
                btn.title = 'Télécharger PDF';
                btn.style.background = '#fff';
                btn.style.border = '1px solid #ccc';
                btn.style.borderRadius = '4px';
                btn.style.fontSize = '10px';
                btn.style.padding = '2px 5px';
                btn.style.cursor = 'pointer';
                btn.style.alignSelf = 'flex-start';
                btn.style.marginTop = '4px';

                btn.onclick = function(e) {
                    e.stopPropagation();
                    generatePDF(info.event.extendedProps);
                };

                contentDiv.appendChild(titleDiv);
                contentDiv.appendChild(btn);
                info.el.appendChild(contentDiv);
            }
        });
        calendar.render();
    });

    function generatePDF(data) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(18);
        doc.text("Détails de la Réservation", 20, 20);

        doc.setFontSize(12);
        let y = 40;
        doc.text(`Type : ${data.type}`, 20, y); y += 10;
        doc.text(`${data.type} : ${data.nom}`, 20, y); y += 10;
        doc.text(`Date : ${data.date}`, 20, y); y += 10;
        doc.text(`Heure de début : ${data.heure_debut}`, 20, y); y += 10;
        doc.text(`Heure de fin : ${data.heure_fin}`, 20, y); y += 20;

        doc.setFontSize(10);
        doc.text("Signature Enseignant :", 20, y); y += 10;
        doc.text("_________________________", 20, y); y += 20;

        const fileName = `${data.type}_${data.nom}_${data.date}_${data.heure_debut}.pdf`;
        doc.save(fileName);
    }
</script>

</body>
</html>
