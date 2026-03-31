<?php
require_once 'app.php';
$pdo = getDb();
$total = countQuestions($pdo);
$questions = [];
$results = [];
$score = 0;
$maxScore = 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'start') {
        $count = max(1, min(20, (int) ($_POST['question_count'] ?? 10)));

        if ($total === 0) {
            $error = 'There are no questions available. Please upload questions first.';
        } else {
            $count = min($count, $total);
            $questions = getRandomQuestions($pdo, $count);
        }
    } elseif ($action === 'submit') {
        $questionIds = array_map('intval', $_POST['question_id'] ?? []);
        $givenAnswers = $_POST['answer'] ?? [];

        if (empty($questionIds)) {
            $error = 'No answers were submitted.';
        } else {
            $questionBank = getQuestionsByIds($pdo, $questionIds);
            $maxScore = count($questionIds);

            foreach ($questionIds as $index => $id) {
                $question = $questionBank[$id] ?? null;
                if (!$question) {
                    continue;
                }

                $selected = trim($givenAnswers[$index] ?? '');
                $correct = trim($question['answer']);
                $isCorrect = strcasecmp($selected, $correct) === 0;

                if ($isCorrect) {
                    $score++;
                }

                $results[] = [
                    'question' => $question['question'],
                    'selected' => $selected,
                    'correct' => $correct,
                    'options' => [$question['option1'], $question['option2'], $question['option3'], $question['option4']],
                    'isCorrect' => $isCorrect,
                ];
            }
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
    <title>Take MockTest</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>MockTest</h1>
            <p>Answer the uploaded questions and get instant feedback.</p>
        </header>

        <?php if ($error !== ''): ?>
            <section class="card">
                <div class="notification error"><?php echo htmlspecialchars($error); ?></div>
                <p><a href="index.php">← Back to Home</a></p>
            </section>
        <?php elseif (!empty($results)): ?>
            <section class="card">
                <h2>Results</h2>
                <p>You scored <strong><?php echo $score; ?>/<?php echo $maxScore; ?></strong>.</p>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Question</th>
                            <th>Your answer</th>
                            <th>Correct answer</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $index => $result): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($result['question']); ?></td>
                                <td><?php echo htmlspecialchars($result['selected']); ?></td>
                                <td><?php echo htmlspecialchars($result['correct']); ?></td>
                                <td><?php echo $result['isCorrect'] ? 'Correct' : 'Incorrect'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><a class="button" href="quiz.php">Try another test</a></p>
            </section>
        <?php elseif (!empty($questions)): ?>
            <section class="card">
                <h2>Mock Test Questions</h2>
                <form method="post">
                    <input type="hidden" name="action" value="submit">
                    <?php foreach ($questions as $index => $question): ?>
                        <article class="question-block">
                            <h3>Question <?php echo $index + 1; ?></h3>
                            <p><?php echo htmlspecialchars($question['question']); ?></p>
                            <?php foreach (['option1', 'option2', 'option3', 'option4'] as $optionKey): ?>
                                <label>
                                    <input type="radio" name="answer[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($question[$optionKey]); ?>" required>
                                    <?php echo htmlspecialchars($question[$optionKey]); ?>
                                </label>
                            <?php endforeach; ?>
                            <input type="hidden" name="question_id[]" value="<?php echo $question['id']; ?>">
                        </article>
                    <?php endforeach; ?>
                    <button type="submit" class="button">Submit Answers</button>
                </form>
            </section>
        <?php else: ?>
            <section class="card">
                <h2>Start a New MockTest</h2>
                <p>Total questions available: <strong><?php echo $total; ?></strong></p>
                <form method="post">
                    <input type="hidden" name="action" value="start">
                    <div class="form-group">
                        <label for="question_count">Number of questions</label>
                        <input type="number" id="question_count" name="question_count" value="10" min="1" max="20">
                    </div>
                    <button type="submit" class="button">Start Test</button>
                </form>
                <p><a href="index.php">← Back to Home</a></p>
            </section>
        <?php endif; ?>
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
