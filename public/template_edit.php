<?php
require_once __DIR__.'/../functions.php';
require_once __DIR__.'/../db.php';
$pdo = get_pdo();

$id = $_GET['id'] ?? null;
$topic_id = $_GET['topic_id'] ?? null;
$template = [
    'name'=>'','description'=>'','schema_json'=>'{}','is_active'=>1,
    'schedule_active'=>0,'schedule_freq'=>'DAILY','schedule_interval'=>1,
    'schedule_time'=>'08:00','schedule_notify_emails'=>'','topic_id'=>$topic_id
];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM form_templates WHERE id=?');
    $stmt->execute([$id]);
    $tpl = $stmt->fetch();
    if (!$tpl) die('Template not found');
    $template = $tpl;
    $topic_id = $tpl['topic_id'];
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $data = $template;
    foreach ($data as $k=>$v) {
        if(isset($_POST[$k])) $data[$k] = $_POST[$k];
    }
    $data['is_active'] = isset($_POST['is_active'])?1:0;
    $data['schedule_active'] = isset($_POST['schedule_active'])?1:0;
    if ($data['schedule_active']) {
        $data['schedule_next_run'] = calculate_next_run($data['schedule_freq'], (int)$data['schedule_interval'], $data['schedule_time']);
    } else {
        $data['schedule_next_run'] = null;
    }
    if ($id) {
        $stmt = $pdo->prepare('UPDATE form_templates SET topic_id=:topic_id,name=:name,description=:description,schema_json=:schema_json,is_active=:is_active,schedule_active=:schedule_active,schedule_freq=:schedule_freq,schedule_interval=:schedule_interval,schedule_time=:schedule_time,schedule_notify_emails=:schedule_notify_emails,schedule_next_run=:schedule_next_run WHERE id=:id');
        $data['id']=$id;
        $stmt->execute($data);
    } else {
        $stmt = $pdo->prepare('INSERT INTO form_templates (topic_id,name,description,schema_json,is_active,schedule_active,schedule_freq,schedule_interval,schedule_time,schedule_notify_emails,schedule_next_run) VALUES (:topic_id,:name,:description,:schema_json,:is_active,:schedule_active,:schedule_freq,:schedule_interval,:schedule_time,:schedule_notify_emails,:schedule_next_run)');
        $stmt->execute($data);
        $id = $pdo->lastInsertId();
    }
    header('Location: templates.php?topic_id='.$data['topic_id']);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Template Edit</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/formeo@latest/dist/formeo.min.css">
</head>
<body class="p-4">
<h1><?= $id? 'Edit':'New' ?> Template</h1>
<form method="post">
<input type="hidden" name="topic_id" value="<?=esc($topic_id)?>">
<input type="hidden" name="schema_json" id="schema_json">
<div class="mb-3"><label class="form-label">Name<input type="text" name="name" class="form-control" value="<?=esc($template['name'])?>" required></label></div>
<div class="mb-3"><label class="form-label">Description<textarea name="description" class="form-control" rows="3"><?=esc($template['description'])?></textarea></label></div>
<div id="formeo-editor" style="border:1px solid #ccc;min-height:300px;"></div>
<div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_active" <?= $template['is_active']?'checked':'' ?>> <label class="form-check-label">Active</label></div>
<h3 class="mt-4">Schedule</h3>
<div class="form-check"><input class="form-check-input" type="checkbox" name="schedule_active" id="sched" <?= $template['schedule_active']?'checked':'' ?>> <label for="sched" class="form-check-label">Enable Schedule</label></div>
<div class="row mt-2">
<div class="col-md-3"><label class="form-label">Frequency<select name="schedule_freq" class="form-select"><option value="DAILY" <?= $template['schedule_freq']=='DAILY'?'selected':'' ?>>Daily</option><option value="WEEKLY" <?= $template['schedule_freq']=='WEEKLY'?'selected':'' ?>>Weekly</option><option value="MONTHLY" <?= $template['schedule_freq']=='MONTHLY'?'selected':'' ?>>Monthly</option><option value="YEARLY" <?= $template['schedule_freq']=='YEARLY'?'selected':'' ?>>Yearly</option></select></label></div>
<div class="col-md-3"><label class="form-label">Interval<input type="number" name="schedule_interval" class="form-control" value="<?=esc($template['schedule_interval'])?>" min="1"></label></div>
<div class="col-md-3"><label class="form-label">Time<input type="time" name="schedule_time" class="form-control" value="<?=esc($template['schedule_time'])?>"></label></div>
</div>
<div class="mb-3"><label class="form-label">Notify Emails<textarea name="schedule_notify_emails" class="form-control" rows="2"><?=esc($template['schedule_notify_emails'])?></textarea></label></div>
<button class="btn btn-primary">Save</button>
<a class="btn btn-secondary" href="templates.php?topic_id=<?=$topic_id?>">Cancel</a>
</form>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/formeo@latest/dist/formeo.min.js"></script>
<script>
const editor = new window.FormeoEditor({container: '#formeo-editor'});
let existing = <?=json_encode($template['schema_json'] ? json_decode($template['schema_json'], true) : new stdClass())?>;
if (existing && Object.keys(existing).length) {
    editor.setData(existing);
}
document.querySelector('form').addEventListener('submit', function(){
    document.getElementById('schema_json').value = JSON.stringify(editor.formData);
});
</script>
</body>
</html>
