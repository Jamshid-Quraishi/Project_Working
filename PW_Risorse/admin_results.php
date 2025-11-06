<?php 
require 'auth.php'; 
require_admin(); 
verify_session();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID sondaggio non valido.");
}

$poll_id = (int)$_GET['id'];
global $pdo;

$stmt = $pdo->prepare("SELECT * FROM polls WHERE id = ?");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch();

if (!$poll) {
    die("Sondaggio non trovato.");
}

$stmt = $pdo->prepare("SELECT * FROM poll_options WHERE poll_id = ?");
$stmt->execute([$poll_id]);
$options = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE poll_id = ?");
$stmt->execute([$poll_id]);
$total = $stmt->fetchColumn();

$anonymous_votes_stmt = $pdo->prepare("
    SELECT po.option_text, COUNT(v.id) as vote_count
    FROM votes v 
    JOIN poll_options po ON v.option_id = po.id 
    WHERE v.poll_id = ? 
    GROUP BY po.id 
    ORDER BY vote_count DESC
");
$anonymous_votes_stmt->execute([$poll_id]);
$anonymous_results = $anonymous_votes_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Risultati: <?= htmlspecialchars($poll['title']) ?></title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="container">
    <header class="header">
      <h1>Risultati: <?= htmlspecialchars($poll['title']) ?></h1>
      <nav class="nav">
        <a href="admin_dashboard.php" class="nav-link">← Dashboard</a>
      </nav>
    </header>

    <section class="section">
      <div class="results-header">
        <h2 class="section-title">Risultati Anonimizzati</h2>
        <div class="total-votes">Totale voti: <?= $total ?></div>
      </div>
      
      <div class="results-grid">
        <?php foreach ($anonymous_results as $result): ?>
          <?php
          $percentage = $total > 0 ? ($result['vote_count'] / $total) * 100 : 0;
          $percentage_display = round($percentage, 1);
          ?>
          <div class="result-item">
            <div class="result-header">
              <span class="option-text"><?= htmlspecialchars($result['option_text']) ?></span>
              <span class="vote-count"><?= $result['vote_count'] ?> voti (<?= $percentage_display ?>%)</span>
            </div>
            <div class="progress-bar">
              <div class="progress-fill" style="width: <?= $percentage ?>%"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section">
      <h2 class="section-title">Dettagli Opzioni</h2>
      <div class="options-list">
        <?php foreach ($options as $option): ?>
          <div class="option-item">
            <span><?= htmlspecialchars($option['option_text']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</body>
</html>