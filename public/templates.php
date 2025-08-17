<?php
require_once __DIR__.'/../functions.php';
require_once __DIR__.'/../db.php';
$pdo = get_pdo();
$topic_id = $_GET['topic_id'] ?? null;
if (!$topic_id) { die('topic_id missing'); }
$topic = $pdo->prepare('SELECT * FROM topics WHERE id=?');
$topic->execute([$topic_id]);
$topic = $topic->fetch();
if (!$topic) die('Topic not found');

$templates = $pdo->prepare('SELECT * FROM form_templates WHERE topic_id=? ORDER BY name');
$templates->execute([$topic_id]);
$templates = $templates->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Templates</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h1>Templates - <?=esc($topic['name'])?></h1>
<a class="btn btn-success mb-3" href="template_edit.php?topic_id=<?=$topic_id?>">New Template</a>
<table class="table table-bordered">
<thead><tr><th>ID</th><th>Name</th><th>Active</th><th>Schedule</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($templates as $tpl): ?>
<tr>
<td><?=esc($tpl['id'])?></td>
<td><?=esc($tpl['name'])?></td>
<td><?= $tpl['is_active'] ? 'Yes':'No' ?></td>
<td><?= $tpl['schedule_active']?esc($tpl['schedule_freq'].'/'.$tpl['schedule_interval'].' '.$tpl['schedule_time']):'No' ?></td>
<td>
<a class="btn btn-sm btn-primary" href="template_edit.php?id=<?=$tpl['id']?>">Edit</a>
<a class="btn btn-sm btn-secondary" href="entries.php?template_id=<?=$tpl['id']?>">Entries</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<a href="index.php" class="btn btn-secondary">Back</a>
</body>
</html>
