<?php
require_once __DIR__.'/../functions.php';
require_once __DIR__.'/../db.php';
$pdo = get_pdo();

$id = $_GET['id'] ?? null;
$name = '';
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM topics WHERE id=?');
    $stmt->execute([$id]);
    $topic = $stmt->fetch();
    if (!$topic) { die('Topic not found'); }
    $name = $topic['name'];
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = trim($_POST['name'] ?? '');
    if ($id) {
        $stmt = $pdo->prepare('UPDATE topics SET name=? WHERE id=?');
        $stmt->execute([$name, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO topics (name) VALUES (?)');
        $stmt->execute([$name]);
        $id = $pdo->lastInsertId();
    }
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Topic Edit</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h1><?= $id ? 'Edit' : 'New' ?> Topic</h1>
<form method="post">
<div class="mb-3"><label class="form-label">Name<input type="text" name="name" class="form-control" value="<?=esc($name)?>" required></label></div>
<button class="btn btn-primary">Save</button>
<a href="index.php" class="btn btn-secondary">Cancel</a>
</form>
</body>
</html>
