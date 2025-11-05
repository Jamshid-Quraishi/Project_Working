<?php
require 'auth.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informativa Privacy - Sondaggi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>Informativa sulla Privacy</h1>
            </div>
            <nav class="nav">
                <?php if (is_logged_in()): ?>
                    <a href="<?= is_admin() ? 'admin_dashboard.php' : 'home.php' ?>" class="nav-link">Home</a>
                <?php else: ?>
                    <a href="index.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Registrati</a>
                <?php endif; ?>
                <a href="terms.php" class="nav-link">Termini di Servizio</a>
            </nav>
        </header>

        <section class="section">
            <div class="privacy-content">
                <h2 class="section-title">Informativa Privacy GDPR</h2>
                
                <div class="privacy-section">
                    <h3>1. Titolare del Trattamento</h3>
                    <p>Il titolare del trattamento dei dati è GANJA S.P.A. con sede Sotto il pontos 15 CAP 777</p>
                </div>

                <div class="privacy-section">
                    <h3>2. Dati Raccolti</h3>
                    <p>Raccogliamo e trattiamo i seguenti dati personali:</p>
                    <ul>
                        <li>Username (obbligatorio)</li>
                        <li>Password (criptata)</li>
                        <li>Indirizzo IP</li>
                        <li>Data e ora di registrazione</li>
                        <li>Storico delle votazioni</li>
                    </ul>
                </div>

                <div class="privacy-section">
                    <h3>3. Finalità del Trattamento</h3>
                    <p>I dati vengono trattati per le seguenti finalità:</p>
                    <ul>
                        <li>Gestione dell'account utente</li>
                        <li>Partecipazione ai sondaggi</li>
                        <li>Analisi statistica anonima</li>
                        <li>Miglioramento del servizio</li>
                        <li>Conformità legale</li>
                    </ul>
                </div>

                <div class="privacy-section">
                    <h3>4. Base Giuridica</h3>
                    <p>Il trattamento si basa sul consenso esplicito dell'utente e sulla necessità di esecuzione del contratto.</p>
                </div>

                <div class="privacy-section">
                    <h3>5. Conservazione Dati</h3>
                    <p>I dati personali vengono conservati per il tempo necessario al perseguimento delle finalità per cui sono stati raccolti, nel rispetto dei termini di legge.</p>
                </div>

                <div class="privacy-section">
                    <h3>6. Diritti dell'Interessato</h3>
                    <p>In ogni momento puoi esercitare i tuoi diritti:</p>
                    <ul>
                        <li>Accesso ai dati personali</li>
                        <li>Rettifica dei dati inesatti</li>
                        <li>Cancellazione dei dati ("diritto all'oblio")</li>
                        <li>Limitazione del trattamento</li>
                        <li>Portabilità dei dati</li>
                        <li>Opposizione al trattamento</li>
                    </ul>
                </div>

                <div class="privacy-section">
                    <h3>7. Sicurezza Dati</h3>
                    <p>Adottiamo misure di sicurezza tecniche e organizzative adeguate per proteggere i dati personali da accessi non autorizzati, modifiche, divulgazioni o distruzioni.</p>
                </div>

                <div class="privacy-section">
                    <h3>8. Trasferimento Dati</h3>
                    <p>I dati non vengono trasferiti al di fuori dello Spazio Economico Europeo (SEE).</p>
                </div>

                <div class="privacy-section">
                    <h3>9. Contatti</h3>
                    <p>Per esercitare i tuoi diritti o per qualsiasi domanda sulla privacy, contattaci all'indirizzo: privacy@sondaggi.it</p>
                </div>

                <div class="privacy-section">
                    <h3>10. Modifiche all'Informativa</h3>
                    <p>Questa informativa privacy può essere aggiornata periodicamente. Le modifiche saranno pubblicate su questa pagina.</p>
                </div>
        </section>

        <section class="section">
            <div class="quick-actions">
                <a href="register.php" class="quick-action">Torna alla Registrazione</a>
                <a href="terms.php" class="quick-action">Termini di Servizio</a>
                <?php if (!is_logged_in()): ?>
                    <a href="index.php" class="quick-action">Login</a>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <style>
        .privacy-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .privacy-section {
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #f8fafc, #e5e7eb);
            border-radius: 12px;
            border-left: 4px solid #4f46e5;
        }

        .privacy-section h3 {
            color: #4f46e5;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .privacy-section p {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .privacy-section ul {
            color: #6b7280;
            padding-left: 20px;
        }

        .privacy-section li {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .privacy-footer {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-radius: 12px;
            border: 1px solid #bae6fd;
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
