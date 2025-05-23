<?php 

$host = 'localhost';
$dbname = 'SAE_203';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}


$materiels = $pdo->query("SELECT ID_Matériel, Nom, Photo, Lien, Quantité FROM matériel")->fetchAll(PDO::FETCH_ASSOC);


$materielsNoms = [];
foreach ($materiels as $m) {
    $materielsNoms[$m['ID_Matériel']] = $m['Nom'];
}


$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_materiel = $_POST['id_materiel'];
    $quantite = intval($_POST['quantite']);
    $date_reservation = $_POST['date_reservation'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);

    
    function checkDisponibilite($pdo, $idMat, $date, $hd, $hf) {
        $stmtCheck = $pdo->prepare("
            SELECT Quantité - COALESCE((
                SELECT SUM(Quantité) FROM réservation 
                WHERE id_Matériel = ? AND Date_Réservation = ? 
                AND (
                    (Heure_début < ? AND Heure_fin > ?) OR
                    (Heure_début < ? AND Heure_fin > ?) OR
                    (Heure_début >= ? AND Heure_fin <= ?)
                )
            ), 0) AS dispo
            FROM matériel WHERE ID_Matériel = ?
        ");
        $stmtCheck->execute([
            $idMat,
            $date,
            $hf, $hf,
            $hd, $hd,
            $hd, $hf,
            $idMat
        ]);
        return $stmtCheck->fetchColumn();
    }

    
    $reservations = [
        ['id' => $id_materiel, 'quantite' => $quantite]
    ];

   
    if (!empty($_POST['id_materiel_supp']) && !empty($_POST['quantite_supp'])) {
        $id_materiel_supps = $_POST['id_materiel_supp'];
        $quantite_supps = $_POST['quantite_supp'];

        foreach ($id_materiel_supps as $key => $idMatSupp) {
            $qteSupp = intval($quantite_supps[$key]);
            if ($idMatSupp && $qteSupp > 0) {
                $reservations[] = ['id' => $idMatSupp, 'quantite' => $qteSupp];
            }
        }
    }

    
    $indisponibles = [];
    foreach ($reservations as $res) {
        $dispo = checkDisponibilite($pdo, $res['id'], $date_reservation, $heure_debut, $heure_fin);
        if ($dispo === false || $dispo < $res['quantite']) {
            $indisponibles[] = $res['id'];
        }
    }

    if (!empty($indisponibles)) {
        
        $nomsIndispo = array_map(fn($id) => $materielsNoms[$id] ?? "ID $id", $indisponibles);
        $message = "Réservation impossible. Matériel(s) indisponible(s) : " . implode(', ', $nomsIndispo) . ".";
    } else {
        
        $sql = "INSERT INTO réservation (id_Matériel, Date_Réservation, Heure_début, Heure_fin, Quantité, Nom, Prénom)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        foreach ($reservations as $res) {
            $stmt->execute([
                $res['id'],
                $date_reservation,
                $heure_debut,
                $heure_fin,
                $res['quantite'],
                $nom,
                $prenom
            ]);
        }
        $message = "Réservation effectuée avec succès.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Réserver du Matériel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="Réserver_Matériel.css" rel="stylesheet" />
  <script>
    const materielImages = <?= json_encode(array_column($materiels, 'Photo', 'ID_Matériel')) ?>;
    const materielLinks = <?= json_encode(array_column($materiels, 'Lien', 'ID_Matériel')) ?>;
    const materiels = <?= json_encode($materiels) ?>;

    function updateImage() {
      const select = document.querySelector('select[name="id_materiel"]');
      const image = document.getElementById('materielImage');
      const videoLink = document.getElementById('videoLink');
      const selectedId = select.value;

      image.src = materielImages[selectedId] || "https://via.placeholder.com/200x200.png?text=Sélection";
      if (materielLinks[selectedId]) {
        videoLink.href = materielLinks[selectedId];
        videoLink.textContent = materielLinks[selectedId];
        videoLink.style.display = "inline";
      } else {
        videoLink.href = "#";
        videoLink.textContent = "Aucun lien disponible";
        videoLink.style.display = "none";
      }
    }

    function updateSuppImage(selectElem) {
      const parent = selectElem.closest('.materiel-supp-item');
      const img = parent.querySelector('.supp-image');
      const link = parent.querySelector('.supp-link');
      const id = selectElem.value;

      img.src = materielImages[id] || "https://via.placeholder.com/100x100.png?text=Sélection";
      if (materielLinks[id]) {
        link.href = materielLinks[id];
        link.textContent = materielLinks[id];
        link.style.display = "inline";
      } else {
        link.href = "#";
        link.textContent = "Aucun lien disponible";
        link.style.display = "none";
      }
    }

    document.addEventListener("DOMContentLoaded", () => {
      document.querySelector('select[name="id_materiel"]').addEventListener("change", updateImage);

      document.getElementById('addMaterielSupp').addEventListener('click', () => {
        const container = document.getElementById('materiel-supp-container');

        const div = document.createElement('div');
        div.classList.add('materiel-supp-item', 'mb-3', 'border', 'p-3', 'rounded');

        div.innerHTML = `
          <div>
            <select name="id_materiel_supp[]" class="form-select mb-2" required>
              <option value="">-- Choisir matériel supplémentaire --</option>
              ${materiels.map(m => `<option value="${m.ID_Matériel}">${m.Nom}</option>`).join('')}
            </select>
            <input type="number" name="quantite_supp[]" class="form-control mb-2" placeholder="Quantité" min="1" required>
          </div>
          <div>
            <img src="https://via.placeholder.com/100x100.png?text=Sélection" class="supp-image rounded mb-1" style="width: 100px; height: 100px; object-fit: cover;" alt="Image matériel supp">
            <a href="#" target="_blank" class="text-info supp-link d-block" style="display:none;">Aucun lien disponible</a>
          </div>
          <div>
            <button type="button" class="btn btn-danger btn-sm mt-2 removeMaterielSupp">Supprimer</button>
          </div>
        `;

        container.appendChild(div);

        div.querySelector('.removeMaterielSupp').addEventListener('click', () => {
          div.remove();
        });

        div.querySelector('select').addEventListener('change', function() {
          updateSuppImage(this);
        });
      });
    });
  </script>
</head>
<body>
  <div class="container">
    <h2 class="text-center mt-4 mb-3 text-primary fw-bold">Réserver du Matériel</h2>

    <?php if (!empty($message)): ?>
      <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="container-custom">
      <div class="row">
        <div class="col-md-4 text-center d-flex flex-column align-items-center">
          <img id="materielImage" src="https://via.placeholder.com/200x200.png?text=Sélection" class="img-fluid rounded mb-3" alt="Image Matériel" />
          <div class="mt-auto w-100">
            <a href="Choix_Réservation.php" class="btn btn-outline-primary w-100">Retour à l'Accueil</a>
          </div>
        </div>

        <div class="col-md-8">
          <form method="POST" action="">
            <div class="mb-3">
              <label class="form-label">Sélectionnez un matériel</label>
              <select class="form-select" name="id_materiel" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($materiels as $m): ?>
                  <option value="<?= $m['ID_Matériel'] ?>"><?= htmlspecialchars($m['Nom']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Quantité :</label>
              <input type="number" class="form-control" name="quantite" placeholder="Entrez la quantité" min="1" required />
            </div>

            <div class="mb-3">
              <label class="form-label">Date de réservation :</label>
              <input type="date" class="form-control" name="date_reservation" required />
            </div>

            <div class="mb-3">
              <label class="form-label">Heure de début :</label>
              <input type="time" class="form-control" name="heure_debut" required />
            </div>

            <div class="mb-3">
              <label class="form-label">Heure de fin</label>
              <input type="time" class="form-control" name="heure_fin" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Nom :</label>
              <input type="text" class="form-control" name="nom" required />
            </div>

            <div class="mb-3">
              <label class="form-label">Prénom :</label>
              <input type="text" class="form-control" name="prenom" required />
            </div>

            <div class="mb-3">
              <label class="form-label">Vidéo :</label><br />
              <a id="videoLink" href="#" class="text-info" target="_blank" style="display: none;">Aucun lien disponible</a>
            </div>

            <hr />

            <div id="materiel-supp-container"></div>

            <div class="mb-3">
              <button type="button" id="addMaterielSupp" class="btn btn-secondary">Ajouter matériel supplémentaire</button>
            </div>

            <button type="submit" class="btn btn-primary w-100">Réserver</button>
          </form>
        </div>
      </div>
    </div>
  </div> 
  </body> 


</html>
