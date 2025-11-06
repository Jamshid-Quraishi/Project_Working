<?php
require 'auth.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termini di Servizio - Sondaggi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>Termini di Servizio</h1>
            </div>
            <nav class="nav">
                <?php if (is_logged_in()): ?>
                    <a href="<?= is_admin() ? 'admin_dashboard.php' : 'home.php' ?>" class="nav-link">Home</a>
                <?php else: ?>
                    <a href="index.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Registrati</a>
                <?php endif; ?>
                <a href="privacy.php" class="nav-link">Privacy Policy</a>
            </nav>
        </header>

        <section class="section">
            <div class="terms-content">
                <h2 class="section-title">Condizioni Generali di Utilizzo</h2>
                
                <div class="terms-section">
                    <h3>1. Accettazione dei Termini</h3>
                    <p>Accedendo e utilizzando questa piattaforma, l'utente accetta di essere vincolato dai presenti Termini di Servizio e da tutte le leggi e regolamenti applicabili.</p>
                </div>

                <div class="terms-section">
                    <h3>2. Account Utente</h3>
                    <p>Per utilizzare le funzionalità della piattaforma è necessario registrarsi creando un account. L'utente si impegna a:</p>
                    <ul>
                        <li>Fornire informazioni accurate e complete durante la registrazione</li>
                        <li>Aggiornare tempestivamente le informazioni in caso di modifiche</li>
                        <li>Mantenere la riservatezza delle credenziali di accesso</li>
                        <li>Essere responsabile di tutte le attività che avvengono tramite il proprio account</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h3>3. Condotta dell'Utente</h3>
                    <p>L'utente si impegna a non:</p>
                    <ul>
                        <li>Utilizzare la piattaforma per scopi illegali o non autorizzati</li>
                        <li>Violare diritti di proprietà intellettuale</li>
                        <li>Trasmettere virus o codice dannoso</li>
                        <li>Tentare di accedere ad aree riservate senza autorizzazione</li>
                        <li>Manomettere o alterare il funzionamento della piattaforma</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h3>4. Sondaggi e Votazioni</h3>
                    <p>Le regole per la partecipazione ai sondaggi:</p>
                    <ul>
                        <li>Ogni utente può votare una sola volta per sondaggio</li>
                        <li>I voti sono anonimi e non modificabili dopo l'invio</li>
                        <li>I risultati sono presentati in forma aggregata</li>
                        <li>È vietato manipolare o alterare i risultati</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h3>5. Proprietà Intellettuale</h3>
                    <p>Tutti i contenuti della piattaforma, inclusi testi, grafica, loghi e software, sono di proprietà esclusiva della piattaforma e protetti dalle leggi sul copyright.</p>
                </div>

                <div class="terms-section">
                    <h3>6. Limitazione di Responsabilità</h3>
                    <p>La piattaforma non è responsabile per:</p>
                    <ul>
                        <li>Interruzioni temporanee del servizio</li>
                        <li>Perdita di dati non imputabile a negligenza</li>
                        <li>Utilizzo improprio della piattaforma da parte degli utenti</li>
                        <li>Contenuti di sondaggi creati dagli amministratori</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h3>7. Modifiche ai Termini</h3>
                    <p>Ci riserviamo il diritto di modificare questi termini in qualsiasi momento. Le modifiche saranno efficaci immediatamente dopo la pubblicazione sulla piattaforma.</p>
                </div>

                <div class="terms-section">
                    <h3>8. Sospensione e Terminazione</h3>
                    <p>Ci riserviamo il diritto di sospendere o terminare l'account di qualsiasi utente che violi questi termini di servizio.</p>
                </div>

                <div class="terms-section">
                    <h3>9. Legge Applicabile</h3>
                    <p>Questi termini sono regolati e interpretati in accordo con le leggi italiane.</p>
                </div>

                <div class="terms-section">
                    <h3>10. Contatti</h3>
                    <p>Per questioni relative a questi termini di servizio, contattare: terms@sondaggi.it</p>
                </div>
        </section>

        <section class="section">
            <div class="quick-actions">
                <a href="register.php" class="quick-action">Torna alla Registrazione</a>
                <a href="privacy.php" class="quick-action">Privacy Policy</a>
                <?php if (!is_logged_in()): ?>
                    <a href="index.php" class="quick-action">Login</a>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <style>
        .terms-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .terms-section {
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #f8fafc, #e5e7eb);
            border-radius: 12px;
            border-left: 4px solid #f59e0b;
        }

        .terms-section h3 {
            color: #f59e0b;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .terms-section p {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .terms-section ul {
            color: #6b7280;
            padding-left: 20px;
        }

        .terms-section li {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .terms-footer {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-radius: 12px;
            border: 1px solid #fcd34d;
        }

        .quick-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .quick-action {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .quick-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
        }
    </style>
</body>
</html>
