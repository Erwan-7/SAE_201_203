<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pdo = new PDO("mysql:host=localhost;dbname=SAE_203;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_salle'])) {
    $nom = $_POST['nom'] ?? '';
    $targetDir = "uploads/Salle/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $photoPath = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $fileName = basename($_FILES["photo"]["name"]);
        $photoPath = $targetDir . time() . "_" . $fileName;
        move_uploaded_file($_FILES["photo"]["tmp_name"], $photoPath);
    }

    $stmt = $pdo->prepare("INSERT INTO salle (Nom, Photo) VALUES (?, ?)");
    $stmt->execute([$nom, $photoPath]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'updateField') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];
    $field = $data['field'];
    $value = $data['value'];

    $allowedFields = ['Nom', 'Photo'];
    if (!in_array($field, $allowedFields)) {
        http_response_code(400);
        echo "Champ invalide.";
        exit;
    }

    $stmt = $pdo->prepare("UPDATE salle SET `$field` = :value WHERE id_salle = :id");
    $stmt->execute(['value' => $value, 'id' => $id]);
    echo "Champ mis à jour.";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'delete') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];
    $stmt = $pdo->prepare("DELETE FROM salle WHERE id_salle = ?");
    $stmt->execute([$id]);
    echo "Salle supprimée.";
    exit;
}

$stmt = $pdo->query("SELECT * FROM salle");
$salles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les Salles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Gestion_Salle.css">
</head>
<body>

<div class="main-wrapper">
    <h2>Gérer les Salles</h2>
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Photo (lien)</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($salles as $item): ?>
                <tr data-id="<?= $item['id_salle'] ?>">
                    <td><?= htmlspecialchars($item['id_salle']) ?></td>
                    <?php foreach (['Nom', 'Photo'] as $field): ?>
                        <td>
                            <div class="cell-wrapper">
                                <span class="cell-content"><?= htmlspecialchars($item[$field]) ?></span>
                                <button class="btn btn-outline-dark btn-sm" onclick="editField(this, '<?= $field ?>')">Modifier</button>
                            </div>
                        </td>
                    <?php endforeach; ?>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="deleteRow(this)">Supprimer</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <h2>Ajouter une salle</h2>
    <form action="" method="post" enctype="multipart/form-data" class="mb-5">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <input type="text" name="nom" class="form-control" placeholder="Nom de la salle" required>
            </div>
            <div class="col-auto">
                <input type="file" name="photo" class="form-control" accept="image/*" onchange="previewImage(event)" required>
            </div>
            <div class="col-auto">
                <button type="submit" name="ajouter_salle" class="btn btn-primary">Ajouter</button>
            </div>
        </div>
        <img id="preview">
    </form>

    <a href="Gestion.php" class="footer-btn">Retour à Gestion</a>
</div>


<script>
function editField(button, field) {
    const cell = button.closest("td");
    const wrapper = cell.querySelector(".cell-wrapper");
    const span = wrapper.querySelector(".cell-content");
    const oldValue = span.textContent.trim();

    const input = document.createElement("input");
    input.type = "text";
    input.value = oldValue;
    input.className = "form-control form-control-sm";

    const saveBtn = document.createElement("button");
    saveBtn.textContent = "✔";
    saveBtn.className = "btn btn-success btn-sm mt-1";

    saveBtn.addEventListener('click', function () {
        const newValue = input.value.trim();
        const row = cell.closest("tr");
        const id = row.getAttribute("data-id");

        if (newValue === "") return;

        fetch('?action=updateField', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id, field, value: newValue })
        })
        .then(res => res.text())
        .then(() => {
            const newWrapper = document.createElement("div");
            newWrapper.className = "cell-wrapper";

            const newSpan = document.createElement("span");
            newSpan.className = "cell-content";
            newSpan.textContent = newValue;
            newWrapper.appendChild(newSpan);

            const newEditBtn = document.createElement("button");
            newEditBtn.className = "btn btn-outline-dark btn-sm mt-1";
            newEditBtn.textContent = "Modifier";
            newEditBtn.onclick = () => editField(newEditBtn, field);

            newWrapper.appendChild(newEditBtn);

            cell.innerHTML = '';
            cell.appendChild(newWrapper);

            cell.classList.add("highlight-success");
            setTimeout(() => {
                cell.classList.remove("highlight-success");
            }, 1000);
        });
    });

    cell.innerHTML = '';
    const wrapperDiv = document.createElement("div");
    wrapperDiv.className = "cell-wrapper";
    wrapperDiv.appendChild(input);
    wrapperDiv.appendChild(saveBtn);
    cell.appendChild(wrapperDiv);
}

function deleteRow(button) {
    const row = button.closest("tr");
    const id = row.getAttribute("data-id");

    if (confirm("Supprimer cette salle ?")) {
        fetch('?action=delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id})
        }).then(() => {
            row.style.transition = "opacity 0.6s, transform 0.6s";
            row.style.opacity = 0;
            row.style.transform = "translateX(-50px)";
            setTimeout(() => {
                row.remove();
            }, 600);
        });
    }
}

function previewImage(event) {
    const preview = document.getElementById('preview');
    preview.src = URL.createObjectURL(event.target.files[0]);
    preview.style.display = 'block';
}
</script>

</body>
</html>
