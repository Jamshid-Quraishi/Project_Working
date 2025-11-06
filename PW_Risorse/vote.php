<?php 
require 'auth.php'; 
require_login(); 
verify_session();

$poll_id = $_GET['id'] ?? 0;
global $pdo;

$stmt = $pdo->prepare("SELECT * FROM polls WHERE id = ? AND is_active = 1");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch();

if (!$poll) {
    header("Location: home.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM poll_options WHERE poll_id = ?");
$stmt->execute([$poll_id]);
$options = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT 1 FROM votes WHERE poll_id = ? AND user_id = ?");
$stmt->execute([$poll_id, $_SESSION['user_id']]);
$has_voted = $stmt->fetch() !== false;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE poll_id = ?");
$stmt->execute([$poll_id]);
$total_votes = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$has_voted) {
    $option_id = $_POST['option_id'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT 1 FROM poll_options WHERE id = ? AND poll_id = ?");
    $stmt->execute([$option_id, $poll_id]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO votes (poll_id, option_id, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$poll_id, $option_id, $_SESSION['user_id']]);
        $has_voted = true;
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE poll_id = ?");
        $stmt->execute([$poll_id]);
        $total_votes = $stmt->fetchColumn();
        
        log_audit($_SESSION['user_id'], 'VOTE_CAST', "Voted in poll: " . $poll['title']);
    }
}

if ($has_voted) {
    $results_stmt = $pdo->prepare("
        SELECT po.id, po.option_text, COUNT(v.id) as vote_count
        FROM poll_options po 
        LEFT JOIN votes v ON po.id = v.option_id 
        WHERE po.poll_id = ? 
        GROUP BY po.id 
        ORDER BY vote_count DESC
    ");
    $results_stmt->execute([$poll_id]);
    $results = $results_stmt->fetchAll();
    
    $max_votes = 0;
    if (!empty($results)) {
        $max_votes = max(array_column($results, 'vote_count'));
    }
    
    $winners = array_filter($results, function($result) use ($max_votes) {
        return $result['vote_count'] == $max_votes && $max_votes > 0;
    });
    $is_tie = count($winners) > 1;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Vota - <?= htmlspecialchars($poll['title']) ?></title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="vote.css">
</head>
<body>
  <div class="vote-container">
    <header class="vote-header">
      <div class="poll-hero">
        <h1 class="poll-title"><?= htmlspecialchars($poll['title']) ?></h1>
        <p class="poll-description"><?= nl2br(htmlspecialchars($poll['description'])) ?></p>
        
        <div class="poll-meta">
          <div class="meta-item">
            <span class="meta-value"><?= count($options) ?></span>
            <span class="meta-label">Opzioni</span>
          </div>
          <div class="meta-item">
            <span class="meta-value"><?= $total_votes ?></span>
            <span class="meta-label">Voti Totali</span>
          </div>
          <div class="meta-item">
            <span class="meta-value">
              <?php if ($has_voted): ?>
                <svg class="status-icon" viewBox="0 0 24 24" fill="currentColor">
                  <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
                </svg>
              <?php else: ?>
                <svg class="status-icon" viewBox="0 0 24 24" fill="currentColor">
                  <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 000-1.5h-3.75V6z" clip-rule="evenodd"/>
                </svg>
              <?php endif; ?>
            </span>
            <span class="meta-label">
              <?= $has_voted ? 'Già Votato' : 'Da Votare' ?>
            </span>
          </div>
        </div>
        
        <nav class="vote-nav">
          <a href="home.php" class="nav-link-vote">
            <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
            </svg>
            Home
          </a>
          <?php if (is_admin()): ?>
            <a href="admin_dashboard.php" class="nav-link-vote">
              <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
              </svg>
              Admin
            </a>
          <?php endif; ?>
        </nav>
      </div>
    </header>

    <main class="vote-main">
      <?php if ($has_voted): ?>
        <section class="results-section">
          <div class="already-voted">
            <div class="voted-message">
              <svg class="voted-icon" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
              </svg>
              <h2 class="voted-title">Hai già votato!</h2>
              <p class="voted-description">Grazie per aver partecipato al sondaggio. Ecco i risultati aggiornati:</p>
            </div>
          </div>

          <div class="results-header">
            <h2 class="results-title">Risultati del Sondaggio</h2>
            <div class="total-votes">Totale voti: <?= $total_votes ?></div>
          </div>

          <div class="results-grid">
            <?php foreach ($results as $result): 
                $percentage = $total_votes > 0 ? ($result['vote_count'] / $total_votes) * 100 : 0;
                $is_winner = $result['vote_count'] == $max_votes && $max_votes > 0;
                $is_tie_winner = $is_winner && $is_tie;
            ?>
              <div class="result-item <?= $is_winner ? ($is_tie_winner ? 'tie' : 'winner') : '' ?>">
                <div class="result-header">
                  <div class="option-text-result">
                    <?= htmlspecialchars($result['option_text']) ?>
                    <?php if ($is_winner): ?>
                      <span class="badge <?= $is_tie_winner ? 'tie-badge' : 'winner-badge' ?>">
                        <?= $is_tie_winner ? 'Pari' : 'Vincitore' ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <div class="vote-count">
                    <?= $result['vote_count'] ?> voti (<?= round($percentage, 1) ?>%)
                  </div>
                </div>
                <div class="progress-bar">
                  <div class="progress-fill" style="width: <?= $percentage ?>%">
                    <span class="progress-text"><?= round($percentage, 1) ?>%</span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

      <?php else: ?>
        <section class="voting-section">
          <h2 class="section-title">Seleziona la tua opzione preferita</h2>
          
          <form method="POST" action="vote.php?id=<?= $poll_id ?>" id="voteForm">
            <div class="vote-options">
              <?php foreach ($options as $option): ?>
                <label class="radio-option" for="option_<?= $option['id'] ?>">
                  <input type="radio" 
                         name="option_id" 
                         value="<?= $option['id'] ?>" 
                         id="option_<?= $option['id'] ?>" 
                         class="option-input" 
                         required>
                  <div class="option-content">
                    <div class="option-checkbox"></div>
                    <div class="option-text"><?= htmlspecialchars($option['option_text']) ?></div>
                  </div>
                </label>
              <?php endforeach; ?>
            </div>
            
            <div class="vote-action">
              <button type="submit" class="vote-btn" id="voteBtn" disabled>
                Conferma il tuo voto
              </button>
            </div>
          </form>
        </section>
      <?php endif; ?>
    </main>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const voteForm = document.getElementById('voteForm');
      const voteBtn = document.getElementById('voteBtn');
      const radioOptions = document.querySelectorAll('.radio-option');
      
      if (voteForm) {
        radioOptions.forEach(option => {
          option.addEventListener('click', function() {
            radioOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            voteBtn.disabled = false;
          });
        });
        
        voteForm.addEventListener('submit', function(e) {
          const selectedOption = document.querySelector('input[name="option_id"]:checked');
          
          if (!selectedOption) {
            e.preventDefault();
            alert('Per favore seleziona un\'opzione prima di votare.');
            return;
          }
          
          voteBtn.disabled = true;
          voteBtn.classList.add('btn-loading');
          voteBtn.textContent = 'Registrando il voto...';
          
          setTimeout(() => {
            voteBtn.textContent = 'Voto registrato!';
          }, 1000);
        });
        
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' && voteForm.contains(document.activeElement)) {
            const selectedOption = document.querySelector('input[name="option_id"]:checked');
            if (selectedOption && !voteBtn.disabled) {
              voteForm.requestSubmit();
            }
          }
        });
      }
      
      const resultItems = document.querySelectorAll('.result-item');
      resultItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
          this.style.transform = 'translateY(-4px)';
        });
        
        item.addEventListener('mouseleave', function() {
          this.style.transform = 'translateY(0)';
        });
      });
      
      const progressBars = document.querySelectorAll('.progress-fill');
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const progressBar = entry.target;
            const width = progressBar.style.width;
            progressBar.style.width = '0%';
            
            setTimeout(() => {
              progressBar.style.width = width;
            }, 300);
          }
        });
      }, { threshold: 0.5 });
      
      progressBars.forEach(bar => observer.observe(bar));
      
      const winnerItems = document.querySelectorAll('.result-item.winner, .result-item.tie');
      winnerItems.forEach(item => {
        item.addEventListener('click', function() {
          this.classList.add('vote-success');
          setTimeout(() => {
            this.classList.remove('vote-success');
          }, 600);
        });
      });
    });

    function celebrateVote() {
      if (typeof confetti === 'function') {
        confetti({
          particleCount: 100,
          spread: 70,
          origin: { y: 0.6 }
        });
      }
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
  
  <style>
    .result-item.tie {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border-color: #f59e0b;
    }

    .tie-badge {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-left: 10px;
    }

    .winner-badge {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-left: 10px;
    }

    .radio-option {
        cursor: pointer;
    }
    
    .radio-option:active {
        transform: scale(0.98);
    }
    
    .vote-btn:active {
        transform: translateY(-1px);
    }
    
    .option-input:focus + .option-content {
        outline: 2px solid #4f46e5;
        outline-offset: 2px;
        border-radius: 16px;
    }
    
    .nav-link-vote:focus {
        outline: 2px solid #4f46e5;
        outline-offset: 2px;
    }
  </style>
</body>
</html>
