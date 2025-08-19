<?php
require_once __DIR__.'/../functions.php';
require_once __DIR__.'/../db.php';
$pdo = get_pdo();
$id = $_GET['id'] ?? null;
$template_id = $_GET['template_id'] ?? null;
$entry = ['data_json'=>'{}','created_by'=>''];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM form_entries WHERE id=?');
    $stmt->execute([$id]);
    $entry = $stmt->fetch();
    if (!$entry) die('Entry not found');
    $template_id = $entry['template_id'];
}
$template = $pdo->prepare('SELECT * FROM form_templates WHERE id=?');
$template->execute([$template_id]);
$template = $template->fetch();
if (!$template) die('Template not found');

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $data_json = $_POST['data_json'] ?? '{}';
    $created_by = $_POST['created_by'] ?? 'user';
    if ($id) {
        $stmt=$pdo->prepare('UPDATE form_entries SET data_json=?, created_by=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([$data_json,$created_by,$id]);
    } else {
        $stmt=$pdo->prepare('INSERT INTO form_entries (template_id, topic_id, data_json, created_by) VALUES (?,?,?,?)');
        $stmt->execute([$template_id,$template['topic_id'],$data_json,$created_by]);
        $id = $pdo->lastInsertId();
    }
    header('Location: entries.php?template_id='.$template_id);
    exit;
}
$schema = $template['schema_json'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Entry</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/formeo@latest/dist/formeo.min.css">
</head>
<body class="p-4">
<h1><?= $id? 'Edit':'New' ?> Entry - <?=esc($template['name'])?></h1>
<form method="post">
<input type="hidden" name="data_json" id="data_json">
<div id="form-area"></div>
<div class="mb-3 mt-3"><label class="form-label">Created By<input type="text" name="created_by" class="form-control" value="<?=esc($entry['created_by'])?>"></label></div>
<button class="btn btn-primary">Save</button>
<a class="btn btn-secondary" href="entries.php?template_id=<?=$template_id?>">Cancel</a>
</form>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/formeo@latest/dist/formeo.min.js"></script>
<script>
const renderer = new window.FormeoRenderer({renderContainer: '#form-area', data: JSON.parse(<?=json_encode($schema)?>)});
const existing = JSON.parse(<?=json_encode($entry['data_json'])?>);
window.addEventListener('load', () => {
    for (const k in existing) {
        const el = document.querySelector('[name="'+k+'"],#'+k);
        if (!el) continue;
        const val = existing[k];
        if (el.type === 'checkbox') {
            if (Array.isArray(val)) {
                val.forEach(v=>{ const c=document.querySelector('[name="'+k+'"][value="'+v+'"]; if(c) c.checked=true; });
            } else {
                el.checked = val==1 || el.value==val;
            }
        } else if (el.type === 'radio') {
            const r=document.querySelector('[name="'+k+'"][value="'+val+'"]; if(r) r.checked=true;');
        } else if (el.options && el.multiple) {
            Array.from(el.options).forEach(o=>{o.selected=val.includes(o.value);});
        } else {
            el.value = val;
        }
    }
});
function collectData(){
    const data={};
    document.querySelectorAll('#form-area [name]').forEach(el=>{
        const name=el.name || el.id;
        if(el.type==='radio'){ if(el.checked) data[name]=el.value; }
        else if(el.type==='checkbox'){
            const boxes=document.querySelectorAll('#form-area [name="'+name+'"][type=checkbox]');
            if(boxes.length>1){ data[name]=data[name]||[]; if(el.checked) data[name].push(el.value); }
            else{ data[name]=el.checked ? (el.value!=='on'?el.value:1) : 0; }
        } else if(el.tagName==='SELECT' && el.multiple){
            data[name]=Array.from(el.options).filter(o=>o.selected).map(o=>o.value);
        } else {
            data[name]=el.value;
        }
    });
    return data;
}
document.querySelector('form').addEventListener('submit', function(){
    document.getElementById('data_json').value = JSON.stringify(collectData());
});
</script>
</body>
</html>
