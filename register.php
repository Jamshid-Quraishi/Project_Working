<?php 
require 'auth.php'; 
if (is_logged_in()) { 
    header("Location: home.php"); 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registrazione - Sondaggi</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="register.css">
</head>
<body>
  <div class="register-container">
    <div class="register-box">
      <div class="register-header">
        <h1 class="register-title">Crea Account</h1>
        <p class="register-subtitle">Registrati e inizia a dirci la tua</p>
      </div>

      <?php if (isset($_GET['error'])): ?>
        <div class="error-message-enhanced">
          <svg class="message-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div>Username già in uso. Scegline un altro.</div>
        </div>
      <?php endif; ?>

      <form method="POST" action="register_process.php" id="registerForm">
        <div class="form-group-enhanced">
          <label for="username" class="form-label">Username</label>
          <div class="form-input-wrapper">
            <input type="text" 
                   id="username"
                   name="username" 
                   class="form-input-enhanced" 
                   placeholder="Il tuo username unico"
                   required
                   minlength="3"
                   maxlength="50">
            <div class="input-icon">
              <svg viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>

        <div class="form-group-enhanced">
          <label for="password" class="form-label">Password</label>
          <div class="form-input-wrapper">
            <input type="password" 
                   id="password"
                   name="password" 
                   class="form-input-enhanced" 
                   placeholder="Crea una password sicura"
                   required
                   minlength="8">
            <div class="input-icon">
              <svg viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="password-strength" id="passwordStrength">
            <div class="strength-indicators">
              <div class="strength-indicator" id="strength1"></div>
              <div class="strength-indicator" id="strength2"></div>
              <div class="strength-indicator" id="strength3"></div>
              <div class="strength-indicator" id="strength4"></div>
            </div>
            <div class="strength-text" id="strengthText">Inserisci una password</div>
          </div>
        </div>

        <div class="gdpr-checkbox">
          <input type="checkbox" id="gdpr_consent" name="gdpr_consent" required>
          <label for="gdpr_consent" class="gdpr-label">
            Mashallah al trattamento dei miei dati personali secondo 
            <a href="privacy.php" target="_blank">l'informativa privacy</a> 
            e accetto 
            <a href="terms.php" target="_blank">i termini di servizio</a>.
          </label>
        </div>

        <button type="submit" class="register-btn" id="submitBtn">
          Crea Account
        </button>
      </form>

      <div class="register-footer">
        <p>Hai già un account?</p>
        <a href="index.php" class="login-link">
          <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
          </svg>
          Accedi al tuo account
        </a>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('password').addEventListener('input', function(e) {
      const password = e.target.value;
      const strengthBar = document.getElementById('passwordStrength');
      const indicators = [
        document.getElementById('strength1'),
        document.getElementById('strength2'),
        document.getElementById('strength3'),
        document.getElementById('strength4')
      ];
      const strengthText = document.getElementById('strengthText');
      
      if (password.length === 0) {
        strengthBar.classList.remove('visible');
        indicators.forEach(ind => ind.classList.remove('active', 'weak', 'medium', 'strong'));
        return;
      }
      
      strengthBar.classList.add('visible');
      
      let strength = 0;
      let text = 'Debole';
      let strengthClass = 'weak';
      
    
      if (password.length >= 8) strength++;
      if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
      if (password.match(/\d/)) strength++;
      if (password.match(/[^a-zA-Z\d]/)) strength++;
      
    
      indicators.forEach((indicator, index) => {
        indicator.classList.remove('active', 'weak', 'medium', 'strong');
        if (index < strength) {
          indicator.classList.add('active');
          if (strength <= 2) {
            indicator.classList.add('weak');
          } else if (strength === 3) {
            indicator.classList.add('medium');
          } else {
            indicator.classList.add('strong');
          }
        }
      });
      
      if (strength >= 4) {
        text = 'Forte';
        strengthClass = 'strong';
      } else if (strength >= 2) {
        text = 'Media';
        strengthClass = 'medium';
      }
      
      strengthText.textContent = text;
      strengthText.className = 'strength-text ' + strengthClass;
    });

    document.getElementById('registerForm').addEventListener('submit', function(e) {
      const btn = document.getElementById('submitBtn');
      const password = document.getElementById('password').value;
      const username = document.getElementById('username').value;
      
      if (username.length < 3) {
        e.preventDefault();
        alert('L\'username deve essere di almeno 3 caratteri');
        return;
      }
      
      if (password.length < 8) {
        e.preventDefault();
        alert('La password deve essere di almeno 8 caratteri');
        return;
      }
      
      btn.disabled = true;
      btn.classList.add('btn-loading');
      btn.textContent = 'Creazione account in corso...';
    });

    document.getElementById('username').addEventListener('input', function(e) {
      const username = e.target.value;
      if (username.length > 0 && username.length < 3) {
        this.style.borderColor = '#ef4444';
      } else {
        this.style.borderColor = '';
      }
    });
  </script>
</body>
</html>