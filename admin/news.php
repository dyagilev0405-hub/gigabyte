<?php
require __DIR__ . '/auth.php';
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_admin();

$pdo = new PDO('mysql:host=127.0.0.1;dbname=gigabyte;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function slugify(string $s): string {
    $s = mb_strtolower(trim($s));
    $map = ['а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z',
            'и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
            'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sch',
            'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya'];
    $s = strtr($s, $map);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-') ?: 'news-' . time();
}

$errors = [];
$edit   = null;

// ---------- Сохранение (создание / обновление) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id      = (int)($_POST['id'] ?? 0);
    $title   = trim((string)($_POST['title'] ?? ''));
    $slug    = trim((string)($_POST['slug'] ?? ''));
    $excerpt = trim((string)($_POST['excerpt'] ?? ''));
    $body    = trim((string)($_POST['body'] ?? ''));
    $cover   = trim((string)($_POST['cover'] ?? ''));
    $status  = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';

    if ($title === '' || mb_strlen($title) > 200) $errors[] = 'Заголовок обязателен (до 200 символов)';
    if ($body === '')                             $errors[] = 'Текст новости обязателен';
    if ($slug === '')                             $slug = slugify($title);
    if (!preg_match('/^[a-z0-9-]{1,220}$/', $slug)) $errors[] = 'Slug: только латиница, цифры, дефис';

    // проверка уникальности slug
    if (!$errors) {
        $chk = $pdo->prepare('SELECT id FROM news WHERE slug = ? AND id <> ?');
        $chk->execute([$slug, $id]);
        if ($chk->fetch()) $errors[] = 'Такой slug уже занят';
    }

    if (!$errors) {
        if ($id > 0) {
            $st = $pdo->prepare('UPDATE news SET title=?, slug=?, excerpt=?, body=?, cover=?, status=?,
                                 published_at = IF(?="published" AND published_at IS NULL, NOW(), published_at)
                                 WHERE id=?');
            $st->execute([$title, $slug, $excerpt, $body, $cover, $status, $status, $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO news (title, slug, excerpt, body, cover, status, published_at)
                                 VALUES (?, ?, ?, ?, ?, ?, IF(?="published", NOW(), NULL))');
            $st->execute([$title, $slug, $excerpt, $body, $cover, $status, $status]);
        }
        header('Location: news.php?saved=1');
        exit;
    }

    // при ошибке — возвращаемся в форму с введёнными данными
    $edit = compact('id','title','slug','excerpt','body','cover','status');
}

// ---------- Удаление ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM news WHERE id = ?')->execute([$id]);
    }
    header('Location: news.php?deleted=1');
    exit;
}

// ---------- Редактирование (GET) ----------
if (isset($_GET['edit']) && $edit === null) {
    $st = $pdo->prepare('SELECT * FROM news WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}

$rows = $pdo->query('SELECT * FROM news ORDER BY id DESC')->fetchAll();
?><!doctype html>
<html lang="ru">
<meta charset="utf-8">
<title>Новости — Гигабайт</title>
<style>
body{font-family:system-ui;background:#0B0F0D;color:#E9F2EC;margin:0;padding:24px;line-height:1.5}
h1{font-size:20px;margin:0 0 16px}
a{color:#1AE58A}
.topnav{margin-bottom:20px}
.topnav a{margin-right:16px;text-decoration:none}
.topnav a.on{color:#D8F24B}
table{width:100%;border-collapse:collapse;font-size:14px;margin-top:20px}
th,td{border-bottom:1px solid #24332B;padding:10px;text-align:left;vertical-align:top}
th{color:#9FB0A7;font-weight:600}
.badge{padding:2px 8px;border-radius:100px;font-size:12px;border:1px solid #24332B}
.published{color:#1AE58A;border-color:rgba(26,229,138,.4)}
.draft{color:#D8F24B;border-color:rgba(216,242,75,.4)}
form.inline{display:inline}
input,select,textarea{background:#101713;color:#E9F2EC;border:1px solid #24332B;border-radius:8px;padding:10px 12px;width:100%;box-sizing:border-box;font:inherit}
textarea{min-height:180px;resize:vertical}
label{display:block;margin:14px 0 6px;font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#9FB0A7;font-weight:700}
.box{background:#131C17;border:1px solid #24332B;border-radius:16px;padding:24px;margin-bottom:24px}
.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 18px;border-radius:100px;border:1.5px solid #24332B;background:transparent;color:#E9F2EC;font-weight:600;cursor:pointer;text-decoration:none;font-size:14px}
.btn-green{background:#1AE58A;color:#06120C;border-color:#1AE58A}
.btn-red{color:#FF7A6B;border-color:rgba(255,122,107,.4)}
.err{color:#FF7A6B;font-size:13px;margin-bottom:10px;background:rgba(255,122,107,.08);padding:10px 14px;border-radius:10px;border:1px solid rgba(255,122,107,.3)}
.ok{color:#1AE58A;font-size:13px;margin-bottom:10px;background:rgba(26,229,138,.08);padding:10px 14px;border-radius:10px;border:1px solid rgba(26,229,138,.3)}
.row2{display:grid;grid-template-columns:2fr 1fr;gap:16px}
.muted{color:#9FB0A7;font-size:13px}
select{appearance:none}
</style>

<h1>Новости <a href="index.php" style="float:right;font-size:14px;color:#9FB0A7">← к заявкам</a></h1>

<div class="topnav">
  <a href="index.php">Заявки</a>
  <a href="news.php" class="on">Новости</a>
  <a href="logout.php" style="float:right">Выйти</a>
</div>

<?php if (isset($_GET['saved'])):   ?><div class="ok">Сохранено</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="ok">Удалено</div><?php endif; ?>

<?php if ($errors): ?>
  <div class="err"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="box">
  <h2 style="margin:0 0 16px;font-size:16px">
    <?= $edit ? 'Редактировать новость #' . (int)$edit['id'] : 'Новая новость' ?>
  </h2>
  <form method="post" action="news.php">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : 0 ?>">

    <div class="row2">
      <div>
        <label for="title">Заголовок *</label>
        <input id="title" name="title" maxlength="200" required
               value="<?= htmlspecialchars($edit['title'] ?? '') ?>">
      </div>
      <div>
        <label for="status">Статус</label>
        <select id="status" name="status">
          <option value="draft"     <?= ($edit['status'] ?? 'draft') === 'draft'     ? 'selected' : '' ?>>Черновик</option>
          <option value="published" <?= ($edit['status'] ?? '')         === 'published' ? 'selected' : '' ?>>Опубликовано</option>
        </select>
      </div>
    </div>

    <label for="slug">Slug (URL) — можно оставить пустым, сгенерируется из заголовка</label>
    <input id="slug" name="slug" maxlength="220"
           value="<?= htmlspecialchars($edit['slug'] ?? '') ?>"
           placeholder="remont-podsvetki-tv">

    <label for="excerpt">Краткое описание (для карточки)</label>
    <input id="excerpt" name="excerpt" maxlength="500"
           value="<?= htmlspecialchars($edit['excerpt'] ?? '') ?>">

    <label for="cover">Ссылка на картинку (необязательно)</label>
    <input id="cover" name="cover" maxlength="300"
           value="<?= htmlspecialchars($edit['cover'] ?? '') ?>"
           placeholder="https://... или /uploads/news.jpg">

    <label for="body">Текст новости *</label>
    <textarea id="body" name="body" required><?= htmlspecialchars($edit['body'] ?? '') ?></textarea>

    <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap">
      <button class="btn btn-green" type="submit"><?= $edit ? 'Сохранить изменения' : 'Опубликовать' ?></button>
      <?php if ($edit): ?>
        <a class="btn" href="news.php">Отмена</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<h2 style="font-size:16px;margin:24px 0 0">Все новости (<?= count($rows) ?>)</h2>

<?php if (!$rows): ?>
  <p class="muted">Пока нет ни одной новости. Создайте первую выше.</p>
<?php else: ?>
<table>
  <thead>
    <tr><th>ID</th><th>Заголовок</th><th>Slug</th><th>Статус</th><th>Создано</th><th>Опубликовано</th><th></th></tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= (int)$r['id'] ?></td>
      <td><b><?= htmlspecialchars($r['title']) ?></b></td>
      <td class="muted"><code><?= htmlspecialchars($r['slug']) ?></code></td>
      <td><span class="badge <?= $r['status'] ?>"><?= $r['status'] === 'published' ? 'опубликовано' : 'черновик' ?></span></td>
      <td class="muted"><?= htmlspecialchars($r['created_at']) ?></td>
      <td class="muted"><?= $r['published_at'] ? htmlspecialchars($r['published_at']) : '—' ?></td>
      <td>
        <a class="btn" href="news.php?edit=<?= (int)$r['id'] ?>">Изменить</a>
        <form class="inline" method="post" action="news.php"
              onsubmit="return confirm('Удалить новость «<?= htmlspecialchars($r['title'], ENT_QUOTES) ?>»?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <button class="btn btn-red" type="submit">Удалить</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>