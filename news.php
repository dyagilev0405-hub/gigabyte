<?php
declare(strict_types=1);

$pdo = new PDO('mysql:host=127.0.0.1;dbname=gigabyte;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';

if ($slug !== '') {
    // одна новость
    $st = $pdo->prepare("SELECT * FROM news WHERE slug = ? AND status = 'published' LIMIT 1");
    $st->execute([$slug]);
    $item = $st->fetch();
    if (!$item) {
        http_response_code(404);
        $title = 'Новость не найдена';
        $content = '<p>Такой новости нет или она снята с публикации.</p><p><a href="/news.php">← Ко всем новостям</a></p>';
    } else {
        $title = $item['title'];
        $date  = date('d.m.Y', strtotime($item['published_at'] ?? $item['created_at']));
        $cover = $item['cover'] ? '<img src="' . htmlspecialchars($item['cover']) . '" alt="" style="max-width:100%;border-radius:16px;margin-bottom:20px">' : '';
        $content = '<article>'
            . '<h1>' . htmlspecialchars($item['title']) . '</h1>'
            . '<p style="color:#9FB0A7;margin-bottom:24px">' . $date . '</p>'
            . $cover
            . '<div style="white-space:pre-wrap;line-height:1.7">' . nl2br(htmlspecialchars($item['body'])) . '</div>'
            . '<p style="margin-top:32px"><a href="/news.php">← Ко всем новостям</a></p>'
            . '</article>';
    }
} else {
    // список
    $title = 'Новости — Гигабайт';
    $rows = $pdo->query("SELECT id,title,slug,excerpt,cover,published_at
                         FROM news WHERE status='published'
                         ORDER BY published_at DESC, id DESC")->fetchAll();

    if (!$rows) {
        $content = '<p style="color:#9FB0A7">Пока нет опубликованных новостей.</p>';
    } else {
        $cards = '';
        foreach ($rows as $r) {
            $date = date('d.m.Y', strtotime($r['published_at'] ?? 'now'));
            $img  = $r['cover'] ? '<img src="' . htmlspecialchars($r['cover']) . '" alt="" style="width:100%;height:180px;object-fit:cover;border-radius:14px 14px 0 0">' : '';
            $cards .= '
            <a class="news-card" href="/news.php?slug=' . urlencode($r['slug']) . '">
              ' . $img . '
              <div class="news-card-body">
                <div class="news-date">' . $date . '</div>
                <h2>' . htmlspecialchars($r['title']) . '</h2>
                <p>' . htmlspecialchars($r['excerpt']) . '</p>
                <span class="news-more">Читать →</span>
              </div>
            </a>';
        }
        $content = '<div class="news-grid">' . $cards . '</div>';
    }
}
?><!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?></title>
<style>
:root{--bg:#0B0F0D;--panel:#131C17;--line:#24332B;--ink:#E9F2EC;--dim:#9FB0A7;--green:#1AE58A;--citrus:#D8F24B}
*{box-sizing:border-box}
body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:var(--bg);color:var(--ink);margin:0;line-height:1.6}
.wrap{max-width:1000px;margin:0 auto;padding:32px 20px 80px}
header.top{position:sticky;top:0;background:rgba(11,15,13,.85);backdrop-filter:blur(12px);border-bottom:1px solid var(--line);z-index:5}
header.top .wrap{display:flex;align-items:center;justify-content:space-between;padding-top:16px;padding-bottom:16px}
header.top a{color:var(--ink);text-decoration:none;font-weight:700}
header.top nav a{margin-left:20px;font-size:14px;color:var(--dim);font-weight:600}
header.top nav a:hover{color:var(--green)}
h1{font-size:clamp(26px,4vw,38px);letter-spacing:-.02em;line-height:1.15;margin:0 0 20px}
.news-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;margin-top:28px}
.news-card{background:var(--panel);border:1px solid var(--line);border-radius:16px;overflow:hidden;color:inherit;text-decoration:none;display:flex;flex-direction:column;transition:border-color .2s,transform .2s}
.news-card:hover{border-color:rgba(26,229,138,.5);transform:translateY(-3px)}
.news-card-body{padding:18px 20px 22px;display:flex;flex-direction:column;flex:1}
.news-date{font-size:12.5px;color:var(--dim);margin-bottom:8px;text-transform:uppercase;letter-spacing:.08em;font-weight:700}
.news-card h2{font-size:18px;margin:0 0 8px;line-height:1.3}
.news-card p{color:var(--dim);font-size:14.5px;flex:1;margin:0 0 14px}
.news-more{color:var(--green);font-weight:700;font-size:14px}
.kicker{display:inline-block;font-size:12px;font-weight:800;letter-spacing:.22em;color:var(--green);text-transform:uppercase;margin-bottom:14px}
.kicker::before{content:"";display:inline-block;width:8px;height:8px;background:var(--green);border-radius:50%;margin-right:8px;box-shadow:0 0 0 4px rgba(26,229,138,.16)}
a.back{color:var(--green);text-decoration:none;font-weight:600}
a.back:hover{text-decoration:underline}
</style>
</head>
<body>

<header class="top">
  <div class="wrap">
    <a href="/">Гигабайт</a>
    <nav>
      <a href="/">Главная</a>
      <a href="/news.php">Новости</a>
      <a href="/#contacts">Контакты</a>
    </nav>
  </div>
</header>

<main class="wrap">
  <div class="kicker">Новости</div>
  <?= $content ?>
</main>
</body>
</html>