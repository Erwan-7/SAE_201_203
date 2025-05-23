<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Réservation</title>


  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="Choix_Réservation.css">
</head>
<body>


  <header class="container-fluid">
    <div class="row align-items-center">
      <div class="col-2 text-start d-none d-md-block"></div> <!-- Espace vide à gauche -->
      <div class="col-8 text-center">
        <h1 class="main-title">Choix de la Réservation</h1>
      </div>
      <div class="col-2 text-end">
        <img src="Logo_Universite.svg.png" alt="Logo Université Gustave Eiffel" class="logo img-fluid">
      </div>
    </div>
  </header>


  <div class="container-box">
    <p>Que souhaitez-vous faire ?</p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="Réserver_MatérielA.php" class="button">Réserver du matériel</a>
      <a href="Réserver_SalleA.php" class="button">Réserver une Salle</a>
    </div>
  </div>


  <div class="footer">
    <a href="Accueil_Admin.php" class="button">Retour à l'accueil</a>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
