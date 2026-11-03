<?php
require __DIR__ . '/auth.php';
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_admin();

$pdo = new PDO('mysql:host=127.0.0.1;dbname=gigabyte;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'], $_POST['id'])) {
    $allowed = ['new','in_progress','done','spam'];
    if (in_array($_POST['status'], $allowed, true)) {
        $st = $pdo->prepare('UPDATE leads SET status = ? WHERE id = ?');
        $st->execute([$_POST['status'], (int)$_POST['id']]);
    }
    header('Location: index.php'); exit;
}

$rows = $pdo->query('SELECT * FROM leads ORDER BY id DESC LIMIT 200')->fetchAll();
?><!doctype html><meta charset="utf-8"><title>Заявки — Гигабайт</title>
<style>
body{font-family:system-ui;background:#0B0F0D;color:#E9F2EC;margin:0;padding:24px}
h1{font-size:20px;margin:0 0 16px}
table{width:100%;border-collapse:collapse;font-size:14px}
th,td{border-bottom:1px solid #24332B;padding:8px 10px;text-align:left;vertical-align:top}
th{color:#9FB0A7;font-weight:600}
.badge{padding:2px 8px;border-radius:100px;font-size:12px;border:1px solid #24332B}
.new{color:#D8F24B}.in_progress{color:#4DA3FF}.done{color:#1AE58A}.spam{color:#FF7A6B}
form{display:inline}
select,button{background:#101713;color:#E9F2EC;border:1px solid #24332B;border-radius:6px;padding:4px 6px}
a.logout{float:right;color:#9FB0A7}
</style>
<h1>Заявки <a class="logout" href="logout.php">Выйти</a></h1>
<table>
<tr><th>ID</th><th>Дата</th><th>Имя</th><th>Телефон</th><th>Город</th><th>Устройство</th><th>Проблема</th><th>Статус</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?= (int)$r['id'] ?></td>
  <td><?= htmlspecialchars($r['created_at']) ?></td>
  <td><?= htmlspecialchars($r['name']) ?></td>
  <td><?= htmlspecialchars($r['phone']) ?></td>
  <td><?= htmlspecialchars($r['city']) ?></td>
  <td><?= htmlspecialchars($r['device']) ?></td>
  <td><?= htmlspecialchars(mb_substr((string)$r['fault'], 0, 120)) ?></td>
  <td>
    <span class="badge <?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span>
    <form method="post">
      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
      <select name="status">
        <?php foreach (['new','in_progress','done','spam'] as $s): ?>
          <option value="<?= $s ?>" <?= $r['status']===$s?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
      <button>OK</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</table>