<?php

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


require_once 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    if (isset($_POST['add_car'])) {
        $marque = ($_POST['marque']);
        $modele = $_POST['modele'];
        $annee = ($_POST['annee']);
        $immatriculation = ($_POST['immatriculation']);

        try {
            $stmt = $conn->prepare("INSERT INTO Voitures (Marque, Modele, Annee, Immatriculation, Disponibilite) 
                                    VALUES (:marque, :modele, :annee, :immatriculation, 1)");
            $stmt->bindParam(':marque', $marque);
            $stmt->bindParam(':modele', $modele);
            $stmt->bindParam(':annee', $annee);
            $stmt->bindParam(':immatriculation', $immatriculation);

            if ($stmt->execute()) {
                $successMessage = "Voiture ajoutée avec succès !";
            } else {
                $errorMessage = "Erreur lors de l'ajout de la voiture.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Erreur : " . $e->getMessage();
        }
    }

    if (isset($_POST['edit_car'])) {
        $id =($_POST['car_id']);
        $marque = ($_POST['marque']);
        $modele = ($_POST['modele']);
        $annee = ($_POST['annee']);
        $immatriculation = ($_POST['immatriculation']);
        $disponibilite = ($_POST['disponibilite']);

        try {
            $stmt = $conn->prepare("UPDATE Voitures SET 
                Marque = :marque, 
                Modele = :modele, 
                Annee = :annee, 
                Immatriculation = :immatriculation, 
                Disponibilite = :disponibilite 
                WHERE ID = :id");
            $stmt->bindParam(':marque', $marque);
            $stmt->bindParam(':modele', $modele);
            $stmt->bindParam(':annee', $annee);
            $stmt->bindParam(':immatriculation', $immatriculation);
            $stmt->bindParam(':disponibilite', $disponibilite);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $successMessage = "Voiture mise à jour avec succès !";
            } else {
                $errorMessage = "Erreur lors de la mise à jour de la voiture.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Erreur : " . $e->getMessage();
        }
    }

    if (isset($_POST['delete_car'])) {
        $id = intval($_POST['car_id']);

        try {
            $stmt = $conn->prepare("DELETE FROM Voitures WHERE ID = :id");
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $successMessage = "Voiture supprimée avec succès !";
            } else {
                $errorMessage = "Erreur lors de la suppression de la voiture.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Tableau de Bord Administrateur</h1>


        <?php if (!empty($successMessage)) { ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php } ?>
        <?php if (!empty($errorMessage)) { ?>
            <div class="alert alert-danger"><?= $errorMessage ?></div>
        <?php } ?>

        
        <h2 class="mt-5">Ajouter une Nouvelle Voiture</h2>
        <form method="POST" action="" class="mb-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="marque" placeholder="Marque" required>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="modele" placeholder="Modèle" required>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="annee" placeholder="Année" required>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="immatriculation" placeholder="Immatriculation" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_car" class="btn btn-success">Ajouter Voiture</button>
                </div>
            </div>
        </form>


        <h2 class="mt-5">Liste des Voitures</h2>
        <?php
        try {
            $query = $conn->query("SELECT * FROM Voitures");
            $voitures = $query->fetchAll();
        } catch (PDOException $e) {
            echo "<p class='text-danger'>Erreur : " . $e->getMessage() . "</p>";
        }
        ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Marque</th>
                    <th>Modèle</th>
                    <th>Année</th>
                    <th>Immatriculation</th>
                    <th>Disponibilité</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($voitures)) {
                    foreach ($voitures as $voiture) { ?>
                        <form method="POST" action="">
                            <tr>
                                <td><?= $voiture['ID'] ?></td>
                                <td><input type="text" name="marque" value="<?= ($voiture['Marque']) ?>" class="form-control"></td>
                                <td><input type="text" name="modele" value="<?= ($voiture['Modele']) ?>" class="form-control"></td>
                                <td><input type="number" name="annee" value="<?= ($voiture['annee']) ?>" class="form-control"></td>
                                <td><input type="text" name="immatriculation" value="<?= ($voiture['Immatriculation']) ?>" class="form-control"></td>
                                <td>
                                    <select name="disponibilite" class="form-select">
                                        <option value="1" <?= $voiture['Disponibilite'] ? 'selected' : '' ?>>Disponible</option>
                                        <option value="0" <?= !$voiture['Disponibilite'] ? 'selected' : '' ?>>Indisponible</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="car_id" value="<?= $voiture['ID'] ?>">
                                    <button type="submit" name="edit_car" class="btn btn-primary btn-sm">Modifier</button>
                                    <button type="submit" name="delete_car" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer cette voiture ?');">Supprimer</button>
                                </td>
                            </tr>
                        </form>

                    <?php }
                } else { ?>
                    <tr>
                        <td colspan="7" class="text-center">Aucune voiture disponible</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="mt-3">
            <a href="logout.php" class="btn btn-danger">Déconnexion</a>
        </div>
    </div>

</body>

</html>













