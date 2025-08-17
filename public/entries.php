<?php
require_once __DIR__.'/../functions.php';
require_once __DIR__.'/../db.php';
$pdo = get_pdo();
$template_id = $_GET['template_id'] ?? null;
if (!$template_id) die('template_id missing');
$template = $pdo->prepare('SELECT * FROM form_templates WHERE id=?');
$template->execute([$template_id]);
$template = $template->fetch();
if (!$template) die('Template not found');
$topic = $pdo->prepare('SELECT * FROM topics WHERE id=?');
$topic->execute([$template['topic_id']]);
$topic = $topic->fetch();

if (isset($_GET['delete_id'])) {
    $stmt=$pdo->prepare('DELETE FROM form_entries WHERE id=?');
    $stmt->execute([$_GET['delete_id']]);
    header('Location: entries.php?template_id='.$template_id);
    exit;
}

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$created_by = $_GET['created_by'] ?? '';
$where = ['template_id=?'];
$params = [$template_id];
if ($from) { $where[] = 'created_at >= ?'; $params[] = $from.' 00:00:00'; }
if ($to) { $where[] = 'created_at <= ?'; $params[] = $to.' 23:59:59'; }
if ($created_by) { $where[] = 'created_by=?'; $params[] = $created_by; }
$sql = 'SELECT * FROM form_entries WHERE '.implode(' AND ',$where).' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$entries = $stmt->fetchAll();

$counts=[]; foreach($entries as $e){$data=json_decode($e['data_json'],true)?:[];foreach($data as $k=>$v){$counts[$k]=($counts[$k]??0)+1;}}
arsort($counts); $preview_keys=array_slice(array_keys($counts),0,5);
$labels = schema_labels(json_decode($template['schema_json'],true)?:[]);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Entries</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>
<body class="p-4">
<h1>Entries - <?=esc($template['name'])?></h1>
<form class="row g-2 mb-3" method="get">
<input type="hidden" name="template_id" value="<?=esc($template_id)?>">
<div class="col-md-2"><input type="date" name="from" class="form-control" value="<?=esc($from)?>" placeholder="From"></div>
<div class="col-md-2"><input type="date" name="to" class="form-control" value="<?=esc($to)?>" placeholder="To"></div>
<div class="col-md-2"><input type="text" name="created_by" class="form-control" value="<?=esc($created_by)?>" placeholder="Created By"></div>
<div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
<div class="col-md-2"><a href="entry_edit.php?template_id=<?=$template_id?>" class="btn btn-success">New Entry</a></div>
<div class="col-md-2"><a href="download_csv.php?template_id=<?=$template_id?>" class="btn btn-secondary">CSV</a></div>
</form>
<table id="entries" class="table table-bordered">
<thead><tr><th>ID</th><th>Created At</th><th>Created By</th><?php foreach($preview_keys as $k): ?><th><?=esc($labels[$k]??$k)?></th><?php endforeach; ?><th>Actions</th></tr></thead>
<tbody>
<?php foreach($entries as $e): $data=json_decode($e['data_json'],true)?:[]; ?>
<tr>
<td><?=esc($e['id'])?></td>
<td><?=esc($e['created_at'])?></td>
<td><?=esc($e['created_by'])?></td>
<?php foreach($preview_keys as $k): $v=$data[$k]??''; if(is_array($v)) $v=implode('|',$v); ?><td><?=esc($v)?></td><?php endforeach; ?>
<td>
<button class="btn btn-sm btn-info" data-id="<?=$e['id']?>" data-bs-toggle="modal" data-bs-target="#detailModal">Detail</button>
<a class="btn btn-sm btn-primary" href="entry_edit.php?id=<?=$e['id']?>">Edit</a>
<a class="btn btn-sm btn-danger" href="?template_id=<?=$template_id?>&delete_id=<?=$e['id']?>" onclick="return confirm('Delete?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="modal" id="detailModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Detail</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">Loading...</div></div></div></div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){ $('#entries').DataTable(); });
$('#detailModal').on('show.bs.modal', function (event) {
  var id = $(event.relatedTarget).data('id');
  var modal = $(this);
  modal.find('.modal-body').load('entry_detail.php?id='+id);
});
</script>
<a href="templates.php?topic_id=<?=$template['topic_id']?>" class="btn btn-secondary mt-3">Back</a>
</body>
</html>
