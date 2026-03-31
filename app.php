<?php

function getConfig(): array
{
    static $config = null;

    if ($config === null) {
        $configPath = __DIR__ . '/config.php';
        if (!file_exists($configPath)) {
            throw new RuntimeException('Missing config.php. Copy config.php.example or create config.php with database settings.');
        }

        $config = include $configPath;
    }

    return $config;
}

function getDbDriver(): string
{
    $config = getConfig();
    return strtolower($config['db_driver'] ?? 'sqlite');
}

function getDbPath(): string
{
    $config = getConfig();
    return $config['sqlite_path'] ?? __DIR__ . '/data/questions.db';
}

function getDb(): PDO
{
    $driver = getDbDriver();
    $config = getConfig();

    if ($driver === 'mysql') {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['mysql_host'],
            $config['mysql_db'],
            $config['mysql_charset'] ?? 'utf8mb4'
        );

        $pdo = new PDO($dsn, $config['mysql_user'], $config['mysql_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } else {
        $dbPath = getDbPath();
        $dir = dirname($dbPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    ensureSchema($pdo);
    return $pdo;
}

function ensureSchema(PDO $pdo): void
{
    $driver = getDbDriver();

    if ($driver === 'mysql') {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS questions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                question TEXT NOT NULL,
                option1 VARCHAR(255) NOT NULL,
                option2 VARCHAR(255) NOT NULL,
                option3 VARCHAR(255) NOT NULL,
                option4 VARCHAR(255) NOT NULL,
                answer VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    } else {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS questions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                question TEXT NOT NULL,
                option1 TEXT NOT NULL,
                option2 TEXT NOT NULL,
                option3 TEXT NOT NULL,
                option4 TEXT NOT NULL,
                answer TEXT NOT NULL
            )'
        );
    }
}

function countQuestions(PDO $pdo): int
{
    return (int) $pdo->query('SELECT COUNT(*) FROM questions')->fetchColumn();
}

function getRandomQuestions(PDO $pdo, int $limit): array
{
    $orderBy = getDbDriver() === 'mysql' ? 'RAND()' : 'RANDOM()';
    $stmt = $pdo->prepare("SELECT id, question, option1, option2, option3, option4, answer FROM questions ORDER BY $orderBy LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getQuestionsByIds(PDO $pdo, array $ids): array
{
    if (count($ids) === 0) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, question, option1, option2, option3, option4, answer FROM questions WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $questionsById = [];
    foreach ($questions as $question) {
        $questionsById[$question['id']] = $question;
    }

    return $questionsById;
}

function normalizeHeader(array $header): array
{
    return array_map(function ($value) {
        return mb_strtolower(trim($value));
    }, $header);
}

function isHeaderRow(array $row): bool
{
    $header = normalizeHeader($row);
    $expected = ['question', 'option 1', 'option1', 'option 2', 'option2', 'option 3', 'option3', 'option 4', 'option4', 'answer'];
    return count(array_intersect($header, $expected)) >= 4;
}

function parseCsvFile(string $path): array
{
    $questions = [];
    if (($handle = fopen($path, 'r')) === false) {
        return $questions;
    }

    $rowIndex = 0;
    while (($row = fgetcsv($handle)) !== false) {
        $rowIndex++;
        if ($rowIndex === 1 && isHeaderRow($row)) {
            continue;
        }

        if (count($row) < 6) {
            continue;
        }

        $question = trim($row[0]);
        $option1 = trim($row[1]);
        $option2 = trim($row[2]);
        $option3 = trim($row[3]);
        $option4 = trim($row[4]);
        $answer = trim($row[5]);

        if ($question === '' || $option1 === '' || $option2 === '' || $option3 === '' || $option4 === '' || $answer === '') {
            continue;
        }

        $questions[] = [
            'question' => $question,
            'option1' => $option1,
            'option2' => $option2,
            'option3' => $option3,
            'option4' => $option4,
            'answer' => $answer,
        ];
    }

    fclose($handle);
    return $questions;
}
