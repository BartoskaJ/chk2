<?php
require_once __DIR__.'/../functions.php';
require_once __DIR__.'/../db.php';
$pdo = get_pdo();
$id = $_GET['id'] ?? null;
if (!$id) die('Missing id');
$entry = $pdo->prepare('SELECT * FROM form_entries WHERE id=?');
$entry->execute([$id]);
$entry = $entry->fetch();
if (!$entry) die('Not found');
$template = $pdo->prepare('SELECT * FROM form_templates WHERE id=?');
$template->execute([$entry['template_id']]);
$template = $template->fetch();
$labels = schema_labels(json_decode($template['schema_json'],true)?:[]);
$data = json_decode($entry['data_json'],true)?:[];
?>
<table class="table table-bordered">
<?php foreach($data as $k=>$v): if(is_array($v)) $v=implode(', ',$v); ?>
<tr><th><?=esc($labels[$k]??$k)?></th><td><?=esc($v)?></td></tr>
<?php endforeach; ?>
<tr><th>Created By</th><td><?=esc($entry['created_by'])?></td></tr>
<tr><th>Created At</th><td><?=esc($entry['created_at'])?></td></tr>
</table>
