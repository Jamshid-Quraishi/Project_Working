<?php 
require 'auth.php'; 
require_login(); 
verify_session();

global $pdo;

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM votes WHERE poll_id = p.id AND user_id = ?) as has_voted,
           (SELECT COUNT(*) FROM votes WHERE poll_id = p.id) as total_votes
    FROM polls p 
    WHERE p.is_active = 1 
    ORDER BY p.created_at DESC
");
$stmt->execute([$user_id]);
$polls = $stmt->fetchAll();

$history_stmt = $pdo->prepare("
    SELECT p.title, po.option_text, v.voted_at 
    FROM votes v 
    JOIN polls p ON v.poll_id = p.id 
    JOIN poll_options po ON v.option_id = po.id 
    WHERE v.user_id = ? 
    ORDER BY v.voted_at DESC 
    LIMIT 10
");
$history_stmt->execute([$user_id]);
$vote_history = $history_stmt->fetchAll();

$stats_stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT v.poll_id) as polls_participated,
        COUNT(DISTINCT v.id) as total_votes,
        (SELECT COUNT(*) FROM polls WHERE is_active = 1) as available_polls
    FROM votes v 
    WHERE v.user_id = ?
");
$stats_stmt->execute([$user_id]);
$user_stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Home - Sondaggi</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="home.css">
</head>
<body>
  <div class="home-container">
    <header class="home-header">
      <div class="welcome-section">
        <h1 class="welcome-title">Benvenuto, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
        <div class="user-stats">
          <div class="stat-item">
            <span class="stat-number"><?= $user_stats['polls_participated'] ?? 0 ?></span>
            <span class="stat-label">Sondaggi</span>
          </div>
          <div class="stat-item">
            <span class="stat-number"><?= $user_stats['total_votes'] ?? 0 ?></span>
            <span class="stat-label">Voti</span>
          </div>
          <div class="stat-item">
            <span class="stat-number"><?= $user_stats['available_polls'] ?? 0 ?></span>
            <span class="stat-label">Disponibili</span>
          </div>
        </div>
      </div>
      
      <div class="search-section">
        <div class="search-container">
          <div class="search-input-wrapper">
            <input type="text" 
                   class="search-input" 
                   id="searchInput"
                   placeholder="Cerca sondaggi per titolo o descrizione..."
                   aria-label="Cerca sondaggi">
            <div class="search-icon" id="searchIcon">
              <svg viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="search-results-info" id="searchResultsInfo">
            <span id="resultsCount">0</span> risultati trovati
          </div>
          
          <div class="search-filters">
            <label class="filter-checkbox">
              <input type="checkbox" id="filterTitle" checked>
              <span>Cerca nel titolo</span>
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" id="filterDescription" checked>
              <span>Cerca nella descrizione</span>
            </label>
            <label class="filter-checkbox">
              <input type="checkbox" id="filterAvailable">
              <span>Solo disponibili</span>
            </label>
          </div>
        </div>
      </div>
      
      <nav class="home-nav">
        <a href="home.php" class="nav-link-home active">Sondaggi</a>
        <?php if (is_admin()): ?>
          <a href="admin_dashboard.php" class="nav-link-home">Admin</a>
        <?php endif; ?>
        <a href="logout.php" class="nav-link-home">Logout</a>
      </nav>
    </header>

    <main class="home-main">
      <section class="content-section">
        <div class="section-header">
          <h2 class="section-title">Sondaggi Disponibili</h2>
          <div class="section-actions">
            <span class="section-info" id="pollsCount"><?= count($polls) ?> sondaggi trovati</span>
          </div>
        </div>

        <div id="pollsContainer">
          <?php if (empty($polls)): ?>
            <div class="empty-state">
              <div class="empty-state-icon">📊</div>
              <h3 class="empty-state-title">Nessun sondaggio disponibile</h3>
              <p class="empty-state-description">
                Al momento non ci sono sondaggi attivi. Torna più tardi per scoprire nuove votazioni!
              </p>
            </div>
          <?php else: ?>
            <div class="polls-grid-home" id="pollsGrid">
              <?php foreach ($polls as $poll): ?>
                <div class="poll-card-home" 
                     data-title="<?= htmlspecialchars(strtolower($poll['title'])) ?>"
                     data-description="<?= htmlspecialchars(strtolower($poll['description'])) ?>"
                     data-voted="<?= $poll['has_voted'] > 0 ? 'true' : 'false' ?>">
                  <div class="poll-header-home">
                    <h3 class="poll-title-home"><?= htmlspecialchars($poll['title']) ?></h3>
                    <span class="poll-meta-home"><?= $poll['total_votes'] ?> voti</span>
                  </div>
                  
                  <p class="poll-description-home"><?= htmlspecialchars($poll['description']) ?></p>
                  
                  <div class="poll-footer-home">
                    <div class="poll-status">
                      <?php if ($poll['has_voted'] > 0): ?>
                        <span class="voted-badge-home">Già votato</span>
                      <?php endif; ?>
                    </div>
                    
                    <div class="poll-actions-home">
                      <?php if ($poll['has_voted'] > 0): ?>
                        <a href="vote.php?id=<?= $poll['id'] ?>" class="btn-poll btn-poll-secondary">
                          <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                          </svg>
                          Vedi risultati
                        </a>
                      <?php else: ?>
                        <a href="vote.php?id=<?= $poll['id'] ?>" class="btn-poll btn-poll-primary">
                          <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                          </svg>
                          Vota ora
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        
        <div class="no-results" id="noResults" style="display: none;">
          <div class="no-results-icon">🔍</div>
          <h3 class="no-results-title">Nessun risultato trovato</h3>
          <p class="no-results-description">
            Prova a modificare i termini di ricerca o i filtri per trovare quello che cerchi.
          </p>
        </div>
      </section>

      <section class="content-section">
        <div class="section-header">
          <h2 class="section-title">Le tue ultime votazioni</h2>
          <div class="section-actions">
            <span class="section-info"><?= count($vote_history) ?> attività</span>
          </div>
        </div>

        <?php if (empty($vote_history)): ?>
          <div class="empty-state">
            <div class="empty-state-icon">📝</div>
            <h3 class="empty-state-title">Nessuna attività recente</h3>
            <p class="empty-state-description">
              Non hai ancora partecipato a nessun sondaggio. Scegli un sondaggio dalla lista sopra e inizia a votare!
            </p>
          </div>
        <?php else: ?>
          <div class="history-list">
            <?php foreach ($vote_history as $vote): ?>
              <div class="history-item">
                <div class="history-content">
                  <div class="history-poll-title"><?= htmlspecialchars($vote['title']) ?></div>
                  <div class="history-choice">Hai votato: <?= htmlspecialchars($vote['option_text']) ?></div>
                </div>
                <div class="history-date"><?= date('d/m/Y H:i', strtotime($vote['voted_at'])) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      const searchResultsInfo = document.getElementById('searchResultsInfo');
      const resultsCount = document.getElementById('resultsCount');
      const pollsCount = document.getElementById('pollsCount');
      const pollsGrid = document.getElementById('pollsGrid');
      const noResults = document.getElementById('noResults');
      const filterTitle = document.getElementById('filterTitle');
      const filterDescription = document.getElementById('filterDescription');
      const filterAvailable = document.getElementById('filterAvailable');
      
      let allPolls = [];
      
      if (pollsGrid) {
        const pollCards = pollsGrid.querySelectorAll('.poll-card-home');
        allPolls = Array.from(pollCards).map(card => ({
          element: card,
          title: card.dataset.title,
          description: card.dataset.description,
          voted: card.dataset.voted === 'true'
        }));
      }
      
      function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const searchInTitle = filterTitle.checked;
        const searchInDescription = filterDescription.checked;
        const onlyAvailable = filterAvailable.checked;
        
        let visibleCount = 0;
        
        if (allPolls.length === 0) return;
        
        allPolls.forEach((poll, index) => {
          let shouldShow = true;
          
          if (searchTerm) {
            const matchesTitle = searchInTitle && poll.title.includes(searchTerm);
            const matchesDescription = searchInDescription && poll.description.includes(searchTerm);
            shouldShow = matchesTitle || matchesDescription;
          }
          
          if (onlyAvailable && poll.voted) {
            shouldShow = false;
          }
          
          poll.element.style.display = shouldShow ? 'block' : 'none';
          
          if (shouldShow) {
            visibleCount++;
            
            if (searchTerm) {
              highlightSearchTerms(poll.element, searchTerm);
            } else {
              removeHighlights(poll.element);
            }
          } else {
            removeHighlights(poll.element);
          }
        });
        
        updateResultsInfo(visibleCount, searchTerm);
      }
      
      function highlightSearchTerms(element, searchTerm) {
        const titleElement = element.querySelector('.poll-title-home');
        const descriptionElement = element.querySelector('.poll-description-home');
        
        removeHighlights(element);
        
        if (titleElement && filterTitle.checked) {
          const originalText = titleElement.textContent;
          const highlightedText = originalText.replace(
            new RegExp(searchTerm, 'gi'),
            match => `<span class="search-highlight">${match}</span>`
          );
          titleElement.innerHTML = highlightedText;
        }
        
        if (descriptionElement && filterDescription.checked) {
          const originalText = descriptionElement.textContent;
          const highlightedText = originalText.replace(
            new RegExp(searchTerm, 'gi'),
            match => `<span class="search-highlight">${match}</span>`
          );
          descriptionElement.innerHTML = highlightedText;
        }
      }
      
      function removeHighlights(element) {
        const titleElement = element.querySelector('.poll-title-home');
        const descriptionElement = element.querySelector('.poll-description-home');
        
        if (titleElement) {
          titleElement.innerHTML = titleElement.textContent;
        }
        
        if (descriptionElement) {
          descriptionElement.innerHTML = descriptionElement.textContent;
        }
      }
      
      function updateResultsInfo(visibleCount, searchTerm) {
        resultsCount.textContent = visibleCount;
        pollsCount.textContent = `${visibleCount} sondaggi trovati`;
        
        if (searchTerm) {
          searchResultsInfo.classList.add('visible');
        } else {
          searchResultsInfo.classList.remove('visible');
        }
        
        if (visibleCount === 0 && (searchTerm || filterAvailable.checked)) {
          noResults.style.display = 'block';
          if (pollsGrid) pollsGrid.style.display = 'none';
        } else {
          noResults.style.display = 'none';
          if (pollsGrid) pollsGrid.style.display = 'grid';
        }
      }
      
      searchInput.addEventListener('input', performSearch);
      filterTitle.addEventListener('change', performSearch);
      filterDescription.addEventListener('change', performSearch);
      filterAvailable.addEventListener('change', performSearch);
      
      document.getElementById('searchIcon').addEventListener('click', function() {
        searchInput.focus();
      });
      
      searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          searchInput.value = '';
          performSearch();
        }
      });
      
      const pollCards = document.querySelectorAll('.poll-card-home');
      pollCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
          this.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
          this.style.transform = 'translateY(0)';
        });
      });
    });

    const style = document.createElement('style');
    style.textContent = `
      .btn-loading {
        position: relative;
        color: transparent;
      }
      .btn-loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid transparent;
        border-radius: 50%;
        border-right-color: currentColor;
        animation: spin 0.8s linear infinite;
      }
      @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
      }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html>