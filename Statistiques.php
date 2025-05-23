<?php
$pdo = new PDO("mysql:host=localhost;dbname=SAE_203;charset=utf8", "root", "");


$statsMateriel = $pdo->query("
    SELECT 
        m.Nom,
        COUNT(r.id_Réservation) AS nb_reservations_totales,
        COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(r.Heure_fin, r.Heure_début)) * IFNULL(r.Quantité, 1)) / 3600, 0) AS heures_totales,
        MAX(r.Date_Réservation) AS derniere
    FROM Matériel m
    LEFT JOIN Réservation r ON r.id_Matériel = m.ID_Matériel
    GROUP BY m.ID_Matériel
")->fetchAll(PDO::FETCH_ASSOC);


$statsSalle = $pdo->query("
    SELECT 
        s.Nom,
        COUNT(r.id_Réservation) AS nb_reservations,
        COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(r.Heure_fin, r.Heure_début))) / 3600, 0) AS heures_totales,
        MAX(r.Date_Réservation) AS derniere
    FROM Salle s
    LEFT JOIN Réservation r ON r.id_Salle = s.ID_Salle
    GROUP BY s.ID_Salle
")->fetchAll(PDO::FETCH_ASSOC);

function getStatExtreme($data, $key, $max = true) {
    $filtered = array_filter($data, fn($a) => $a[$key] != null);
    usort($filtered, fn($a, $b) => $max ? $b[$key] <=> $a[$key] : $a[$key] <=> $b[$key]);
    return $filtered[0]['Nom'] ?? 'Aucun';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://rawgit.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>
    <link rel="stylesheet" href="Statistiques.css">
</head>
<body>
<div class="container mt-5 stats-container" id="statBlock">
    <div class="header mb-3">
        <h1>Statistiques</h1>
        <div class="logo-right">
            <img src="Logo_Meaux.png" height="50">
        </div>
    </div>

    <h4 class="text-center mb-4">Vue synthétique</h4>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th></th>
            <th>+ Réservé</th>
            <th>- Réservé</th>
            <th>Dernier réservé</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Matériel</th>
            <td><?= getStatExtreme($statsMateriel, 'nb_reservations_totales', true) ?></td>
            <td><?= getStatExtreme($statsMateriel, 'nb_reservations_totales', false) ?></td>
            <td><?= getStatExtreme($statsMateriel, 'derniere', true) ?></td>
        </tr>
        <tr>
            <th>Salle</th>
            <td><?= getStatExtreme($statsSalle, 'nb_reservations', true) ?></td>
            <td><?= getStatExtreme($statsSalle, 'nb_reservations', false) ?></td>
            <td><?= getStatExtreme($statsSalle, 'derniere', true) ?></td>
        </tr>
        </tbody>
    </table>

    <h4 class="text-center mt-5 mb-3">Détails par Salle</h4>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Salle</th>
            <th>Heures réservées</th>
            <th>Nombre de réservations</th>
            <th>Dernière réservation</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($statsSalle as $s): ?>
            <tr>
                <td><?= $s['Nom'] ?></td>
                <td><?= number_format($s['heures_totales'], 2, '.', '') ?></td>
                <td><?= $s['nb_reservations'] ?></td>
                <td><?= $s['derniere'] ?? 'Jamais' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h4 class="text-center mt-5 mb-3">Détails par Matériel</h4>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Matériel</th>
            <th>Heures réservées</th>
            <th>Nombre de réservations</th>
            <th>Dernière réservation</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($statsMateriel as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['Nom']) ?></td>
                <td><?= number_format($m['heures_totales'], 2, '.', '') ?></td>
                <td><?= $m['nb_reservations_totales'] ?></td>
                <td><?= $m['derniere'] ?? 'Jamais' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h4 class="text-center mt-5">Graphiques</h4>
    <div class="graph-container mt-5">
        <div class="mb-4">
            <canvas id="salleChart"></canvas>
        </div>
        <div>
            <canvas id="materielChart"></canvas>
        </div>
    </div>

    <div id="noPrint" class="d-flex justify-content-between mt-5">
        <a href="Accueil_Admin.php" class="btn btn-custom">Retour à l'accueil</a>
        <div>
            <button onclick="downloadCSV()" class="btn btn-custom me-2">Télécharger CSV</button>
            <button onclick="downloadPDF()" class="btn btn-custom">Télécharger PDF</button>
        </div>
    </div>
</div>

<script>
const salleLabels = <?= json_encode(array_column($statsSalle, 'Nom')) ?>;
const salleData = <?= json_encode(array_map('intval', array_column($statsSalle, 'nb_reservations'))) ?>;
const materielLabels = <?= json_encode(array_column($statsMateriel, 'Nom')) ?>;
const materielData = <?= json_encode(array_map('intval', array_column($statsMateriel, 'nb_reservations_totales'))) ?>;

function createChart(ctxId, labels, data, title) {
    const ctx = document.getElementById(ctxId).getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: title,
                data: data,
                backgroundColor: '#7b2cbf',
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { labels: { color: '#fff' } },
                title: {
                    display: true,
                    text: title,
                    color: '#fff'
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#fff',
                        precision: 0,
                        callback: value => Math.round(value),
                    },
                    beginAtZero: true
                },
                y: {
                    ticks: { color: '#fff' }
                }
            }
        }
    });
}

createChart('salleChart', salleLabels, salleData, 'Nombre de Réservations - Salles');
createChart('materielChart', materielLabels, materielData, 'Nombre de Réservations - Matériel');

function downloadCSV() {
    let csvContent = 'Catégorie;Nom;Heures réservées;Nombre de réservations;Dernière réservation\n';

    <?php foreach ($statsSalle as $s): ?>
    csvContent += [
        "Salle",
        "<?= str_replace('"', '""', $s['Nom']) ?>",
        "<?= number_format($s['heures_totales'], 2, ',', '') ?>",
        "<?= $s['nb_reservations'] ?>",
        "<?= $s['derniere'] ?? 'Jamais' ?>"
    ].map(field => `"${field}"`).join(';') + "\n";
    <?php endforeach; ?>

    <?php foreach ($statsMateriel as $m): ?>
    csvContent += [
        "Matériel",
        "<?= str_replace('"', '""', $m['Nom']) ?>",
        "<?= number_format($m['heures_totales'], 2, ',', '') ?>",
        "<?= $m['nb_reservations_totales'] ?>",
        "<?= $m['derniere'] ?? 'Jamais' ?>"
    ].map(field => `"${field}"`).join(';') + "\n";
    <?php endforeach; ?>

    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', 'statistiques.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}


function downloadPDF() {
    const element = document.getElementById('statBlock');
    const buttons = document.getElementById('noPrint');

    const canvases = element.querySelectorAll('canvas');
    canvases.forEach(canvas => {
        const img = document.createElement('img');
        img.src = canvas.toDataURL("image/png");
        img.style.width = '100%';
        img.style.marginTop = '20px';
        canvas.parentNode.replaceChild(img, canvas);
    });

    buttons.style.display = 'none';

    const opt = {
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: 'statistiques.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
            scale: 2,
            useCORS: true,
            scrollY: 0,
            scrollX: 0,
            windowWidth: document.body.scrollWidth,
            windowHeight: document.body.scrollHeight
        },
        jsPDF: {
            unit: 'in',
            format: 'a4',
            orientation: 'portrait'
        }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        buttons.style.display = 'flex';
        location.reload();
    });
}
</script>
</body>
</html>
