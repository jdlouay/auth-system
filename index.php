<?php

session_start();


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo "<p class='text-danger'>Erreur : Identifiant utilisateur introuvable. Veuillez vous reconnecter.</p>";
    exit();
}


require_once 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['search_cars'])) {
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'];

    
        if (empty($date_debut) || empty($date_fin)) {
            $errorMessage = "Veuillez remplir toutes les dates.";
        } elseif ($date_debut > $date_fin) {
            $errorMessage = "La date de début doit être antérieure à la date de fin.";
        } else {
            try {
                $stmt = $conn->prepare("
                    SELECT * 
                    FROM voitures 
                    WHERE ID NOT IN (
                        SELECT Voiture_ID 
                        FROM réservations  
                        WHERE (Date_debut BETWEEN :date_debut AND :date_fin)
                           OR (Date_fin BETWEEN :date_debut AND :date_fin)

                    )
                    AND Disponibilite = 1
                ");
                $stmt->bindParam(':date_debut', $date_debut);
                $stmt->bindParam(':date_fin', $date_fin);
                $stmt->execute();
                $voitures_disponibles = $stmt->fetchAll();
            } catch (PDOException $e) {
                $errorMessage = "Erreur : " . $e->getMessage();
            }
        }
    }

    
    if (isset($_POST['reserve_car'])) {
        $voiture_id = ($_POST['voiture_id']);
        $date_debut = $_POST['hidden_date_debut'];
        $date_fin = $_POST['hidden_date_fin'];
        $client_id = $_SESSION['user_id']; 

        try {
            
            $stmt = $conn->prepare("
                INSERT INTO réservations  (Client_ID, Voiture_ID, Date_debut, Date_fin) 
                VALUES (:client_id, :voiture_id, :date_debut, :date_fin)
            ");
            $stmt->bindParam(':client_id', $client_id);
            $stmt->bindParam(':voiture_id', $voiture_id);
            $stmt->bindParam(':date_debut', $date_debut);
            $stmt->bindParam(':date_fin', $date_fin);

            if ($stmt->execute()) {
                $successMessage = "Réservation confirmée !";
            } else {
                $errorMessage = "Erreur lors de la réservation.";
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
    <title>Réservation de Voiture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Réservation de Voiture</h1>

        <?php if (!empty($successMessage)) { ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php } ?>
        <?php if (!empty($errorMessage)) { ?>
            <div class="alert alert-danger"><?= $errorMessage ?></div>
        <?php } ?>

        
        <h2 class="mt-5">Recherche de Voitures Disponibles</h2>
        <form method="POST" action="" class="mb-3">
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="date_debut" class="form-label">Date de Début</label>
                    <input type="date" class="form-control" name="date_debut" id="date_debut" required>
                </div>
                <div class="col-md-5">
                    <label for="date_fin" class="form-label">Date de Fin</label>
                    <input type="date" class="form-control" name="date_fin" id="date_fin" required>
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" name="search_cars" class="btn btn-primary">Rechercher</button>
                </div>
            </div>
        </form>

       
        <?php if (isset($voitures_disponibles) && count($voitures_disponibles) > 0) { ?>
            <h2 class="mt-5">Voitures Disponibles</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Marque</th>
                        <th>Modèle</th>
                        <th>Année</th>
                        <th>Immatriculation</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($voitures_disponibles as $voiture) { ?>
                        <tr>
                            <td><?= $voiture['ID'] ?></td>
                            <td><?= $voiture['Marque'] ?></td>
                            <td><?= $voiture['Modele'] ?></td>
                            <td><?= $voiture['annee'] ?></td>
                            <td><?= $voiture['Immatriculation'] ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="voiture_id" value="<?= $voiture['ID'] ?>">
                                    <input type="hidden" name="hidden_date_debut" value="<?= $_POST['date_debut'] ?>">
                                    <input type="hidden" name="hidden_date_fin" value="<?= $_POST['date_fin'] ?>">
                                    <button type="submit" name="reserve_car" class="btn btn-success btn-sm">Réserver</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>

            </table>

        <?php } elseif (isset($_POST['search_cars'])) { ?>
            <p class="text-danger">Aucune voiture disponible pour les dates sélectionnées.</p>
        <?php } ?>
        <button class="btn btn-secondary" onclick="history.back();">Retour</button>
        <div class="mt-3">
            <a href="logout.php" class="btn btn-danger">Déconnexion</a>
        </div>
    </div>
</body>

</html>
