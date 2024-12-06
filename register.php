<?php 
require "includes/header.php"; 
require "config.php"; 

session_start(); 


if (isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

if (isset($_POST['submit'])) {
    if (empty($_POST['email']) || empty($_POST['username']) || empty($_POST['password'])) {
        echo "<p class='text-danger text-center'>Veuillez remplir tous les champs.</p>";
    } else {
    
        $email = ($_POST['email']);
        $username = ($_POST['username']);
        $password = ($_POST['password']);

        try {
            
            $insert = $conn->prepare("INSERT INTO clients (Nom, Adresse, `Numéro de téléphone`, Email, `Mot de passe`, Role) 
                VALUES (:username, 'Adresse par défaut', '0000000000', :email, :mypassword, 'client')");

            $insert->execute([
                ':email' => $email,
                ':username' => $username,
                ':mypassword' => password_hash($password, PASSWORD_DEFAULT), 
            ]);

            echo "<p class='text-success text-center'>Inscription réussie ! Vous pouvez maintenant <a href='login.php'>vous connecter</a>.</p>";

        } catch (PDOException $e) {
            echo "<p class='text-danger text-center'>Erreur : " . $e->getMessage() . "</p>";
        }
    }
}
?>

<main class="form-signin w-50 m-auto">
    <form method="POST" action="register.php">
        <h1 class="h3 mt-5 fw-normal text-center">Inscription</h1>

        <div class="form-floating">
            <input name="email" type="email" class="form-control" id="floatingInput" placeholder="name@example.com" required>
            <label for="floatingInput">Adresse email</label>
        </div>

        <div class="form-floating">
            <input name="username" type="text" class="form-control" id="floatingInput" placeholder="Nom" required>
            <label for="floatingInput">Nom</label>
        </div>

        <div class="form-floating">
            <input name="password" type="password" class="form-control" id="floatingPassword" placeholder="Mot de passe" required>
            <label for="floatingPassword">Mot de passe</label>
        </div>

        <button name="submit" class="w-100 btn btn-lg btn-primary" type="submit">S'inscrire</button>
        <h6 class="mt-3">Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a></h6>
    </form>
</main>

<?php require "includes/footer.php"; ?>
