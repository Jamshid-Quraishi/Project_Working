<?php
require 'auth.php';
require_admin();
verify_session();

global $pdo;

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("
    SELECT al.*, u.username 
    FROM audit_log al 
    LEFT JOIN users u ON al.user_id = u.id 
    ORDER BY al.timestamp DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

$count_stmt = $pdo->query("SELECT COUNT(*) FROM audit_log");
$total_logs = $count_stmt->fetchColumn();
$total_pages = ceil($total_logs / $limit);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Log di Sistema</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Log di Sistema</h1>
            <nav class="nav">
                <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
                <a href="user_management.php" class="nav-link">Gestione Utenti</a>
                <a href="audit_log.php" class="nav-link active">Log di Sistema</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </nav>
        </header>

        <section class="section">
            <div class="logs-header">
                <h2 class="section-title">Tracciamento Attività</h2>
                <div class="logs-info">
                    <span>Totali: <?= $total_logs ?> log</span>
                </div>
            </div>

            <div class="logs-container">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>Data/Ora</th>
                            <th>Utente</th>
                            <th>Azione</th>
                            <th>Dettagli</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="timestamp"><?= date('d/m/Y H:i:s', strtotime($log['timestamp'])) ?></td>
                                <td class="username"><?= $log['username'] ? htmlspecialchars($log['username']) : 'Sistema' ?></td>
                                <td class="action">
                                    <span class="action-badge action-<?= $log['action_type'] ?>">
                                        <?= htmlspecialchars($log['action_type']) ?>
                                    </span>
                                </td>
                                <td class="details"><?= htmlspecialchars($log['details']) ?></td>
                                <td class="ip"><?= htmlspecialchars($log['ip_address']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="audit_log.php?page=<?= $i ?>" class="page-link <?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>