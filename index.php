<?php
require_once 'app.php';
$pdo = getDb();
$total = countQuestions($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" href="icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="icon.svg">
    <link rel="manifest" href="manifest.json">
    <title>MockTest Application</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>MockTest Application</h1>
            <p>Upload previous year questions and take a mock test instantly.</p>
        </header>

        <section class="card">
            <h2>Getting started</h2>
            <p><strong>Total questions available:</strong> <?php echo $total; ?></p>
            <div class="actions">
                <a class="button" href="upload.php">Upload Questions</a>
                <a class="button secondary" href="quiz.php">Take MockTest</a>
            </div>
        </section>

        <section class="card">
            <h2>Upload template</h2>
            <p>Download the sample CSV template and fill it with previous year questions.</p>
            <a class="button" href="questions-template.csv" download>Download CSV Template</a>
        </section>

        <footer>
            <p>Deploy in XAMPP at <code>http://localhost/mocktest/</code></p>
        </footer>
    </div>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('sw.js').catch(function (error) {
                    console.error('Service worker registration failed:', error);
                });
            });
        }
    </script>
</body>
</html>
