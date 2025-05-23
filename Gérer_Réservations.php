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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM Réservation WHERE id_Réservation = ?");
        $stmt->execute([$_POST['id']]);
        exit('Deleted');
    } else {
        $id = $_POST['id'];
        $start = $_POST['start'];
        $end = $_POST['end'];
        $date = substr($start, 0, 10);
        $heure_debut = substr($start, 11, 5);
        $heure_fin = substr($end, 11, 5);
        $stmt = $pdo->prepare("UPDATE Réservation SET Date_Réservation = ?, Heure_début = ?, Heure_fin = ? WHERE id_Réservation = ?");
        $stmt->execute([$date, $heure_debut, $heure_fin, $id]);
        exit('Updated');
    }
}

$events = [];
$reservations = $pdo->query("SELECT * FROM Réservation")->fetchAll(PDO::FETCH_ASSOC);

foreach ($reservations as $r) {
    $title = '';
    $color = '';
    if (!empty($r['id_Salle'])) {
        $stmtSalle = $pdo->prepare("SELECT Nom FROM Salle WHERE id_salle = ?");
        $stmtSalle->execute([$r['id_Salle']]);
        $nomSalle = $stmtSalle->fetchColumn() ?: "Salle inconnue";
        $title = "Salle : $nomSalle";
        $color = "#6ec1e4";
    } elseif (!empty($r['id_Matériel'])) {
        $stmtMat = $pdo->prepare("SELECT Nom FROM Matériel WHERE ID_Matériel = ?");
        $stmtMat->execute([$r['id_Matériel']]);
        $nomMat = $stmtMat->fetchColumn() ?: "Matériel inconnu";
        $title = "Matériel : $nomMat x{$r['Quantité']}";
        $color = "#bb86fc";
    }

    $events[] = [
        'id' => $r['id_Réservation'],
        'title' => $title,
        'start' => $r['Date_Réservation'] . 'T' . $r['Heure_début'],
        'end' => $r['Date_Réservation'] . 'T' . $r['Heure_fin'],
        'color' => $color
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les Réservations</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Gérer_Réservations.css">
</head>
<body>

<div class="container">
    <h1>Gérer les Réservations</h1>

    <div class="legend">
        <div><span class="legend-color" style="background-color: #6ec1e4;"></span> Salle</div>
        <div><span class="legend-color" style="background-color: #bb86fc;"></span> Matériel</div>
    </div>

    <div id="calendar"></div>

    <button class="btn-home" onclick="window.location.href='Accueil_Admin.php'">Retour à l'accueil</button>
</div>

<div id="modal">
    <input type="date" id="edit-date">
    <input type="time" id="edit-start">
    <input type="time" id="edit-end">
    <button id="save-btn">Enregistrer</button>
    <button class="delete" id="delete-btn">Supprimer</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let selectedEvent = null;

        const modal = document.getElementById('modal');
        const dateInput = document.getElementById('edit-date');
        const startInput = document.getElementById('edit-start');
        const endInput = document.getElementById('edit-end');

        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'fr',
            initialView: 'dayGridMonth',
            editable: true,
            eventClick: function (info) {
                selectedEvent = info.event;
                modal.style.display = 'block';

                dateInput.value = selectedEvent.start.toISOString().slice(0, 10);
                startInput.value = selectedEvent.start.toTimeString().slice(0, 5);
                endInput.value = selectedEvent.end.toTimeString().slice(0, 5);
            },
            eventDrop: function (info) {
                updateEvent(info.event);
            },
            eventResize: function (info) {
                updateEvent(info.event);
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: "Aujourd'hui",
                month: 'Mois',
                week: 'Semaine',
                day: 'Jour'
            },
            events: <?= json_encode($events) ?>
        });

        calendar.render();

        document.getElementById('save-btn').addEventListener('click', () => {
            const date = dateInput.value;
            const start = startInput.value;
            const end = endInput.value;

            const startDateTime = new Date(`${date}T${start}`);
            const endDateTime = new Date(`${date}T${end}`);

            selectedEvent.setStart(startDateTime);
            selectedEvent.setEnd(endDateTime);
            updateEvent(selectedEvent);
            modal.style.display = 'none';
        });

        document.getElementById('delete-btn').addEventListener('click', () => {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("id=" + selectedEvent.id + "&delete=1");

            selectedEvent.remove();
            modal.style.display = 'none';
        });

        function updateEvent(event) {
            const startStr = event.start.toLocaleString('sv-SE').replace(' ', 'T');
            const endStr = event.end.toLocaleString('sv-SE').replace(' ', 'T');

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("id=" + event.id + "&start=" + startStr + "&end=" + endStr);
        }

        window.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>
