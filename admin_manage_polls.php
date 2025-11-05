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
    header("Location: user_management.php?success=toggle");
    exit;
}

if ($action === 'delete' && $user_id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != ? AND is_admin = 0");
    $stmt->execute([$user_id, $_SESSION['user_id']]);
    header("Location: user_management.php?success=delete");
    exit;
}

$stmt = $pdo->query("
    SELECT id, username, is_admin, is_active, created_at 
    FROM users 
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll();

$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_users,
        SUM(is_admin) as admin_count,
        SUM(is_active) as active_users
    FROM users
");
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Utenti - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Gestione Utenti</h1>
            <nav class="nav">
                <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
                <a href="admin_manage_polls.php" class="nav-link">Sondaggi</a>
                <a href="user_management.php" class="nav-link active">Utenti</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </nav>
        </header>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <?= $_GET['success'] === 'toggle' ? 'Stato utente aggiornato!' : 'Utente eliminato!' ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Utenti Totali</h3>
                <div class="stat-number"><?= $stats['total_users'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Amministratori</h3>
                <div class="stat-number"><?= $stats['admin_count'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Utenti Attivi</h3>
                <div class="stat-number"><?= $stats['active_users'] ?></div>
            </div>
        </div>

        <section class="section">
            <h2 class="section-title">Elenco Utenti</h2>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Ruolo</th>
                            <th>Stato</th>
                            <th>Data Registrazione</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td>
                                    <span class="role-badge <?= $user['is_admin'] ? 'admin' : 'user' ?>">
                                        <?= $user['is_admin'] ? 'Admin' : 'Utente' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?= $user['is_active'] ? 'active' : 'inactive' ?>">
                                        <?= $user['is_active'] ? 'Attivo' : 'Inattivo' ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="user_management.php?action=toggle&id=<?= $user['id'] ?>" 
                                               class="btn-small <?= $user['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                                                <?= $user['is_active'] ? 'Disabilita' : 'Attiva' ?>
                                            </a>
                                            <?php if (!$user['is_admin']): ?>
                                                <a href="user_management.php?action=delete&id=<?= $user['id'] ?>" 
                                                   class="btn-small btn-danger"
                                                   onclick="return confirm('Sei sicuro di voler eliminare questo utente?')">
                                                    Elimina
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: var(--gray-500); font-size: 0.8rem;">Tu</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>