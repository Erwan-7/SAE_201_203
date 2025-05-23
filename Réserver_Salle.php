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

$message = "";

$salles = $pdo->query("SELECT * FROM Salle")->fetchAll(PDO::FETCH_ASSOC);


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


$eventsQuery = $pdo->query("
    SELECT r.Date_Réservation as date, r.Heure_début, r.Heure_fin, s.Nom as salle, s.id_Salle
    FROM Réservation r
    JOIN Salle s ON r.id_Salle = s.id_Salle
");

$events = [];
$salleColors = [];

foreach ($eventsQuery as $row) {
    $idSalle = $row['id_Salle'];

    if (!isset($salleColors[$idSalle])) {
        $salleColors[$idSalle] = generateContrastingColor($idSalle);
    }

    $events[] = [
        'title' => $row['salle'] . " (" . $row['Heure_début'] . " - " . $row['Heure_fin'] . ")",
        'start' => $row['date'],
        'color' => $salleColors[$idSalle]
    ];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_salle = $_POST['id_salle'];
    $date = $_POST['date_reservation'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];

    $check = $pdo->prepare("SELECT * FROM Réservation 
        WHERE id_Salle = :id_salle AND Date_Réservation = :date
        AND (
            (:heure_debut BETWEEN Heure_début AND Heure_fin)
            OR (:heure_fin BETWEEN Heure_début AND Heure_fin)
            OR (Heure_début BETWEEN :heure_debut AND :heure_fin)
        )");

    $check->execute([
        ':id_salle' => $id_salle,
        ':date' => $date,
        ':heure_debut' => $heure_debut,
        ':heure_fin' => $heure_fin
    ]);

    if ($check->rowCount() > 0) {
        $message = "<div class='alert alert-danger'>Salle déjà réservée sur ce créneau.</div>";
    } else {
        $insert = $pdo->prepare("INSERT INTO Réservation 
            (id_Matériel, id_Salle, Date_Réservation, Heure_début, Heure_fin, Quantité, Nom, Prénom)
            VALUES (NULL, :id_salle, :date, :heure_debut, :heure_fin, 1, :nom, :prenom)");

        $insert->execute([
            ':id_salle' => $id_salle,
            ':date' => $date,
            ':heure_debut' => $heure_debut,
            ':heure_fin' => $heure_fin,
            ':nom' => $nom,
            ':prenom' => $prenom
        ]);

        $message = "<div class='alert alert-success'>Réservation effectuée !</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réserver une Salle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Réserver_Salle.css">
</head>
<body>
<div class="container-fluid bg-black text-white min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card p-4 col-md-8 shadow-lg" style="background-color: #1e004d; border-radius: 20px;">
        <h1 class="text-center mb-4">Réserver une Salle</h1>

        <?= $message ?>

        <form method="POST">
            <div class="mb-3">
                <label>Sélectionnez une Salle :</label>
                <select name="id_salle" id="salleSelect" class="form-select" required onchange="updateImage()">
                    <option value="">-- Choisir une salle --</option>
                    <?php foreach ($salles as $salle): ?>
                        <option value="<?= $salle['id_salle'] ?>" data-img="<?= htmlspecialchars($salle['Photo']) ?>">
                            <?= htmlspecialchars($salle['Nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="text-center mb-4">
                <img id="salleImage" src="" alt="Image de la salle sélectionnée" class="img-thumbnail d-none">
            </div>

            <div class="mb-4">
                <div id="calendar"></div>
            </div>

            <div class="mb-3">
                <label>Date de réservation :</label>
                <input type="date" name="date_reservation" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Heure de début :</label>
                <input type="time" name="heure_debut" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Heure de fin :</label>
                <input type="time" name="heure_fin" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Nom :</label>
                <input type="text" name="nom" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Prénom :</label>
                <input type="text" name="prenom" class="form-control" required>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="Accueil_Etudiant.php" class="btn btn-secondary">Retour à l'accueil</a>
                <button type="submit" class="btn btn-primary">Réserver</button>
            </div>
        </form>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/fr.global.min.js"></script>

<script>
    function updateImage() {
        const select = document.getElementById('salleSelect');
        const img = document.getElementById('salleImage');
        const selected = select.options[select.selectedIndex];
        const imgSrc = selected.getAttribute('data-img');

        if (imgSrc) {
            img.src = 'uploads/' + imgSrc;
            img.classList.remove('d-none');
        } else {
            img.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'fr',
            initialView: 'dayGridMonth',
            height: 450,
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
