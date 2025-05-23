<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pdo = new PDO("mysql:host=localhost;dbname=SAE_203;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'updateField') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];
    $field = $data['field'];
    $value = $data['value'];

    $allowedFields = ['Nom', 'Référence', 'Date_Achat', 'Etat', 'Description', 'Photo', 'Lien', 'Quantité'];
    if (!in_array($field, $allowedFields)) {
        http_response_code(400);
        echo "Champ invalide.";
        exit;
    }

    $stmt = $pdo->prepare("UPDATE materiel SET `$field` = :value WHERE ID_Matériel = :id");
    $stmt->execute(['value' => $value, 'id' => $id]);
    echo "Champ mis à jour.";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'delete') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];
    $stmt = $pdo->prepare("DELETE FROM materiel WHERE ID_Matériel = ?");
    $stmt->execute([$id]);
    echo "Matériel supprimé.";
    exit;
}

$stmt = $pdo->query("SELECT * FROM matériel");
$materiels = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer le Matériel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Gestion_Matériel.css">
</head>
<body>

<div class="main-wrapper">
    <h2>Gérer le Matériel</h2>
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Référence</th>
                <th>Date Achat</th>
                <th>État</th>
                <th>Description</th>
                <th>Photo</th>
                <th>Lien</th>
                <th>Quantité</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($materiels as $item): ?>
                <tr data-id="<?= $item['ID_Matériel'] ?>">
                    <td><?= htmlspecialchars($item['ID_Matériel']) ?></td>
                    <?php foreach (['Nom', 'Référence', 'Date_Achat', 'Etat', 'Description', 'Photo', 'Lien', 'Quantité'] as $field): ?>
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
    <a href="Gestion.php" class="footer-btn">Retour à Gestion</a>
</div>

<script>
function editField(button, field) {
    const cell = button.closest("td");
    const wrapper = cell.querySelector(".cell-wrapper");
    const span = wrapper.querySelector(".cell-content");
    const oldValue = span.textContent.trim();

    const input = document.createElement("input");
    input.type = (field === 'Date_Achat') ? "date" : "text";
    input.value = (field === 'Date_Achat' && oldValue) ? new Date(oldValue).toISOString().split('T')[0] : oldValue;
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

    if (confirm("Supprimer ce matériel ?")) {
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
</script>

</body>
</html>
