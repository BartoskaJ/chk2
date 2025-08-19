<?php
require_once __DIR__.'/../functions.php';
require_once __DIR__.'/../db.php';
$lockFile = fopen('/tmp/checklist_scheduler.lock', 'c');
if (!flock($lockFile, LOCK_EX | LOCK_NB)) {
    exit; // another instance running
}
$pdo = get_pdo();
$now = date('Y-m-d H:i:s');
$sql = "SELECT * FROM form_templates WHERE schedule_active=1 AND is_active=1 AND schedule_next_run IS NOT NULL AND schedule_next_run <= ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$now]);
$templates = $stmt->fetchAll();
foreach ($templates as $tpl) {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO form_entries (template_id, topic_id, data_json, created_by) VALUES (?,?,?,?)');
    $stmt->execute([$tpl['id'], $tpl['topic_id'], '{}', 'scheduler']);
    $entryId = $pdo->lastInsertId();
    $emails = preg_split('/[\s,;]+/', $tpl['schedule_notify_emails']);
    $url = base_url('entry_edit.php?id='.$entryId);
    foreach ($emails as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            send_mail($email, 'Checklist: '.$tpl['name'], '<p>New checklist entry created: <a href="'.$url.'">Edit entry</a></p>');
        }
    }
    $next = calculate_next_run($tpl['schedule_freq'], (int)$tpl['schedule_interval'], $tpl['schedule_time'], $tpl['schedule_next_run']);
    $upd = $pdo->prepare('UPDATE form_templates SET schedule_last_run=?, schedule_next_run=? WHERE id=?');
    $upd->execute([$now, $next, $tpl['id']]);
    $pdo->commit();
}
flock($lockFile, LOCK_UN);
