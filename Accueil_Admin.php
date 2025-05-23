<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Accueil Admin</title>

 
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="Accueil_Admin.css">
</head>
<body>

  <header class="position-relative px-4 py-2 d-flex align-items-center">
    <img src="Logo_Universite.svg.png" alt="Logo IUT" height="40">
    <h1 class="position-absolute top-50 start-50 translate-middle text-center m-0">Accueil</h1>
    
    
    <form method="post" action="deconnexion.php" style="margin:0;">
      <button type="submit" class="btn btn-gradient ms-auto">Déconnexion</button>
    </form>
  </header>
      

  <main class="flex-fill d-flex align-items-center justify-content-center">
    <div class="dashboard">
      <h2>Bonjour (Nom de l’Admin)</h2>
      <p>Que souhaitez-vous faire ?</p>

      <div class="action-buttons">
        <a href="Choix_Réservation.php" class="btn btn-action">Faire une réservation</a>
        <a href="Gérer_Réservations.php" class="btn btn-action">Gérer les Réservations</a>
        <a href="Ajout_Matériel.php" class="btn btn-action">Ajouter Matériel</a>
        <a href="Gestion.php" class="btn btn-action">GESTION</a>
        <a href="Statistiques.php" class="btn btn-action">STATS</a>
        <a href="Historique.php" class="btn btn-action">HISTORIQUE</a>
      </div>
    </div>
  </main>

  <footer>
    © IUT Gustave Effel - Site de Meaux
  </footer>

</body>
</html>

