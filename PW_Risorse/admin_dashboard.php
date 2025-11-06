<?php 
require 'auth.php'; 
require_admin(); 
verify_session();

global $pdo;

$stats_stmt = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE is_admin = 0) as total_users,
        (SELECT COUNT(*) FROM polls) as total_polls,
        (SELECT COUNT(*) FROM votes) as total_votes
");
$stats = $stats_stmt->fetch();

$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM votes WHERE poll_id = p.id) as vote_count,
           (SELECT username FROM users WHERE id = p.created_by) as creator
    FROM polls p 
    ORDER BY p.created_at DESC
");
$stmt->execute();
$polls = $stmt->fetchAll();

$recent_votes_stmt = $pdo->query("
    SELECT p.title, COUNT(v.id) as votes_count
    FROM polls p 
    LEFT JOIN votes v ON p.id = v.poll_id 
    WHERE v.voted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY p.id 
    ORDER BY votes_count DESC 
    LIMIT 5
");
$recent_votes = $recent_votes_stmt->fetchAll();

if (isset($_GET['delete_poll'])) {
    $poll_id = (int)$_GET['delete_poll'];
    
    $check_stmt = $pdo->prepare("SELECT id FROM polls WHERE id = ?");
    $check_stmt->execute([$poll_id]);
    $poll_exists = $check_stmt->fetch();
    
    if ($poll_exists) {
        $delete_votes_stmt = $pdo->prepare("DELETE FROM votes WHERE poll_id = ?");
        $delete_votes_stmt->execute([$poll_id]);
        
        $delete_options_stmt = $pdo->prepare("DELETE FROM poll_options WHERE poll_id = ?");
        $delete_options_stmt->execute([$poll_id]);
        
        $delete_poll_stmt = $pdo->prepare("DELETE FROM polls WHERE id = ?");
        $delete_poll_stmt->execute([$poll_id]);
        
        log_audit($_SESSION['user_id'], 'POLL_DELETED', "Deleted poll ID: " . $poll_id);
        
        header("Location: admin_dashboard.php?success=poll_deleted");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="admin_dashboard.css" />
</head>
<body>
  <div class="admin-container">
    <header class="admin-header">
      <h1>Admin Dashboard</h1>
      <nav class="admin-nav">
        <a href="create_poll.php" class="nav-link-admin">
          <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
          </svg>
          Crea Sondaggio
        </a>
        <a href="user_management.php" class="nav-link-admin">Gestione Utenti</a>
        <a href="audit_log.php" class="nav-link-admin">Log di Sistema</a>
        <a href="logout.php" class="nav-link-admin logout-btn">
          <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
          </svg>
          Logout
        </a>
      </nav>
    </header>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'poll_deleted'): ?>
      <div class="success-message-enhanced">
        <svg class="message-icon" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <div>Sondaggio eliminato con successo!</div>
      </div>
    <?php endif; ?>

    <section class="stats-grid-admin">
      <div class="stat-card-admin">
        <h3>Utenti Registrati</h3>
        <div class="stat-number-admin"><?= $stats['total_users'] ?></div>
      </div>
      <div class="stat-card-admin">
        <h3>Sondaggi Totali</h3>
        <div class="stat-number-admin"><?= $stats['total_polls'] ?></div>
      </div>
      <div class="stat-card-admin">
        <h3>Voti Totali</h3>
        <div class="stat-number-admin"><?= $stats['total_votes'] ?></div>
      </div>
    </section>

    <section class="admin-section">
      <h2 class="section-title-admin">Sondaggi Recenti</h2>
      <div class="polls-grid-admin">
        <?php foreach ($polls as $poll): ?>
          <div class="poll-card-admin">
            <div class="poll-header-admin">
              <h3 class="poll-title-admin"><?= htmlspecialchars($poll['title']) ?></h3>
              <span class="poll-meta-admin"><?= $poll['vote_count'] ?> voti</span>
            </div>
            <p class="poll-description-admin"><?= htmlspecialchars($poll['description']) ?></p>
            <div class="poll-meta-info-admin">
              <span>Creato da: <?= htmlspecialchars($poll['creator']) ?></span>
              <span>Il: <?= date('d/m/Y H:i', strtotime($poll['created_at'])) ?></span>
            </div>
            <div class="poll-actions-admin">
              <a href="admin_results.php?id=<?= $poll['id'] ?>" class="btn-admin btn-admin-primary">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                  <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                </svg>
                Risultati
              </a>
              <a href="admin_dashboard.php?delete_poll=<?= $poll['id'] ?>" 
                 class="btn-admin btn-admin-danger delete-poll-btn"
                 data-poll-title="<?= htmlspecialchars($poll['title']) ?>">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                Elimina
              </a>
              <?php if ($poll['is_active']): ?>
                <span class="status-badge-admin active">Attivo</span>
              <?php else: ?>
                <span class="status-badge-admin inactive">Inattivo</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="admin-section">
      <h2 class="section-title-admin">Voti Recenti (Ultimi 7 giorni)</h2>
      <div class="recent-votes-admin">
        <?php if (empty($recent_votes)): ?>
          <p class="no-data-admin">Nessun voto negli ultimi 7 giorni.</p>
        <?php else: ?>
          <?php foreach ($recent_votes as $vote): ?>
            <div class="vote-item-admin">
              <span class="vote-title-admin"><?= htmlspecialchars($vote['title']) ?></span>
              <span class="vote-count-admin"><?= $vote['votes_count'] ?> voti</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const deleteButtons = document.querySelectorAll('.delete-poll-btn');
      
      deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          const pollTitle = this.getAttribute('data-poll-title');
          const confirmDelete = confirm(`Sei sicuro di voler eliminare il sondaggio "${pollTitle}"?\n\nQuesta azione cancellerà anche tutti i voti associati e non può essere annullata.`);
          
          if (!confirmDelete) {
            e.preventDefault();
          }
        });
      });

      const pollCards = document.querySelectorAll('.poll-card-admin');
      pollCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
          this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
          this.style.transform = 'translateY(0)';
        });
      });
    });
  </script>
</body>
</html>