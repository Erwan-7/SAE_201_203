<?php session_start(); ?>
<!DOCTYPE html> 
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Accueil Agent</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="Accueil_Agent.css">
  </style>
</head>
<body>

  <header class="position-relative px-4 py-2 d-flex align-items-center">
    <img src="Logo_Universite.svg.png" alt="Logo IUT" height="40" />
    <h1 class="position-absolute top-50 start-50 translate-middle text-center m-0">Accueil</h1>

    <form method="post" action="deconnexion.php" style="margin:0;">
      <button type="submit" class="btn btn-gradient ms-auto">Déconnexion</button>
    </form>
  </header>

  <main class="flex-fill d-flex align-items-center justify-content-center">
    <div class="dashboard">
      <h2>Bonjour (Nom de l’Agent)</h2>
      <p>Voici les réservations :</p>

      <div class="action-buttons">
        <a href="Télécharger_Réservations.php" class="btn btn-action">Voir les Réservations</a>
      </div>
    </div>
  </main>

  <footer>
    © IUT Gustave Effel - Site de Meaux
  </footer>

</body>
</html>
