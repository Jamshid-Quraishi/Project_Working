<?php 
require 'auth.php'; 
require_admin();
verify_session();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    global $pdo;
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $options = array_filter($_POST['options']);

    if (empty($title) || empty($desc) || count($options) < 2) {
        $error = "Inserisci titolo, descrizione e almeno 2 opzioni.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO polls (title, description, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$title, $desc, $_SESSION['user_id']]);
        $poll_id = $pdo->lastInsertId();

        foreach ($options as $opt) {
            $opt = trim($opt);
            if (!empty($opt)) {
                $stmt = $pdo->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)");
                $stmt->execute([$poll_id, $opt]);
            }
        }
        
        log_audit($_SESSION['user_id'], 'POLL_CREATED', "Created poll: " . $title);
        header("Location: admin_dashboard.php?success=poll_created");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Crea Nuovo Sondaggio</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="create_poll.css" />
</head>
<body>
  <div class="create-poll-container">
    <div class="create-poll-box">
      <div class="create-poll-header">
        <h1 class="create-poll-title">Crea Nuovo Sondaggio</h1>
        <p class="create-poll-subtitle">Compila i campi per creare un nuovo sondaggio</p>
      </div>

      <?php if (isset($error)): ?>
        <div class="error-message-enhanced">
          <svg class="message-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div><?= htmlspecialchars($error) ?></div>
        </div>
      <?php endif; ?>

      <form method="POST" action="create_poll.php" id="createPollForm">
        <div class="form-group-enhanced">
          <label for="title" class="form-label">Titolo del Sondaggio</label>
          <div class="form-input-wrapper">
            <input type="text" 
                   id="title"
                   name="title" 
                   class="form-input-enhanced" 
                   placeholder="Inserisci un titolo accattivante"
                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                   required
                   maxlength="255">
            <div class="input-icon">
              <svg viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>

        <div class="form-group-enhanced">
          <label for="description" class="form-label">Descrizione</label>
          <div class="form-input-wrapper">
            <textarea id="description"
                      name="description" 
                      class="form-input-enhanced" 
                      placeholder="Descrivi il sondaggio in dettaglio..."
                      rows="4"
                      required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            <div class="input-icon">
              <svg viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H6z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>

        <div class="form-group-enhanced">
          <label class="form-label">Opzioni di Risposta</label>
          <div class="options-container" id="optionsContainer">
            <div class="option-input-wrapper">
              <div class="form-input-wrapper">
                <input type="text" 
                       name="options[]" 
                       class="form-input-enhanced" 
                       placeholder="Opzione 1"
                       required>
                <div class="input-icon">
                  <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
            <div class="option-input-wrapper">
              <div class="form-input-wrapper">
                <input type="text" 
                       name="options[]" 
                       class="form-input-enhanced" 
                       placeholder="Opzione 2"
                       required>
                <div class="input-icon">
                  <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
          </div>
          
          <button type="button" class="add-option-btn" id="addOptionBtn">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
            </svg>
            Aggiungi Opzione
          </button>
        </div>

        <div class="form-actions">
          <button type="submit" class="create-poll-btn" id="submitBtn">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            Crea Sondaggio
          </button>
          
          <a href="admin_dashboard.php" class="cancel-btn">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            Annulla
          </a>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const optionsContainer = document.getElementById('optionsContainer');
      const addOptionBtn = document.getElementById('addOptionBtn');
      const createPollForm = document.getElementById('createPollForm');
      const submitBtn = document.getElementById('submitBtn');
      
      let optionCount = 2;
      
      addOptionBtn.addEventListener('click', function() {
        optionCount++;
        const optionWrapper = document.createElement('div');
        optionWrapper.className = 'option-input-wrapper';
        optionWrapper.innerHTML = `
          <div class="form-input-wrapper">
            <input type="text" 
                   name="options[]" 
                   class="form-input-enhanced" 
                   placeholder="Opzione ${optionCount}"
                   required>
            <div class="input-icon option-remove" style="cursor: pointer; color: #ef4444;">
              <svg viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        `;
        
        optionsContainer.appendChild(optionWrapper);
        
        const removeIcon = optionWrapper.querySelector('.option-remove');
        removeIcon.addEventListener('click', function() {
          if (optionsContainer.children.length > 2) {
            optionWrapper.remove();
            updateOptionPlaceholders();
          }
        });
        
        updateOptionPlaceholders();
      });
      
      function updateOptionPlaceholders() {
        const inputs = optionsContainer.querySelectorAll('input[name="options[]"]');
        inputs.forEach((input, index) => {
          input.placeholder = `Opzione ${index + 1}`;
        });
      }
      
      createPollForm.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const optionInputs = document.querySelectorAll('input[name="options[]"]');
        const validOptions = Array.from(optionInputs).filter(input => input.value.trim().length > 0);
        
        if (title.length === 0) {
          e.preventDefault();
          alert('Il titolo del sondaggio è obbligatorio.');
          return;
        }
        
        if (description.length === 0) {
          e.preventDefault();
          alert('La descrizione del sondaggio è obbligatoria.');
          return;
        }
        
        if (validOptions.length < 2) {
          e.preventDefault();
          alert('Sono necessarie almeno 2 opzioni valide.');
          return;
        }
        
        submitBtn.disabled = true;
        submitBtn.classList.add('btn-loading');
        submitBtn.innerHTML = 'Creazione in corso...';
      });
    });
  </script>
</body>
</html>