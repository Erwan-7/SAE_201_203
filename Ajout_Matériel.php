<?php

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $host = "localhost";
    $dbname = "SAE_203";
    $username = "root";
    $password = "";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    } catch (PDOException $e) {
        die("Connexion échouée : " . $e->getMessage());
    }

  
    $nom = $_POST["nom"] ?? '';
    $reference = $_POST["reference"] ?? '';
    $date_achat = $_POST["date_achat"] ?? '';
    $etat = $_POST["etat"] ?? '';
    $description = $_POST["description"] ?? '';
    $lien = $_POST["lien"] ?? '';
    $chemin_photo = null;

    
    if (isset($_FILES['photoInput']) && $_FILES['photoInput']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = basename($_FILES['photoInput']['name']);
        $targetPath = $uploadDir . uniqid() . "_" . $fileName;

        if (move_uploaded_file($_FILES['photoInput']['tmp_name'], $targetPath)) {
            $chemin_photo = $targetPath;
        } else {
            $message = "❌ Erreur lors du téléchargement de la photo.";
        }
    }

    $sql = "INSERT INTO matériel (nom, référence, date_achat, etat, description, lien, photo)
            VALUES (:nom, :reference, :date_achat, :etat, :description, :lien, :photo)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':reference', $reference);
    $stmt->bindParam(':date_achat', $date_achat);
    $stmt->bindParam(':etat', $etat);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':lien', $lien);
    $stmt->bindParam(':photo', $chemin_photo);

    if ($stmt->execute()) {
        $message = "✅ Matériel ajouté avec succès.";
    } else {
        $message = "❌ Une erreur est survenue lors de l'ajout.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter un Matériel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="Ajout_Matériel.css">
</head>
<body>

<div class="container my-5">
  <div class="form-container">
    <h2 class="text-center mb-4">Ajouter un Matériel</h2>

    <?php if ($message): ?>
      <div class="alert alert-info message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="row">
      
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Nom Matériel</label>
            <input type="text" class="form-control" name="nom" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Référence Matériel</label>
            <input type="text" class="form-control" name="reference" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Date d'achat</label>
            <input type="date" class="form-control" name="date_achat" required>
          </div>
          <div class="mb-3">
            <label class="form-label">État Matériel</label>
            <input type="text" class="form-control" name="etat" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3" required></textarea>
          </div>
        </div>

      
        <div class="col-md-6 text-center">
          <label class="form-label">Photo Matériel</label>
          <input type="file" class="form-control" accept="image/*" name="photoInput" id="photoInput">
          <img id="preview" class="preview-img" src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/89/Xbox_one_controller.jpg/800px-Xbox_one_controller.jpg" alt="Aperçu">
          <div class="mt-3">
            <label class="form-label">Lien</label>
            <input type="url" class="form-control" name="lien">
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between mt-4">
        <a href="Accueil_Admin.php" class="btn btn-outline-light">Retour à l'Accueil</a>
        <button type="submit" class="btn btn-purple">Ajouter</button>
      </div>
    </form>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Prévisualisation image
  document.getElementById('photoInput').addEventListener('change', function (e) {
    const reader = new FileReader();
    reader.onload = function () {
      document.getElementById('preview').src = reader.result;
    }
    if (e.target.files[0]) {
      reader.readAsDataURL(e.target.files[0]);
    }
  });
</script>

</body>
</html>

