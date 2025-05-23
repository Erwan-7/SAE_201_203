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

    
    $allowedFields = ['Nom', 'Prénom', 'Email', 'Téléphone', 'Date_de_Naissance', 'Adresse', 'Role', 'Numéro_étudiant', 'TP'];
    if (!in_array($field, $allowedFields)) {
        http_response_code(400);
        echo "Champ invalide.";
        exit;
    }

    $stmt = $pdo->prepare("UPDATE utilisateur SET `$field` = :value WHERE id = :id");
    $stmt->execute(['value' => $value, 'id' => $id]);
    echo "Champ mis à jour.";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'delete') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];
    $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
    $stmt->execute([$id]);
    echo "Utilisateur supprimé.";
    exit;
}

$stmt = $pdo->query("SELECT * FROM utilisateur");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer Comptes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Gestion_Compte.css">
</head>
<body>
<div class="main-wrapper">
    <h2>Gérer les Utilisateurs</h2>
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Date de Naissance</th>
                <th>Adresse</th>
                <th>Rôle</th>
                <th>Numéro étudiant</th>
                <th>TP</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($users as $user): ?>
                <tr data-id="<?= $user['id'] ?>">
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <?php foreach (['Nom', 'Prénom', 'Email', 'Téléphone', 'Date_de_Naissance', 'Adresse', 'Role', 'Numéro_étudiant', 'TP'] as $field): ?>
                        <td>
                            <div class="cell-wrapper">
                                <span class="cell-content"><?= htmlspecialchars($user[$field]) ?></span>
                                <?php if (!empty($user[$field])): ?>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="editField(this, '<?= $field ?>')">Modifier</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    <?php endforeach; ?>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="deleteUser(this)">Supprimer</button>
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
    input.type = (field === 'Date_de_Naissance') ? "date" : "text";
    input.value = (field === 'Date_de_Naissance' && oldValue) ? new Date(oldValue).toISOString().split('T')[0] : oldValue;
    input.className = "form-control form-control-sm";

    const saveBtn = document.createElement("button");
    saveBtn.textContent = "✔";
    saveBtn.className = "btn btn-success btn-sm mt-1";

    saveBtn.addEventListener('click', function () {
        const newValue = input.value.trim();
        const row = cell.closest("tr");
        const id = row.getAttribute("data-id");

        if (newValue === "") return;

        fetch('Gestion_Compte.php?action=updateField', {
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
            newEditBtn.className = "btn btn-outline-secondary btn-sm mt-1";
            newEditBtn.textContent = "Modifier";
            newEditBtn.onclick = () => editField(newEditBtn, field);

            newWrapper.appendChild(newEditBtn);

            cell.innerHTML = '';
            cell.appendChild(newWrapper);

            // ✅ Animation de surbrillance verte
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

function deleteUser(button) {
    const row = button.closest("tr");
    const id = row.getAttribute("data-id");

    if (confirm("Supprimer cet utilisateur ?")) {
        fetch('Gestion_Compte.php?action=delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id})
        }).then(() => {
            // 🗑️ Animation de disparition
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
