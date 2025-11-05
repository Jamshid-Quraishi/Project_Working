<?php
require 'auth.php';
require_admin();
verify_session();

global $pdo;

$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? 0;

if ($action === 'toggle' && $user_id) {
    $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND id != ?");
    $stmt->execute([$user_id, $_SESSION['user_id']]);
    log_audit($_SESSION['user_id'], 'USER_TOGGLE', "Modificato stato utente ID: $user_id");
}

if ($action === 'delete' && $user_id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != ? AND is_admin = 0");
    $stmt->execute([$user_id, $_SESSION['user_id']]);
    log_audit($_SESSION['user_id'], 'USER_DELETE', "Eliminato utente ID: $user_id");
}

$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_users,
        SUM(is_admin) as admin_count,
        SUM(is_active) as active_users,
        COUNT(*) - SUM(is_active) as inactive_users
    FROM users
");
$stats = $stats_stmt->fetch();

$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.username,
        u.is_admin,
        u.is_active,
        u.created_at,
        COUNT(DISTINCT v.id) as vote_count,
        COUNT(DISTINCT p.id) as poll_count,
        MAX(v.voted_at) as last_vote
    FROM users u
    LEFT JOIN votes v ON u.id = v.user_id
    LEFT JOIN polls p ON u.id = p.created_by
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute();
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Utenti - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .user-management-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .user-management-table th {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-management-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        
        .user-management-table tr:last-child td {
            border-bottom: none;
        }
        
        .user-management-table tr:hover {
            background: #f8fafc;
        }
        
        .user-info-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
        }
        
        .username {
            font-weight: 600;
            color: #1f2937;
            font-size: 0.95rem;
        }
        
        .user-id {
            color: #6b7280;
            font-size: 0.8rem;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-admin {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .badge-user {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
        }
        
        .badge-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .badge-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .activity-stats {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .stat {
            color: #6b7280;
            font-size: 0.85rem;
        }
        
        .last-activity {
            color: #9ca3af;
            font-size: 0.8rem;
            font-style: italic;
        }
        
        .registration-date {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-action {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .current-user-label {
            color: #6b7280;
            font-style: italic;
            font-size: 0.9rem;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>Gestione Utenti</h1>
            </div>
            <nav class="nav">
                <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
                <a href="user_management.php" class="nav-link active">Gestione Utenti</a>
                <a href="create_poll.php" class="nav-link">Crea Sondaggio</a>
                <a href="audit_log.php" class="nav-link">Log Sistema</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </nav>
        </header>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Utenti Totali</h3>
                    <div class="stat-number"><?= $stats['total_users'] ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Amministratori</h3>
                    <div class="stat-number"><?= $stats['admin_count'] ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Utenti Attivi</h3>
                    <div class="stat-number"><?= $stats['active_users'] ?></div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Elenco Utenti Registrati</h2>
                <div class="section-actions">
                    <span class="total-count"><?= count($users) ?> utenti trovati</span>
                </div>
            </div>

            <div class="table-container">
                <table class="user-management-table">
                    <thead>
                        <tr>
                            <th>Utente</th>
                            <th>Ruolo</th>
                            <th>Stato</th>
                            <th>Attività</th>
                            <th>Registrazione</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info-cell">
                                        <div class="user-avatar">
                                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                        </div>
                                        <div class="user-details">
                                            <div class="username"><?= htmlspecialchars($user['username']) ?></div>
                                            <div class="user-id">ID: <?= $user['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $user['is_admin'] ? 'badge-admin' : 'badge-user' ?>">
                                        <?= $user['is_admin'] ? 'Amministratore' : 'Utente' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $user['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $user['is_active'] ? 'Attivo' : 'Inattivo' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="activity-stats">
                                        <div class="stat"><?= $user['vote_count'] ?> voti</div>
                                        <div class="stat"><?= $user['poll_count'] ?> sondaggi</div>
                                        <?php if ($user['last_vote']): ?>
                                            <div class="last-activity">Ultimo: <?= date('d/m/y', strtotime($user['last_vote'])) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="registration-date">
                                    <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                </td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <div class="action-buttons">
                                            <a href="user_management.php?action=toggle&id=<?= $user['id'] ?>" 
                                               class="btn-action <?= $user['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                                                <?= $user['is_active'] ? 'Disabilita' : 'Attiva' ?>
                                            </a>

                                            <?php if (!$user['is_admin']): ?>
                                                <a href="user_management.php?action=delete&id=<?= $user['id'] ?>" 
                                                   class="btn-action btn-danger"
                                                   onclick="return confirm('Sei sicuro di voler eliminare definitivamente questo utente? Questa azione non può essere annullata.')">
                                                    Elimina
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="current-user-label">Tu</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">Conformità GDPR</h2>
            <div class="gdpr-compliance">
                <div class="compliance-item">
                    <div class="compliance-content">
                        <h4>Protezione Dati</h4>
                        <p>Tutti i dati personali sono crittografati e protetti secondo le normative GDPR</p>
                    </div>
                </div>
                <div class="compliance-item">
                    <div class="compliance-content">
                        <h4>Consenso Esplicito</h4>
                        <p>Il consenso GDPR viene richiesto esplicitamente durante la registrazione</p>
                    </div>
                </div>
                <div class="compliance-item">
                    <div class="compliance-content">
                        <h4>Diritto all'Oblio</h4>
                        <p>Gli utenti possono essere eliminati completamente dal sistema</p>
                    </div>
                </div>
                <div class="compliance-item">
                    <div class="compliance-content">
                        <h4>Trasparenza</h4>
                        <p>Tutte le attività sono tracciate nel log di sistema per audit</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-danger');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Questa azione è irreversibile. Procedere con l\'eliminazione?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
