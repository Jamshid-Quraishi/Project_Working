<?php 
require 'auth.php'; 
if (is_logged_in()) {
    header("Location: " . (is_admin() ? "admin_dashboard.php" : "home.php"));
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Sondaggi</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="container">
    <div class="auth-box">
      <h1>Login Sondaggi</h1>
      <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
          <?php 
          $errors = [
            '1' => 'Username o password errati.',
            'disabled' => 'Utente disabilitato. Contatta l\'amministratore.',
            'session_expired' => 'Sessione scaduta. Accedi nuovamente.'
          ];
          echo $errors[$_GET['error']] ?? 'Errore durante il login.';
          ?>
        </div>
      <?php endif; ?>
      <form method="POST" action="login_process.php">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="input-group">
          <input type="text" name="username" placeholder="Username" required maxlength="50" />
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Password" required minlength="8" />
        </div>
        <button type="submit" class="btn-primary">Accedi</button>
      </form>
      <div class="auth-links">
        <a href="register.php" class="btn-secondary">Registrati</a>
      </div>
    </div>
  </div>
</body>
</html>
