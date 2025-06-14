<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AppMobTimeTouch - Application Mobile Requise</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .info-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            text-align: center;
        }
        
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
            color: #667eea;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .mobile-link {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin-top: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .mobile-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .features {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: left;
        }
        
        .features h3 {
            color: #333;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .features ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .features li {
            padding: 8px 0;
            color: #555;
        }
        
        .features li:before {
            content: "✓ ";
            color: #28a745;
            font-weight: bold;
            margin-right: 8px;
        }
        
        .system-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="info-container">
        <div class="icon">📱</div>
        <h1>AppMobTimeTouch</h1>
        <p><strong>Application de pointage mobile pour Dolibarr</strong></p>
        
        <p>Cette application est spécialement conçue et optimisée pour les appareils mobiles (smartphones et tablettes).</p>
        
        <div class="features">
            <h3>Fonctionnalités disponibles sur mobile :</h3>
            <ul>
                <li>Pointage entrée/sortie avec géolocalisation</li>
                <li>Suivi temps réel des heures travaillées</li>
                <li>Résumés quotidiens et hebdomadaires</li>
                <li>Consultation de l'historique des pointages</li>
                <li>Interface tactile optimisée</li>
                <li>Fonctionnement hors ligne</li>
            </ul>
        </div>
        
        <p>Pour une expérience optimale, veuillez accéder à cette application depuis votre smartphone ou tablette.</p>
        
        <a href="home.php" class="mobile-link">Accéder à l'application mobile →</a>
        
        <div class="system-info">
            <strong>Information système :</strong><br>
            Votre navigateur : <?php echo dol_escape_htmltag($_SERVER['HTTP_USER_AGENT']); ?><br>
            Module AppMobTimeTouch activé • Version <?php echo $version ?? '1.0'; ?>
        </div>
    </div>
</body>
</html>