<?php
require_once __DIR__.'/../functions.php';
require_once __DIR__.'/../db.php';
$pdo = get_pdo();
$template_id = $_GET['template_id'] ?? null;
if (!$template_id) die('template_id');
$template = $pdo->prepare('SELECT * FROM form_templates WHERE id=?');
$template->execute([$template_id]);
$template=$template->fetch();
if(!$template) die('not found');
$labels = schema_labels(json_decode($template['schema_json'],true)?:[]);
$entries=$pdo->prepare('SELECT * FROM form_entries WHERE template_id=? ORDER BY created_at');
$entries->execute([$template_id]);
$entries=$entries->fetchAll();
$keys=[]; foreach($entries as $e){$data=json_decode($e['data_json'],true)?:[]; $keys=array_unique(array_merge($keys,array_keys($data)));}
$fh=fopen('php://output','w');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="entries_'.$template_id.'.csv"');
$header=array_merge(['id','created_at','created_by'],array_map(fn($k)=>$labels[$k]??$k,$keys));
fputcsv($fh,$header);
foreach($entries as $e){$data=json_decode($e['data_json'],true)?:[];$row=[$e['id'],$e['created_at'],$e['created_by']];foreach($keys as $k){$v=$data[$k]??'';if(is_array($v))$v=implode('|',$v);$row[]=$v;}fputcsv($fh,$row);}
exit;
