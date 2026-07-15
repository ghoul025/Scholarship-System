<?php
require_once __DIR__ . '/../config.php';

// Read-only preview: show how existing `full_name` values would be split
// into first_name, middle_name, last_name using a naive token-based approach.

try {
    $stmt = $pdo->query("SELECT id, COALESCE(full_name, CONCAT_WS(' ', first_name, middle_name, last_name)) AS full_name FROM scholars ORDER BY id");
    $rows = $stmt->fetchAll();
} catch (Exception $e) {
    die('Failed to read scholars: ' . htmlspecialchars($e->getMessage()));
}

function split_name_preview($fullname) {
    $fullname = trim(preg_replace('/\s+/', ' ', $fullname));
    if ($fullname === '') return ['first' => '', 'middle' => '', 'last' => ''];
    $parts = explode(' ', $fullname);
    $count = count($parts);
    if ($count === 1) {
        return ['first' => $parts[0], 'middle' => '', 'last' => ''];
    }
    if ($count === 2) {
        return ['first' => $parts[0], 'middle' => '', 'last' => $parts[1]];
    }
    // 3 or more: first = first, last = last, middle = join(1..n-2)
    $first = array_shift($parts);
    $last = array_pop($parts);
    $middle = implode(' ', $parts);
    return ['first' => $first, 'middle' => $middle, 'last' => $last];
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview name split - Scholarship System</title>
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2>Preview: Proposed name splits</h2>
    <p>This is read-only. It shows how existing full_name values would be split into first, middle, last using the migration logic.</p>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Existing full_name</th>
                <th>Proposed first_name</th>
                <th>Proposed middle_name</th>
                <th>Proposed last_name</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r):
            $s = split_name_preview($r['full_name']);
        ?>
            <tr>
                <td><?php echo htmlspecialchars($r['id']); ?></td>
                <td><?php echo htmlspecialchars($r['full_name']); ?></td>
                <td><?php echo htmlspecialchars($s['first']); ?></td>
                <td><?php echo htmlspecialchars($s['middle']); ?></td>
                <td><?php echo htmlspecialchars($s['last']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
