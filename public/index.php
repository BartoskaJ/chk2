<?php
require_once __DIR__.'/../functions.php';
require_once __DIR__.'/../db.php';

$pdo = get_pdo();
$topics = $pdo->query('SELECT * FROM topics ORDER BY name')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Checklist App</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h1>Topics</h1>
<a href="topic_edit.php" class="btn btn-success mb-3">New Topic</a>
<table class="table table-bordered">
<thead><tr><th>ID</th><th>Name</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($topics as $t): ?>
<tr>
<td><?=esc($t['id'])?></td>
<td><?=esc($t['name'])?></td>
<td><a class="btn btn-sm btn-primary" href="topic_edit.php?id=<?=$t['id']?>">Edit</a> <a class="btn btn-sm btn-secondary" href="templates.php?topic_id=<?=$t['id']?>">Templates</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
