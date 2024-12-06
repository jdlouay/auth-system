<?php
require "includes/header.php";
require "config.php";

session_start();

if (isset($_POST['submit'])) {
    $email = ($_POST['email']);
    $password = ($_POST['password']);

    if (empty($email) || empty($password)) {
        echo "<script>alert('Veuillez remplir tous les champs.');</script>";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM clients WHERE Email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($password, $data['Mot de passe'])) {
                    $_SESSION['user_id'] = $data['ID'];

                    $_SESSION['username'] = $data['Nom'];
                    $_SESSION['email'] = $data['Email'];
                    $_SESSION['role'] = $data['Role'];

                    if ($data['Role'] === 'admin') {
                        header("location: admin.php");
                    } else {
                        header("location: index.php");
                    }
                    exit();
                } else {
                    echo "<script>alert('Mot de passe incorrect.');</script>";
                }
            } else {
                echo "<script>alert('Adresse e-mail non trouvée.');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Une erreur est survenue. Veuillez réessayer plus tard.');</script>";
        }
    }
}
?>

<main class="form-signin w-50 m-auto">
    <form method="POST" action="login.php">
        <h1 class="h3 mt-5 fw-normal text-center">Veuillez vous connecter</h1>

        <div class="form-floating mb-3">
            <input name="email" type="email" class="form-control" id="floatingInput" placeholder="name@example.com" required>
            <label for="floatingInput">Adresse e-mail</label>
        </div>

        <div class="form-floating mb-3">
            <input name="password" type="password" class="form-control" id="floatingPassword" placeholder="Mot de passe" required>
            <label for="floatingPassword">Mot de passe</label>
        </div>

        <button name="submit" class="w-100 btn btn-lg btn-primary" type="submit">Se connecter</button>
        <h6 class="mt-3">Vous n'avez pas de compte ? <a href="register.php">Créez votre compte</a></h6>
    </form>
</main>

<?php require "includes/footer.php"; ?>
