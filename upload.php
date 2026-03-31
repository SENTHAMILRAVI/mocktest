<?php
require_once 'app.php';
$pdo = getDb();
$message = '';
$imported = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['questions_file']) || $_FILES['questions_file']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Please choose a valid CSV file to upload.';
    } else {
        $filePath = $_FILES['questions_file']['tmp_name'];
        $questions = parseCsvFile($filePath);

        if (count($questions) === 0) {
            $message = 'No valid questions were found in the uploaded CSV file.';
        } else {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO questions (question, option1, option2, option3, option4, answer) VALUES (:question, :option1, :option2, :option3, :option4, :answer)');

            foreach ($questions as $question) {
                $stmt->execute([
                    ':question' => $question['question'],
                    ':option1' => $question['option1'],
                    ':option2' => $question['option2'],
                    ':option3' => $question['option3'],
                    ':option4' => $question['option4'],
                    ':answer' => $question['answer'],
                ]);
                $imported++;
            }

            $pdo->commit();
            $message = "Imported $imported questions successfully.";
        }
    }
}
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
    <title>Upload Questions - MockTest</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Upload Previous Year Questions</h1>
            <p>Use a CSV file with six columns: Question, Option 1, Option 2, Option 3, Option 4, Answer.</p>
        </header>

        <section class="card">
            <?php if ($message !== ''): ?>
                <div class="notification"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="questions_file">Choose CSV file</label>
                    <input type="file" id="questions_file" name="questions_file" accept=".csv,text/csv" required>
                </div>
                <button type="submit" class="button">Upload Questions</button>
            </form>

            <div class="info-block">
                <h3>CSV format</h3>
                <p>Example header row:</p>
                <pre>Question,Option 1,Option 2,Option 3,Option 4,Answer</pre>
                <p>The answer value must exactly match one of the 4 options.</p>
            </div>

            <p><a href="index.php">← Back to Home</a></p>
        </section>
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
