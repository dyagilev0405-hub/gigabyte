<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,      // локально HTTP
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
// Последние 3 опубликованные новости
$latestNews = [];
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gigabyte;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $latestNews = $pdo->query("SELECT title, slug, excerpt, cover, published_at
                                FROM news WHERE status='published'
                                ORDER BY published_at DESC, id DESC
                                LIMIT 3")->fetchAll();
} catch (Throwable $e) {
    error_log('news: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Ремонт ноутбуков, компьютеров и телевизоров в Якутске — Гигабайт</title>
  <meta name="description"
    content="Сервисный центр «Гигабайт»: ремонт ноутбуков, ПК, видеокарт, телевизоров и подсветки, MacBook, восстановление данных. Якутск и Владивосток. Выезд мастера, письменная гарантия до 6 месяцев.">
  <link rel="icon" type="image/jpeg" href="111.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Golos+Text:wght@400;500;600;700&family=Unbounded:wght@500;600;700&display=swap"
    rel="stylesheet">
  <style>
      .consent a {
          color: var(--green);
          text-decoration: underline;
          text-underline-offset: 2px;
      }

          .consent a:hover {
              color: var(--citrus);
          }

      .consent.err a {
          color: var(--danger);
      }

    :root {
      --ink: #E9F2EC;
      --ink-dim: #9FB0A7;
      --bg: #0B0F0D;
      --bg2: #101713;
      --panel: #131C17;
      --panel2: #182420;
      --line: #24332B;
      --line-soft: #1B2822;
      --green: #1AE58A;
      --green-d: #0FA968;
      --citrus: #D8F24B;
      --max: #B07BFF;
      --tg: #4DA3FF;
      --wa: #46E07C;
      --danger: #FF7A6B;
      --r: 4px;
      --r-lg: 26px;
      --wrap: 1200px;
      --h: "Unbounded", system-ui, sans-serif;
      --t: "Golos Text", system-ui, sans-serif;
    }

      /* ---------- Модальные окна документов ---------- */
      .modal {
          position: fixed;
          inset: 0;
          z-index: 100;
          display: none;
          align-items: center;
          justify-content: center;
          padding: 24px;
      }

          .modal.open {
              display: flex;
          }

      .modal-backdrop {
          position: absolute;
          inset: 0;
          background: rgba(3, 7, 5, .78);
          backdrop-filter: blur(6px);
          -webkit-backdrop-filter: blur(6px);
      }

      .modal-panel {
          position: relative;
          width: 100%;
          max-width: 860px;
          max-height: 88vh;
          background: var(--panel);
          border: 1px solid var(--line);
          border-radius: 20px;
          box-shadow: 0 40px 100px -30px rgba(0, 0, 0, .9);
          display: flex;
          flex-direction: column;
          overflow: hidden;
          animation: modalIn .22s ease;
      }

      @keyframes modalIn {
          from {
              opacity: 0;
              transform: translateY(16px) scale(.98);
          }

          to {
              opacity: 1;
              transform: none;
          }
      }

      .modal-close {
          position: absolute;
          top: 12px;
          right: 12px;
          z-index: 2;
          width: 40px;
          height: 40px;
          border-radius: 50%;
          border: 1px solid var(--line);
          background: var(--panel2);
          color: var(--ink);
          font-size: 22px;
          line-height: 1;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          transition: background .15s, border-color .15s, color .15s;
      }

          .modal-close:hover {
              border-color: var(--green);
              color: var(--green);
          }

      .modal-body {
          overflow-y: auto;
          padding: 40px 36px 36px;
          color: var(--ink);
          font-size: 14.5px;
          line-height: 1.65;
      }

          .modal-body::-webkit-scrollbar {
              width: 10px;
          }

          .modal-body::-webkit-scrollbar-thumb {
              background: var(--line);
              border-radius: 10px;
          }

      /* Документы внутри модалки */
      .doc h2 {
          font-size: 22px;
          text-transform: none;
          letter-spacing: -.02em;
          margin-bottom: 6px;
          color: var(--ink);
      }

      .doc .doc-meta {
          color: var(--ink-dim);
          font-size: 12.5px;
          margin-bottom: 22px;
          padding-bottom: 16px;
          border-bottom: 1px solid var(--line);
      }

      .doc h3 {
          font-family: var(--t);
          font-size: 15px;
          font-weight: 700;
          text-transform: none;
          letter-spacing: 0;
          margin: 22px 0 8px;
          color: var(--green);
      }

      .doc p, .doc li {
          color: var(--ink-dim);
          margin-bottom: 8px;
      }

      .doc ul, .doc ol {
          padding-left: 20px;
          margin-bottom: 10px;
      }

      .doc li {
          margin-bottom: 6px;
      }

      .doc b, .doc strong {
          color: var(--ink);
          font-weight: 600;
      }

      .doc a {
          color: var(--green);
          text-decoration: underline;
          text-underline-offset: 2px;
      }

      .doc table {
          width: 100%;
          border-collapse: collapse;
          margin: 10px 0 16px;
          font-size: 13.5px;
      }

      .doc th, .doc td {
          border: 1px solid var(--line);
          padding: 9px 12px;
          text-align: left;
          vertical-align: top;
          color: var(--ink-dim);
      }

      .doc th {
          background: var(--panel2);
          color: var(--ink);
          font-weight: 600;
      }

      .doc .doc-note {
          margin-top: 22px;
          padding: 14px 16px;
          border-radius: 12px;
          background: rgba(26, 229, 138, .06);
          border: 1px dashed rgba(26, 229, 138, .3);
          color: var(--ink-dim);
          font-size: 13px;
      }

      body.modal-open {
          overflow: hidden;
      }

      @media (max-width: 600px) {
          .modal {
              padding: 0;
          }

          .modal-panel {
              max-height: 100vh;
              height: 100vh;
              border-radius: 0;
          }

          .modal-body {
              padding: 32px 20px 28px;
          }
      }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    html {
      scroll-behavior: smooth;
      -webkit-text-size-adjust: 100%
    }

    section[id] {
      scroll-margin-top: 90px
    }

    body {
      font-family: var(--t);
      font-size: 16px;
      line-height: 1.6;
      color: var(--ink);
      background: var(--bg);
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden
    }

    img,
    video {
      max-width: 100%;
      display: block
    }

    a {
      color: inherit
    }

    button,
    input,
    select,
    textarea {
      font: inherit;
      color: inherit
    }

    .wrap {
      max-width: var(--wrap);
      margin: 0 auto;
      padding: 0 24px
    }

    h1,
    h2,
    h3 {
      font-family: var(--h);
      font-weight: 600;
      letter-spacing: -.03em;
      line-height: 1.02
    }

    h2 {
      font-size: clamp(28px, 4vw, 46px);
      margin-bottom: 16px;
      text-transform: uppercase
    }

    .lead {
      color: var(--ink-dim);
      font-size: 17px;
      max-width: 60ch
    }

    .kicker {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-size: 12px;
      font-weight: 700;
      color: var(--green);
      margin-bottom: 18px;
      text-transform: uppercase;
      letter-spacing: .22em;
      font-family: var(--t)
    }

    .kicker::before {
      content: "";
      width: 8px;
      height: 8px;
      background: var(--green);
      border-radius: 50%;
      box-shadow: 0 0 0 4px rgba(26, 229, 138, .16)
    }

    section {
      padding: 104px 0;
      position: relative
    }

    .ico {
      width: 20px;
      height: 20px;
      flex-shrink: 0;
      fill: none;
      stroke: currentColor;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round
    }

    /* ---------- Кнопки ---------- */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 16px 26px;
      border-radius: 100px;
      border: 1.5px solid transparent;
      font-weight: 600;
      font-size: 15px;
      text-decoration: none;
      cursor: pointer;
      transition: transform .2s, box-shadow .2s, background .2s, border-color .2s, color .2s;
      white-space: nowrap;
      letter-spacing: .01em
    }

    .btn:hover {
      transform: translateY(-2px)
    }

    .btn:active {
      transform: translateY(0)
    }

    .btn-green {
      background: var(--green);
      color: #06120C;
      box-shadow: 0 0 0 0 rgba(26, 229, 138, .5)
    }

    .btn-green:hover {
      box-shadow: 0 12px 34px -10px rgba(26, 229, 138, .7)
    }

    .btn-ink {
      background: var(--ink);
      color: #07100B
    }

    .btn-wa {
      background: var(--wa);
      color: #052210
    }

    .btn-tg {
      background: var(--tg);
      color: #041426
    }

    .btn-max {
      background: var(--max);
      color: #170A2B
    }

    .btn-line {
      background: transparent;
      border-color: var(--line);
      color: var(--ink)
    }

    .btn-line:hover {
      border-color: var(--green);
      color: var(--green)
    }

    .btn-ghost {
      background: rgba(255, 255, 255, .04);
      color: var(--ink);
      border-color: var(--line)
    }

    .btn-ghost:hover {
      background: rgba(26, 229, 138, .08);
      border-color: var(--green)
    }

    .btn:focus-visible,
    .chip:focus-visible,
    a:focus-visible,
    input:focus-visible,
    select:focus-visible,
    textarea:focus-visible,
    .tab:focus-visible {
      outline: 2px solid var(--citrus);
      outline-offset: 3px
    }

    /* ---------- Логотип ---------- */
    .logo {
      display: inline-flex;
      align-items: center;
      gap: 11px;
      text-decoration: none;
      font-family: var(--h);
      font-weight: 700;
      font-size: 21px;
      letter-spacing: -.04em;
      color: var(--ink)
    }

    .logo .mark {
      width: 38px;
      height: 38px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      overflow: hidden;
      border: 1px solid var(--line);
      box-shadow: 0 6px 18px -10px rgba(26, 229, 138, .8)
    }

    .logo .mark img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      padding: 3px
    }

    /* ---------- Шапка ---------- */
    header.top {
      position: sticky;
      top: 0;
      z-index: 60;
      background: rgba(11, 15, 13, .78);
      backdrop-filter: saturate(1.6) blur(18px);
      -webkit-backdrop-filter: saturate(1.6) blur(18px);
      border-bottom: 1px solid var(--line-soft)
    }

    .top .wrap {
      display: flex;
      align-items: center;
      gap: 20px;
      height: 76px
    }

    nav.menu {
      display: flex;
      gap: 2px;
      margin-left: auto
    }

    nav.menu a {
      text-decoration: none;
      font-size: 13.5px;
      font-weight: 600;
      color: var(--ink-dim);
      padding: 8px 13px;
      border-radius: 100px;
      transition: color .15s, background .15s;
      letter-spacing: .01em
    }

    nav.menu a:hover {
      color: var(--ink);
      background: var(--panel2)
    }

    /* ---------- Переключатель города ---------- */
    .city {
      display: inline-flex;
      border: 1.5px solid var(--line);
      border-radius: 100px;
      padding: 4px;
      background: var(--panel)
    }

    .city button {
      border: 0;
      background: none;
      padding: 7px 14px;
      border-radius: 100px;
      font-size: 12.5px;
      font-weight: 700;
      color: var(--ink-dim);
      cursor: pointer;
      outline: 2px solid transparent;
      outline-offset: 1px;
      transition: outline-color .15s, background .15s, color .15s
    }

    .city button:hover {
      color: var(--ink)
    }

    .city button[aria-pressed="true"] {
      background: var(--green);
      color: #06120C;
      outline: 2px solid var(--green);
      outline-offset: 1px
    }

    .top-phone {
      font-weight: 700;
      text-decoration: none;
      white-space: nowrap;
      font-size: 14.5px;
      color: var(--ink);
      font-family: var(--h);
      letter-spacing: -.02em
    }

    .top-phone:hover {
      color: var(--green)
    }

    .burger {
      display: none;
      margin-left: auto;
      width: 46px;
      height: 46px;
      border-radius: 50%;
      border: 1.5px solid var(--line);
      background: var(--panel);
      cursor: pointer;
      align-items: center;
      justify-content: center;
      color: var(--ink)
    }

    .drawer {
      display: none
    }

    /* ---------- HERO ---------- */
    .hero {
      position: relative;
      padding: 72px 0 96px;
      background:
        radial-gradient(760px 520px at 92% 4%, rgba(26, 229, 138, .16), transparent 62%),
        radial-gradient(620px 480px at -6% 96%, rgba(176, 123, 255, .13), transparent 60%),
        var(--bg);
      overflow: hidden;
      border-bottom: 1px solid var(--line-soft)
    }

    .hero::before {
      content: "";
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(255, 255, 255, .028) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, .028) 1px, transparent 1px);
      background-size: 64px 64px;
      mask-image: radial-gradient(ellipse 90% 70% at 50% 0%, #000 40%, transparent 90%);
      -webkit-mask-image: radial-gradient(ellipse 90% 70% at 50% 0%, #000 40%, transparent 90%);
      pointer-events: none
    }

    .hero .wrap {
      position: relative;
      display: grid;
      grid-template-columns: 1.08fr .92fr;
      gap: 64px;
      align-items: start
    }

    .hero .kicker {
      margin-bottom: 22px
    }

    .hero h1 {
      font-size: clamp(36px, 5.6vw, 68px);
      margin-bottom: 24px;
      letter-spacing: -.045em;
      text-transform: uppercase;
      line-height: .96
    }

    .hero h1 .thin {
      font-weight: 500;
      color: var(--ink-dim);
      display: block;
      font-size: .52em;
      letter-spacing: -.02em;
      margin-bottom: 14px;
      text-transform: none
    }

    .hero h1 em {
      font-style: normal;
      color: var(--green);
      position: relative;
      display: inline-block
    }

    .hero h1 em::after {
      content: "";
      position: absolute;
      left: 0;
      right: 0;
      bottom: .04em;
      height: .12em;
      background: var(--citrus);
      z-index: -1;
      transform: skewX(-14deg)
    }

    .hero .lead {
      font-size: 17.5px;
      margin-bottom: 30px;
      color: var(--ink-dim);
      max-width: 52ch
    }

    .facts {
      list-style: none;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px 20px;
      margin-bottom: 32px;
      max-width: 560px
    }

    .facts li {
      display: flex;
      gap: 11px;
      align-items: flex-start;
      font-weight: 500;
      color: var(--ink);
      font-size: 14.5px;
      line-height: 1.4
    }

    .facts .ico {
      color: var(--green);
      margin-top: 2px;
      width: 18px;
      height: 18px;
      stroke-width: 2.6;
      background: rgba(26, 229, 138, .1);
      border-radius: 50%;
      padding: 3px;
      box-sizing: content-box
    }

    .hero-cta {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 40px
    }

    .chips-l {
      font-size: 12px;
      font-weight: 700;
      color: var(--ink-dim);
      margin-bottom: 14px;
      text-transform: uppercase;
      letter-spacing: .16em
    }

    .chips {
      display: flex;
      flex-wrap: wrap;
      gap: 8px
    }

    .chip {
      border: 1.5px solid var(--line);
      background: var(--panel);
      padding: 9px 16px;
      border-radius: 100px;
      font-size: 13.5px;
      font-weight: 600;
      cursor: pointer;
      color: var(--ink-dim);
      transition: border-color .15s, background .15s, color .15s
    }

    .chip:hover {
      border-color: var(--green);
      color: var(--ink)
    }

    .chip.on {
      background: var(--green);
      border-color: var(--green);
      color: #06120C
    }

    /* ---------- Форма — «терминал» ---------- */
    .form {
      background: linear-gradient(180deg, var(--panel2), var(--panel));
      border: 1px solid var(--line);
      border-radius: var(--r-lg);
      padding: 32px 28px 28px;
      box-shadow: 0 40px 80px -50px rgba(0, 0, 0, .9), inset 0 1px 0 rgba(255, 255, 255, .04);
      color: var(--ink);
      position: relative;
      overflow: hidden
    }

    .form::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--green), var(--citrus), var(--max))
    }

    .form .fhead {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 6px
    }

    .form h2 {
      font-size: 20px;
      margin: 0;
      text-transform: none;
      letter-spacing: -.02em;
      color: var(--ink)
    }

    .form .tag {
      font-size: 10.5px;
      font-weight: 700;
      letter-spacing: .14em;
      text-transform: uppercase;
      color: var(--green);
      border: 1px dashed rgba(26, 229, 138, .4);
      padding: 4px 9px;
      border-radius: 100px;
      white-space: nowrap
    }

    .form .sub {
      color: var(--ink-dim);
      font-size: 13.5px;
      margin-bottom: 24px
    }

    .row2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px
    }

    .fg {
      margin-bottom: 16px;
      position: relative
    }

    .fg label,
    .fg .lbl {
      display: block;
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 7px;
      color: var(--ink-dim);
      text-transform: uppercase;
      letter-spacing: .1em
    }

    .fg input[type=text],
    .fg input[type=tel],
    .fg select,
    .fg textarea {
      width: 100%;
      padding: 14px 15px;
      border: 1.5px solid var(--line);
      border-radius: 10px;
      background: var(--bg2);
      font-size: 16px;
      transition: border-color .15s, background .15s;
      color: var(--ink)
    }

    .fg select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%239FB0A7' stroke-width='2.4' stroke-linecap='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 15px center;
      padding-right: 40px
    }

    .fg input::placeholder,
    .fg textarea::placeholder {
      color: #6E8279
    }

    .fg input:focus,
    .fg select:focus,
    .fg textarea:focus {
      border-color: var(--green);
      background: var(--bg2);
      outline: none;
      box-shadow: 0 0 0 4px rgba(26, 229, 138, .1)
    }

    .fg textarea {
      min-height: 84px;
      resize: vertical
    }

    .fg.err input,
    .fg.err textarea {
      border-color: var(--danger)
    }

    .fg .em {
      display: none;
      color: var(--danger);
      font-size: 12.5px;
      margin-top: 5px;
      font-weight: 600
    }

    .fg.err .em {
      display: block
    }

    .seg {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 4px;
      background: var(--bg2);
      border: 1.5px solid var(--line);
      border-radius: 10px;
      padding: 4px
    }

    .seg label {
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 9px;
      border-radius: 7px;
      font-weight: 600;
      font-size: 13.5px;
      cursor: pointer;
      color: var(--ink-dim);
      transition: background .15s, color .15s;
      text-transform: none;
      letter-spacing: 0
    }

    .seg input {
      position: absolute;
      opacity: 0;
      pointer-events: none
    }

    .seg label:has(input:checked) {
      background: var(--panel2);
      box-shadow: inset 0 0 0 1.5px var(--green);
      color: var(--ink)
    }

    .consent {
      display: flex;
      gap: 10px;
      align-items: flex-start;
      font-size: 12.5px;
      color: var(--ink-dim);
      margin: 4px 0 18px;
      line-height: 1.45
    }

    .consent input {
      width: 18px;
      height: 18px;
      margin-top: 1px;
      accent-color: var(--green);
      flex-shrink: 0
    }

    .consent.err {
      color: var(--danger)
    }

    .hp {
      position: absolute;
      left: -9999px;
      width: 1px;
      height: 1px;
      opacity: 0
    }

    .submit {
      width: 100%;
      padding: 17px;
      font-size: 15.5px;
      border-radius: 12px
    }

    .submit:disabled {
      opacity: .6;
      cursor: wait
    }

    .or {
      text-align: center;
      color: var(--ink-dim);
      font-size: 12px;
      margin: 18px 0 12px;
      text-transform: uppercase;
      letter-spacing: .14em;
      font-weight: 700
    }

    .msgr {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 8px
    }

    .msgr .btn {
      padding: 11px 8px;
      font-size: 13px;
      border-radius: 10px
    }

    [hidden] {
      display: none !important
    }

    .note {
      display: none;
      margin-top: 16px;
      padding: 14px 16px;
      border-radius: 12px;
      font-size: 14px;
      line-height: 1.5
    }

    .note.ok {
      display: block;
      background: rgba(26, 229, 138, .1);
      color: var(--green);
      border: 1px solid rgba(26, 229, 138, .3)
    }

    .note.bad {
      display: block;
      background: rgba(255, 122, 107, .1);
      color: var(--danger);
      border: 1px solid rgba(255, 122, 107, .3)
    }

    .note a {
      color: inherit;
      font-weight: 700
    }

    /* ---------- Маркиза ---------- */
    .marquee {
      background: var(--green);
      color: #06120C;
      overflow: hidden;
      padding: 14px 0;
      border-top: 1px solid rgba(0, 0, 0, .1);
      border-bottom: 1px solid rgba(0, 0, 0, .1)
    }

    .marquee-track {
      display: flex;
      gap: 40px;
      width: max-content;
      animation: slide 34s linear infinite;
      font-family: var(--h);
      font-weight: 600;
      font-size: 15px;
      text-transform: uppercase;
      letter-spacing: .04em
    }

    .marquee-track span {
      display: flex;
      align-items: center;
      gap: 40px
    }

    .marquee-track span::after {
      content: "✦";
      font-size: 12px
    }

    @keyframes slide {
      to {
        transform: translateX(-50%)
      }
    }

    /* ---------- Услуги — bento ---------- */
    .bento {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
      margin-top: 44px
    }

    .tile {
      position: relative;
      background: var(--panel);
      border: 1px solid var(--line-soft);
      border-radius: 18px;
      padding: 26px 24px;
      transition: border-color .25s, transform .25s, background .25s;
      overflow: hidden;
      min-height: 180px;
      display: flex;
      flex-direction: column
    }

    .tile::after {
      content: "";
      position: absolute;
      right: -40px;
      top: -40px;
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(26, 229, 138, .16), transparent 70%);
      opacity: 0;
      transition: opacity .3s
    }

    .tile:hover {
      border-color: rgba(26, 229, 138, .45);
      transform: translateY(-4px);
      background: var(--panel2)
    }

    .tile:hover::after {
      opacity: 1
    }

    .tile.big {
      grid-column: span 2;
      min-height: 200px
    }

    .tile .ic {
      width: 46px;
      height: 46px;
      border-radius: 12px;
      background: rgba(26, 229, 138, .08);
      border: 1px solid rgba(26, 229, 138, .2);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 18px;
      color: var(--green)
    }

    .tile .ic .ico {
      width: 24px;
      height: 24px;
      stroke-width: 2
    }

    .tile h3 {
      font-size: 16px;
      margin-bottom: 7px;
      letter-spacing: -.01em;
      text-transform: none;
      color: var(--ink)
    }

    .tile p {
      font-size: 14px;
      color: var(--ink-dim);
      line-height: 1.5;
      flex: 1
    }

    .tile .from {
      margin-top: 16px;
      font-weight: 700;
      font-size: 15px;
      color: var(--citrus);
      font-family: var(--h);
      letter-spacing: -.01em
    }

    .tile .from a {
      color: var(--green);
      text-decoration: none;
      border-bottom: 1px dashed rgba(26, 229, 138, .5)
    }

    /* ---------- Прайс ---------- */
    .prices {
      background: var(--bg2);
      border-top: 1px solid var(--line-soft);
      border-bottom: 1px solid var(--line-soft)
    }

    .tabs {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin: 38px 0 24px
    }

    .tab {
      border: 1.5px solid var(--line);
      background: transparent;
      padding: 10px 20px;
      border-radius: 100px;
      font-weight: 600;
      font-size: 13.5px;
      cursor: pointer;
      transition: background .15s, border-color .15s, color .15s;
      color: var(--ink-dim)
    }

    .tab:hover {
      border-color: var(--green);
      color: var(--ink)
    }

    .tab[aria-selected="true"] {
      background: var(--green);
      border-color: var(--green);
      color: #06120C
    }

    .plist {
      display: none;
      border: 1px solid var(--line);
      border-radius: 16px;
      overflow: hidden;
      background: var(--panel)
    }

    .plist.on {
      display: block;
      animation: fade .3s ease
    }

    @keyframes fade {
      from {
        opacity: 0;
        transform: translateY(8px)
      }

      to {
        opacity: 1;
        transform: none
      }
    }

    .prow {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      gap: 18px;
      padding: 17px 24px;
      border-bottom: 1px dashed var(--line);
      font-size: 15px;
      transition: background .15s
    }

    .prow:last-child {
      border-bottom: 0
    }

    .prow:hover {
      background: rgba(26, 229, 138, .05)
    }

    .prow b {
      white-space: nowrap;
      font-weight: 700;
      font-family: var(--h);
      color: var(--green);
      font-size: 14.5px;
      letter-spacing: -.01em
    }

    .pnote {
      display: flex;
      gap: 12px;
      margin-top: 22px;
      color: var(--ink-dim);
      font-size: 13.5px;
      max-width: 84ch;
      padding: 16px 18px;
      background: rgba(26, 229, 138, .05);
      border: 1px dashed rgba(26, 229, 138, .3);
      border-radius: 14px
    }

    .pnote .ico {
      color: var(--green);
      margin-top: 1px
    }

    /* ---------- Шаги — таймлайн ---------- */
    .steps {
      margin-top: 48px;
      display: grid;
      gap: 0;
      border-left: 1px dashed var(--line);
      padding-left: 0;
      counter-reset: s
    }

    .step {
      position: relative;
      padding: 0 0 34px 40px;
      display: grid;
      grid-template-columns: 150px 1fr;
      gap: 24px;
      align-items: start
    }

    .step:last-child {
      padding-bottom: 0
    }

    .step::before {
      counter-increment: s;
      content: "0" counter(s);
      position: absolute;
      left: -21px;
      top: -2px;
      width: 41px;
      height: 41px;
      border-radius: 50%;
      background: var(--bg);
      border: 1.5px solid var(--green);
      color: var(--green);
      font-family: var(--h);
      font-size: 12.5px;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 0 0 6px var(--bg)
    }

    .step h3 {
      font-size: 16px;
      margin-bottom: 0;
      text-transform: none;
      color: var(--ink);
      padding-top: 7px
    }

    .step p {
      font-size: 14.5px;
      color: var(--ink-dim);
      line-height: 1.55;
      padding-top: 8px
    }

    /* ---------- Гарантия ---------- */
    .warranty {
      background: var(--bg2);
      border-top: 1px solid var(--line-soft);
      border-bottom: 1px solid var(--line-soft);
      padding: 96px 0
    }

    .wgrid {
      display: grid;
      grid-template-columns: 1fr 1.15fr;
      gap: 56px;
      align-items: start
    }

    .wtable {
      display: grid;
      border-top: 1px solid var(--line)
    }

    .wtable div {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      gap: 24px;
      padding: 16px 0;
      border-bottom: 1px solid var(--line);
      font-size: 14.5px
    }

    .wtable div b {
      font-family: var(--h);
      font-weight: 600;
      font-size: 14px;
      text-align: right;
      white-space: nowrap;
      color: var(--citrus);
      letter-spacing: -.01em
    }

    .wtable div span {
      color: var(--ink-dim)
    }

    /* ---------- Галерея ---------- */
    .gallery {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      margin-top: 40px
    }

    .gallery figure {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      background: var(--panel);
      aspect-ratio: 4/3;
      border: 1px solid var(--line-soft)
    }

    .gallery img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform .5s, filter .3s;
      filter: saturate(.9) contrast(1.05)
    }

    .gallery figure:hover img {
      transform: scale(1.07);
      filter: saturate(1.15) contrast(1.05)
    }

    .gallery figcaption {
      position: absolute;
      left: 10px;
      bottom: 10px;
      background: rgba(6, 18, 12, .82);
      color: var(--ink);
      font-size: 12px;
      font-weight: 600;
      padding: 6px 12px;
      border-radius: 100px;
      backdrop-filter: blur(6px);
      border: 1px solid rgba(26, 229, 138, .25)
    }

    /* ---------- Отзывы ---------- */
    .reviews {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
      margin-top: 40px
    }

    .rv {
      background: var(--panel);
      border: 1px solid var(--line-soft);
      border-radius: 18px;
      padding: 28px;
      display: flex;
      flex-direction: column;
      transition: border-color .25s, transform .25s;
      position: relative
    }

    .rv:hover {
      border-color: rgba(26, 229, 138, .4);
      transform: translateY(-3px)
    }

    .rv .stars {
      color: var(--citrus);
      letter-spacing: 3px;
      font-size: 14px;
      margin-bottom: 14px
    }

    .rv p {
      font-size: 15px;
      flex: 1;
      line-height: 1.6;
      color: var(--ink)
    }

    .rv .who {
      margin-top: 18px;
      font-size: 13px;
      color: var(--ink-dim);
      border-top: 1px dashed var(--line);
      padding-top: 14px
    }

    .rv .who b {
      color: var(--green);
      font-weight: 700
    }

    .links2gis {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 28px
    }

    /* ---------- B2B ---------- */
    .b2b {
      background: linear-gradient(160deg, var(--bg2), var(--bg));
      color: var(--ink);
      position: relative;
      overflow: hidden;
      border-top: 1px solid var(--line-soft)
    }

    .b2b::before {
      content: "";
      position: absolute;
      top: -30%;
      right: -8%;
      width: 620px;
      height: 620px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(216, 242, 75, .1), transparent 66%);
      pointer-events: none
    }

    .b2b .wrap {
      position: relative;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 56px;
      align-items: center
    }

    .b2b ul {
      list-style: none;
      display: grid;
      gap: 14px;
      margin: 28px 0 32px
    }

    .b2b li {
      display: flex;
      gap: 12px;
      color: var(--ink);
      font-size: 15px
    }

    .b2b li .ico {
      color: var(--green);
      margin-top: 2px;
      stroke-width: 2.4
    }

    .b2b .ph {
      border-radius: 22px;
      overflow: hidden;
      aspect-ratio: 4/3;
      background: var(--panel);
      border: 1px solid var(--line)
    }

    .b2b .ph img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: .92;
      filter: saturate(.85) contrast(1.05)
    }

    /* ---------- Reach ---------- */
    .reach {
      background: var(--green);
      color: #06120C
    }

    .reach .kicker {
      color: #06120C
    }

    .reach .kicker::before {
      background: #06120C;
      box-shadow: 0 0 0 4px rgba(6, 18, 12, .15)
    }

    .reach h2 {
      color: #06120C
    }

    .reach .lead {
      color: rgba(6, 18, 12, .68);
      font-weight: 500
    }

    .reach-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
      margin-top: 34px
    }

    .reach-grid .btn {
      padding: 22px 16px;
      font-size: 15px;
      border-radius: 16px;
      justify-content: center;
      background: #06120C;
      color: var(--ink);
      border-color: transparent
    }

    .reach-grid .btn:hover {
      background: #0D1F16
    }

    .reach-grid .btn-call {
      background: var(--bg2);
      color: var(--ink)
    }

    .reach-note {
      margin-top: 20px;
      font-size: 14px;
      color: rgba(6, 18, 12, .6);
      font-weight: 600
    }

    /* ---------- Контакты ---------- */
    .contacts {
      background: var(--bg);
      border-top: 1px solid var(--line-soft)
    }

    .cgrid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      margin-top: 40px
    }

    .ccard {
      border: 1px solid var(--line);
      border-radius: 22px;
      padding: 32px;
      background: var(--panel);
      transition: border-color .25s, transform .25s, box-shadow .25s
    }

    .ccard:hover {
      transform: translateY(-2px)
    }

    .ccard.active {
      border-color: var(--green);
      box-shadow: 0 0 0 1px var(--green), 0 30px 60px -40px rgba(26, 229, 138, .5)
    }

    .ccard h3 {
      font-size: 19px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 11px;
      text-transform: none
    }

    .ccard h3 .ico {
      color: var(--green)
    }

    .ccard dl {
      display: grid;
      grid-template-columns: 100px 1fr;
      gap: 12px 16px;
      font-size: 15px;
      margin-bottom: 22px
    }

    .ccard dt {
      color: var(--ink-dim);
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: .08em;
      font-weight: 600;
      padding-top: 2px
    }

    .ccard dd a {
      font-weight: 700;
      text-decoration: none;
      color: var(--ink)
    }

    .ccard dd a:hover {
      color: var(--green)
    }

    .ccard .acts {
      display: flex;
      flex-wrap: wrap;
      gap: 8px
    }

    .ccard .acts .btn {
      padding: 11px 18px;
      font-size: 13.5px
    }

    /* ---------- Footer ---------- */
    footer {
      background: var(--bg2);
      color: var(--ink-dim);
      padding: 60px 0 34px;
      font-size: 14px;
      border-top: 1px solid var(--line-soft)
    }

    footer .wrap {
      display: grid;
      grid-template-columns: 1.3fr 1fr 1fr;
      gap: 36px
    }

    footer a {
      color: var(--ink);
      text-decoration: none;
      transition: color .15s
    }

    footer a:hover {
      color: var(--green)
    }

    footer ul {
      list-style: none;
      display: grid;
      gap: 11px
    }

    footer .legal {
      grid-column: 1/-1;
      border-top: 1px solid var(--line);
      padding-top: 24px;
      font-size: 12.5px;
      color: #6E8279;
      line-height: 1.6
    }

    /* ---------- FAB ---------- */
    .fab {
      display: none
    }

    /* ---------- Адаптив ---------- */
    @media(max-width:1100px) {
      nav.menu {
        display: none
      }

      .burger {
        display: inline-flex
      }

      .top .city {
        margin-left: auto
      }

      .burger {
        margin-left: 0
      }

      .bento {
        grid-template-columns: repeat(2, 1fr)
      }

      .tile.big {
        grid-column: span 2
      }

      .gallery {
        grid-template-columns: repeat(2, 1fr)
      }

      .drawer {
        position: fixed;
        inset: 76px 0 auto 0;
        background: var(--bg2);
        border-bottom: 1px solid var(--line);
        padding: 12px 24px 24px;
        z-index: 59;
        box-shadow: 0 30px 50px -20px rgba(0, 0, 0, .8)
      }

      .drawer.open {
        display: block
      }

      .drawer a {
        display: block;
        padding: 15px 4px;
        border-bottom: 1px solid var(--line-soft);
        text-decoration: none;
        font-weight: 600;
        color: var(--ink)
      }
    }

    @media(max-width:880px) {

      .hero .wrap,
      .wgrid,
      .b2b .wrap,
      .cgrid {
        grid-template-columns: 1fr
      }

      .hero {
        padding: 52px 0 68px
      }

      .hero .wrap {
        gap: 42px
      }

      .facts {
        grid-template-columns: 1fr 1fr
      }

      .reviews {
        grid-template-columns: 1fr 1fr
      }

      .top-phone {
        display: none
      }

      section {
        padding: 76px 0
      }

      .step {
        grid-template-columns: 1fr;
        gap: 4px
      }

      .step h3 {
        padding-top: 4px
      }

      .step p {
        padding-top: 0
      }
    }

    @media(max-width:600px) {
      .wrap {
        padding: 0 16px
      }

      .top .wrap {
        height: 66px;
        gap: 10px
      }

      .drawer {
        top: 66px
      }

      .city button {
        padding: 6px 10px;
        font-size: 12px
      }

      .hero h1 {
        font-size: clamp(30px, 9vw, 44px)
      }

      .hero-cta .btn {
        flex: 1 1 100%
      }

      .facts {
        grid-template-columns: 1fr
      }

      .row2 {
        grid-template-columns: 1fr
      }

      .form {
        padding: 24px 20px
      }

      .bento,
      .reviews {
        grid-template-columns: 1fr
      }

      .tile.big {
        grid-column: span 1
      }

      .gallery {
        grid-template-columns: 1fr 1fr;
        gap: 8px
      }

      .prow {
        padding: 14px 16px;
        font-size: 14px
      }

      .ccard dl {
        grid-template-columns: 1fr;
        gap: 2px
      }

      .ccard dd {
        margin-bottom: 10px
      }

      .ccard {
        padding: 24px
      }

      footer .wrap {
        grid-template-columns: 1fr
      }

      .reach-grid {
        grid-template-columns: 1fr 1fr
      }

      .fab {
        display: block;
        position: fixed;
        right: 16px;
        bottom: 16px;
        z-index: 60
      }

      .fab-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 0;
        background: var(--green);
        color: #06120C;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 14px 30px -8px rgba(26, 229, 138, .7);
        cursor: pointer;
        transition: transform .18s
      }

      .fab-btn:hover {
        transform: scale(1.06)
      }

      .fab-btn .ico {
        width: 26px;
        height: 26px
      }
    }

    @media(prefers-reduced-motion:reduce) {
      * {
        transition: none !important;
        scroll-behavior: auto !important;
        animation: none !important
      }

      .marquee-track {
        animation: none !important
      }
    }
  </style>
</head>

<body>

    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <symbol id="i-phone" viewBox="0 0 24 24">
            <path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2" />
        </symbol>
        <symbol id="i-wa" viewBox="0 0 24 24">
            <path fill="currentColor" stroke="none"
                  d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2m0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2m4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .1-1.3c0-.1-.2-.2-.5-.3" />
        </symbol>
        <symbol id="i-tg" viewBox="0 0 24 24">
            <path fill="currentColor" stroke="none"
                  d="M21.4 4.3 18.3 19c-.2 1-.8 1.3-1.7.8l-4.6-3.4-2.2 2.1c-.3.3-.5.5-1 .5l.3-4.7 8.6-7.8c.4-.3-.1-.5-.6-.2L6.5 13 2 11.6c-1-.3-1-1 .2-1.5L20.2 3.2c.8-.3 1.6.2 1.2 1.1" />
        </symbol>
        <symbol id="i-max" viewBox="0 0 24 24">
            <path fill="currentColor" stroke="none"
                  d="M12 2.5c5.5 0 9.5 3.9 9.5 9.2s-4 9.3-9.6 9.3c-1.4 0-2.6-.3-3.7-.8L4 21.5l1-3.9A9 9 0 0 1 2.5 11.7C2.5 6.4 6.5 2.5 12 2.5m0 4.2c-2.8 0-4.9 2.1-4.9 5s2 5 4.9 5 5-2.1 5-5-2.2-5-5-5" />
        </symbol>
        <symbol id="i-check" viewBox="0 0 24 24">
            <path d="m5 12.5 4.5 4.5L19 7.5" />
        </symbol>
        <symbol id="i-pin" viewBox="0 0 24 24">
            <path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z" />
            <circle cx="12" cy="9.5" r="2.5" />
        </symbol>
        <symbol id="i-info" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 11v5M12 8h.01" />
        </symbol>
        <symbol id="i-laptop" viewBox="0 0 24 24">
            <rect x="4" y="5" width="16" height="11" rx="1.5" />
            <path d="M2 19h20" />
        </symbol>
        <symbol id="i-pc" viewBox="0 0 24 24">
            <rect x="3" y="4" width="13" height="10" rx="1.5" />
            <path d="M7 18h5M9.5 14v4M19 4v14" />
        </symbol>
        <symbol id="i-gpu" viewBox="0 0 24 24">
            <rect x="2" y="7" width="20" height="10" rx="1.5" />
            <circle cx="9" cy="12" r="2.6" />
            <circle cx="16" cy="12" r="2.6" />
            <path d="M4 17v3" />
        </symbol>
        <symbol id="i-tv" viewBox="0 0 24 24">
            <rect x="2" y="4" width="20" height="13" rx="1.5" />
            <path d="M8 21h8M12 17v4" />
        </symbol>
        <symbol id="i-disk" viewBox="0 0 24 24">
            <rect x="4" y="3" width="16" height="18" rx="2" />
            <circle cx="12" cy="11" r="4" />
            <path d="M12 11h.01M8 18h2" />
        </symbol>
        <symbol id="i-apple" viewBox="0 0 24 24">
            <path d="M15.5 3.5c0 1.6-1.3 3-2.8 3 0-1.6 1.3-3 2.8-3zM17 12.3c0-2 1.6-2.9 1.7-3a3.8 3.8 0 0 0-3-1.6c-1.3-.1-2.5.8-3.1.8s-1.6-.7-2.7-.7a4 4 0 0 0-3.3 2c-1.4 2.5-.4 6.1 1 8.1.7 1 1.5 2 2.5 2s1.4-.6 2.6-.6 1.5.6 2.6.6 1.8-1 2.4-2a8 8 0 0 0 1.1-2.3A3.4 3.4 0 0 1 17 12.3z" />
        </symbol>
        <symbol id="i-pad" viewBox="0 0 24 24">
            <path d="M7 8h10a5 5 0 0 1 4.9 6l-.6 3a2.5 2.5 0 0 1-4.4 1L15 16H9l-1.9 2a2.5 2.5 0 0 1-4.4-1l-.6-3A5 5 0 0 1 7 8z" />
            <path d="M7 11v3M5.5 12.5h3M16 11.5h.01M18 13.5h.01" />
        </symbol>
        <symbol id="i-office" viewBox="0 0 24 24">
            <rect x="4" y="3" width="16" height="18" rx="1.5" />
            <path d="M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1M10 21v-3h4v3" />
        </symbol>
        <symbol id="i-chat" viewBox="0 0 24 24">
            <path d="M4 5h16v11H9l-5 4z" />
            <path d="M8 9.5h8M8 12.5h5" />
        </symbol>
        <symbol id="i-menu" viewBox="0 0 24 24">
            <path d="M4 7h16M4 12h16M4 17h16" />
        </symbol>
        <symbol id="i-ext" viewBox="0 0 24 24">
            <path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" />
        </symbol>
    </svg>

    <header class="top">
        <div class="wrap">
            <a class="logo" href="/" aria-label="Гигабайт — на главную">
                <span class="mark">
                    <img src="111.png" alt="Логотип Гигабайт" loading="eager" width="38" height="38">
                </span>Гигабайт
            </a>
              <nav class="menu" aria-label="Разделы">
                <a href="#services">Услуги</a><a href="#prices">Цены</a><a href="#warranty">Гарантия</a><a href="#works">Работы</a><a href="#reviews">Отзывы</a><a href="/news.php">Новости</a><a href="#b2b">Организациям</a><a href="#contacts">Контакты</a>
            </nav>
            <div class="city" role="group" aria-label="Город">
                <button type="button" data-city="yakutsk" aria-pressed="true">Якутск</button>
                <button type="button" data-city="vladivostok" aria-pressed="false">Владивосток</button>
            </div>
            <a class="top-phone js-tel" href="tel:+79148212918"><span class="js-tel-txt">+7 (914) 821-29-18</span></a>
            <button class="burger" type="button" aria-label="Меню" aria-expanded="false" id="burger">
                <svg class="ico">
                    <use href="#i-menu"></use>
                </svg>
            </button>
        </div>
                <div class="drawer" id="drawer">
            <a href="#services">Услуги</a><a href="#prices">Цены</a><a href="#warranty">Гарантия</a><a href="#works">Работы</a><a href="#reviews">Отзывы</a><a href="/news.php">Новости</a><a href="#b2b">Организациям</a><a href="#contacts">Контакты</a>
        </div>
    </header>

    <main>
        <section class="hero" id="top">
            <div class="wrap">
                <div>
                    <div class="kicker">Сервисный центр · <span class="js-city-name">Якутск</span></div>
                    <h1>
                        <span class="thin">Ремонтируем сложное.</span>Ноутбуки, ПК и ТВ <em class="js-city-in">в Якутске</em>
                    </h1>
                    <p class="lead">
                        Чиним то, за что другие не берутся: пайка на плате, подсветка ТВ, видеокарты, MacBook.
                        Сначала разбираемся, потом называем цену.
                    </p>
                    <ul class="facts">
                        <li>
                            <svg class="ico">
                                <use href="#i-check" />
                            </svg>Диагностика может войти в стоимость ремонта
                        </li>
                        <li>
                            <svg class="ico">
                                <use href="#i-check" />
                            </svg>Письменная гарантия до 6 месяцев
                        </li>
                        <li>
                            <svg class="ico">
                                <use href="#i-check" />
                            </svg><span class="js-visit">Выезд мастера с диагностикой — от 2 000 ₽</span>
                        </li>
                        <li>
                            <svg class="ico">
                                <use href="#i-check" />
                            </svg>Цены «от», без скрытых доплат
                        </li>
                    </ul>
                    <div class="hero-cta">
                        <a class="btn btn-wa js-wa" href="https://wa.me/79148212918" target="_blank" rel="noopener">
                            <svg class="ico">
                                <use href="#i-wa" />
                            </svg>Написать в WhatsApp
                        </a>
                        <a class="btn btn-ghost js-tel" href="tel:+79148212918">
                            <svg class="ico">
                                <use href="#i-phone" />
                            </svg><span class="js-tel-txt">+7 (914) 821-29-18</span>
                        </a>
                    </div>
                    <div class="chips-l">Что случилось? Нажмите — заполним заявку</div>
                    <div class="chips" id="chips">
                        <button class="chip" type="button" data-dev="laptop" data-fault="Не включается">
                            Ноутбук не
                            включается
                        </button>
                        <button class="chip" type="button" data-dev="laptop" data-fault="Греется, шумит, тормозит">
                            Греется и
                            шумит
                        </button>
                        <button class="chip" type="button" data-dev="laptop" data-fault="Разбит или не работает экран">
                            Разбит
                            экран
                        </button>
                        <button class="chip" type="button" data-dev="pc" data-fault="Не включается или не загружается">
                            ПК не
                            включается
                        </button>
                        <button class="chip" type="button" data-dev="tv" data-fault="Есть звук, нет изображения">
                            ТВ без
                            картинки
                        </button>
                        <button class="chip" type="button" data-dev="gpu" data-fault="Артефакты, полосы">Артефакты GPU</button>
                        <button class="chip" type="button" data-dev="apple" data-fault="MacBook / iMac">MacBook / iMac</button>
                        <button class="chip" type="button" data-dev="console" data-fault="PS4 / PS5">PS4 / PS5</button>
                    </div>
                </div>
                <form class="form" id="leadForm" novalidate>
                  <input type="hidden" name="csrf" id="f-csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">
                    <div class="fhead">
                        <h2>Заявка на ремонт</h2>
                        <span class="tag">Онлайн</span>
                    </div>
                    <p class="sub">Свяжемся в рабочее время удобным для вас способом.</p>
                    <div class="fg">
                        <span class="lbl">Город</span>
                        <div class="seg" id="cityRadios">
                            <label><input type="radio" name="city" value="yakutsk" checked><span>Якутск</span></label>
                            <label><input type="radio" name="city" value="vladivostok"><span>Владивосток</span></label>
                        </div>
                    </div>
                    <div class="row2">
                        <div class="fg" id="fg-name">
                            <label for="f-name">Имя</label><input id="f-name" name="name" type="text"
                                                                  autocomplete="name" maxlength="60" placeholder="Как к вам обращаться">
                            <div class="em">Укажите имя</div>
                        </div>
                        <div class="fg" id="fg-phone">
                            <label for="f-phone">Телефон</label><input id="f-phone" name="phone"
                                                                       type="tel" inputmode="tel" autocomplete="tel" maxlength="20" placeholder="+7 900 000-00-00">
                            <div class="em">Нужен номер из 10–11 цифр</div>
                        </div>
                    </div>
                    <div class="row2">
                        <div class="fg">
                            <label for="f-device">Что ремонтируем</label>
                            <select id="f-device" name="device">
                                <option value="laptop">Ноутбук</option>
                                <option value="pc">Компьютер (ПК)</option>
                                <option value="aio">Моноблок</option>
                                <option value="tv">Телевизор</option>
                                <option value="gpu">Видеокарта</option>
                                <option value="apple">MacBook / iMac</option>
                                <option value="console">Игровая приставка PS4 / PS5</option>
                                <option value="other">Другое</option>
                            </select>
                        </div>
                        <div class="fg">
                            <span class="lbl">Как удобнее</span>
                            <div class="seg">
                                <label><input type="radio" name="visit" value="office" checked><span>В сервис</span></label>
                                <label><input type="radio" name="visit" value="onsite"><span>Выезд</span></label>
                            </div>
                        </div>
                    </div>
                    <div class="fg" id="fg-address" hidden>
                        <label for="f-address">Адрес <small>(улица, дом)</small></label><input id="f-address" name="address" type="text" autocomplete="street-address" maxlength="200"
                                                                                               placeholder="Например: Ленина 10">
                    </div>
                    <div class="fg">
                        <label for="f-fault">Что случилось <small>(необязательно)</small></label><textarea id="f-fault" name="fault" maxlength="600" placeholder="Модель и что не так…"></textarea>
                    </div>
                    <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <label class="consent" id="consentWrap">
                        <input type="checkbox" id="f-consent" name="consent" value="1" required>
                        <span>
                            Я согласен на обработку персональных данных в соответствии с
                            <a href="#privacy" class="js-open-policy" data-doc="privacy">Политикой конфиденциальности</a>
                            и принимаю
                            <a href="#terms" class="js-open-policy" data-doc="terms">Пользовательское соглашение</a>
                        </span>
                    </label>
                    <div class="em" id="consentErr" style="display:none;color:var(--danger);font-size:12.5px;margin:-10px 0 14px;font-weight:600">
                        Для отправки заявки необходимо согласие на обработку персональных данных
                    </div>
                    <button class="btn btn-green submit" id="submitBtn" type="submit">Отправить заявку</button>
                    <div class="note" id="formNote" role="status" aria-live="polite"></div>
                    <div class="or">или напишите нам сами</div>
                    <div class="msgr">
                        <a class="btn btn-wa js-wa" href="https://wa.me/79148212918" target="_blank" rel="noopener">
                            <svg class="ico">
                                <use href="#i-wa" />
                            </svg>WhatsApp
                        </a>
                        <a class="btn btn-tg" href="https://t.me/gigabyte_remont14" target="_blank" rel="noopener">
                            <svg class="ico">
                                <use href="#i-tg" />
                            </svg>Telegram
                        </a>
                        <a class="btn btn-max" href="https://max.ru" target="_blank" rel="noopener">
                            <svg class="ico">
                                <use href="#i-max" />
                            </svg>MAX
                        </a>
                    </div>
                </form>
            </div>
        </section>

        <div class="marquee" aria-hidden="true">
            <div class="marquee-track">
                <span>Пайка плат</span><span>Подсветка ТВ</span><span>Ремонт видеокарт</span><span>MacBook</span><span>
                    PS4 /
                    PS5
                </span><span>Восстановление данных</span>
                <span>Пайка плат</span><span>Подсветка ТВ</span><span>Ремонт видеокарт</span><span>MacBook</span><span>
                    PS4 /
                    PS5
                </span><span>Восстановление данных</span>
            </div>
        </div>

        <section id="services">
            <div class="wrap">
                <div class="kicker">Что ремонтируем</div>
                <h2>Компьютерная техника и телевизоры</h2>
                <p class="lead">Ремонтируем на уровне платы: меняем микросхемы и видеочипы, восстанавливаем дорожки.</p>
                <div class="bento">
                    <article class="tile big">
                        <div class="ic">
                            <svg class="ico">
                                <use href="#i-laptop" />
                            </svg>
                        </div>
                        <h3>Ноутбуки</h3>
                        <p>Не включается, греется, не заряжается, разбита матрица.</p>
                        <div class="from">от 700 ₽</div>
                    </article>
                    <article class="tile">
                        <div class="ic">
                            <svg class="ico">
                                <use href="#i-pc" />
                            </svg>
                        </div>
                        <h3>Компьютеры и сборка ПК</h3>
                        <p>Ремонт, чистка, апгрейд. Подбор комплектующих.</p>
                        <div class="from">от 1 000 ₽</div>
                    </article>
                    <article class="tile">
                        <div class="ic">
                            <svg class="ico">
                                <use href="#i-gpu" />
                            </svg>
                        </div>
                        <h3>Видеокарты</h3>
                        <p>Артефакты, полосы, нет изображения. Замена видеочипа.</p>
                        <div class="from">от 3 300 ₽</div>
                    </article>
                    <article class="tile">
                        <div class="ic">
                            <svg class="ico">
                                <use href="#i-tv" />
                            </svg>
                        </div>
                        <h3>Телевизоры и подсветка</h3>
                        <p>Нет картинки при звуке, блок питания, T-Con.</p>
                        <div class="from">от 900 ₽</div>
                    </article>
                    <article class="tile">
                        <div class="ic">
                            <svg class="ico">
                                <use href="#i-disk" />
                            </svg>
                        </div>
                        <h3>Восстановление данных</h3>
                        <p>Удалённые файлы, HDD/SSD.</p>
                        <div class="from">от 3 300 ₽</div>
                    </article>
                    <article class="tile">
                        <div class="ic">
                            <svg class="ico">
                                <use href="#i-apple" />
                            </svg>
                        </div>
                        <h3>MacBook и iMac</h3>
                        <p>Ремонт платы, аккумулятор, матрица, после залития.</p>
                        <div class="from">от 2 900 ₽</div>
                    </article>
                    <article class="tile">
                        <div class="ic">
                            <svg class="ico">
                                <use href="#i-pad" />
                            </svg>
                        </div>
                        <h3>Игровые приставки</h3>
                        <p>PS4 и PS5: перегрев, не включается, нет изображения.</p>
                        <div class="from">от 1 990 ₽</div>
                    </article>
                    <article class="tile">
                        <div class="ic">
                            <svg class="ico">
                                <use href="#i-office" />
                            </svg>
                        </div>
                        <h3>Обслуживание организаций</h3>
                        <p>Рабочие места, сеть, принтеры по договору.</p>
                        <div class="from"><a href="#b2b">Подробнее →</a></div>
                    </article>
                </div>
            </div>
        </section>

        <section class="prices" id="prices">
            <div class="wrap">
                <div class="kicker">Прайс</div>
                <h2>Цены на работу</h2>
                <p class="lead">Цены за работу, запчасти — отдельно. Точную стоимость называем после диагностики.</p>
                <div class="tabs" role="tablist">
                    <button class="tab" role="tab" aria-selected="true" data-tab="p-diag">Диагностика и выезд</button>
                    <button class="tab" role="tab" aria-selected="false" data-tab="p-laptop">Ноутбуки и ПК</button>
                    <button class="tab" role="tab" aria-selected="false" data-tab="p-tv">Телевизоры</button>
                    <button class="tab" role="tab" aria-selected="false" data-tab="p-soft">Windows и данные</button>
                    <button class="tab" role="tab" aria-selected="false" data-tab="p-mac">MacBook</button>
                </div>
                <div class="plist on" id="p-diag">
                    <div class="prow"><span>Программная диагностика</span><b>1 500 ₽</b></div>
                    <div class="prow"><span>Сложная (аппаратная) диагностика</span><b>2 000 ₽</b></div>
                    <div class="prow"><span>Выезд мастера с диагностикой</span><b>от 2 000 ₽</b></div>
                </div>
                <div class="plist" id="p-laptop">
                    <div class="prow"><span>Чистка ноутбука от пыли</span><b>от 1 400 ₽</b></div>
                    <div class="prow"><span>Замена матрицы ноутбука</span><b>от 2 500 ₽</b></div>
                    <div class="prow"><span>Замена клавиатуры ноутбука</span><b>от 1 400 ₽</b></div>
                    <div class="prow"><span>Замена аккумулятора ноутбука</span><b>от 900 ₽</b></div>
                </div>
                <div class="plist" id="p-tv">
                    <div class="prow"><span>Ремонт подсветки — перепайка светодиодов</span><b>от 5 000 ₽</b></div>
                    <div class="prow"><span>Замена подсветки — оригинальные ленты</span><b>от 7 000 ₽</b></div>
                    <div class="prow"><span>Ремонт блока питания телевизора</span><b>от 2 500 ₽</b></div>
                </div>
                <div class="plist" id="p-soft">
                    <div class="prow"><span>Установка Windows 10/11</span><b>от 2 500 ₽</b></div>
                    <div class="prow"><span>Удаление вирусов и рекламы</span><b>от 2 500 ₽</b></div>
                    <div class="prow"><span>Восстановление данных (программное)</span><b>от 3 300 ₽</b></div>
                </div>
                <div class="plist" id="p-mac">
                    <div class="prow"><span>Диагностика MacBook / iMac</span><b>3 800 ₽</b></div>
                    <div class="prow"><span>Замена аккумулятора MacBook</span><b>от 5 800 ₽</b></div>
                    <div class="prow"><span>Ремонт MacBook после залития</span><b>от 9 600 ₽</b></div>
                </div>
                <p class="pnote">
                    <svg class="ico">
                        <use href="#i-info" />
                    </svg><span>
                        Цены одинаковые в Якутске и Владивостоке. Вечерний выезд после 22:00 — +25%. Окончательная
                        стоимость — после диагностики.
                    </span>
                </p>
            </div>
        </section>

        <section id="how">
            <div class="wrap">
                <div class="kicker">Как мы работаем</div>
                <h2>Пять шагов от заявки до выдачи</h2>
                <div class="steps">
                    <div class="step">
                        <h3>Заявка</h3>
                        <p>Оставляете заявку на сайте или звоните.</p>
                    </div>
                    <div class="step">
                        <h3>Приём или выезд</h3>
                        <p>Привозите технику или мастер приезжает.</p>
                    </div>
                    <div class="step">
                        <h3>Диагностика</h3>
                        <p>Находим причину неисправности.</p>
                    </div>
                    <div class="step">
                        <h3>Согласование</h3>
                        <p>Называем цену и сроки.</p>
                    </div>
                    <div class="step">
                        <h3>Ремонт и выдача</h3>
                        <p>Чиним, проверяем, выдаём с гарантией.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="warranty" id="warranty">
            <div class="wrap wgrid">
                <div>
                    <div class="kicker">Гарантия</div>
                    <h2>Письменная гарантия до 6 месяцев</h2>
                    <p class="lead">Срок зависит от вида работ и записывается в акт выполненных работ.</p>
                </div>
                <div class="wtable">
                    <div><span>Запчасти</span><b>90 дней</b></div>
                    <div><span>Подсветка оригинальными лентами</span><b>от 6 месяцев</b></div>
                    <div><span>Работа, чистка, пайка и BGA</span><b>30 дней</b></div>
                    <div><span>Windows и программы</span><b>14 дней</b></div>
                    <div><span>После попадания жидкости</span><b>14 дней или без гарантии</b></div>
                </div>
            </div>
        </section>

        <section id="works">
            <div class="wrap">
                <div class="kicker">Работы</div>
                <h2>Из нашей мастерской</h2>
                <p class="lead">Реальные фото и видео наших ремонтов.</p>
                <div class="gallery">
                    <figure>
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRy-lMPZvjZB9qiGTwgUe6clnxgdOC-eXGFaUaFiwaYXnIzMufACUxF_6o&s=10"
                             alt="Мастерская" loading="lazy">
                        <figcaption>Мастерская</figcaption>
                    </figure>
                    <figure>
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQZxIp9QgMSuaXGebmSIZhO6je8vH4xZpgzRjHTbaqWWTF9vjPrX_KlLKU&s=10"
                             alt="Чистка ноутбука" loading="lazy">
                        <figcaption>Чистка ноутбука</figcaption>
                    </figure>
                    <figure>
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRrcw8U9ZD8i8RXqQLOZEWSg2eRD_YTUvpsQpX8XYKDhk5l9JVPBY_Z1Dfs&s=10"
                             alt="Ремонт ТВ" loading="lazy">
                        <figcaption>Ремонт ТВ</figcaption>
                    </figure>
                    <figure>
                        <img src="https://com-dv.ru/wa-data/public/shop/themes/default/images/sborka-komputerov-na-zakaz.jpg"
                             alt="Сборка ПК" loading="lazy">
                        <figcaption>Сборка ПК</figcaption>
                    </figure>
                </div>
            </div>
        </section>

        <section id="reviews" style="padding-top:0">
            <div class="wrap">
                <div class="kicker">Отзывы</div>
                <h2>Что пишут клиенты в 2ГИС</h2>
                <div class="reviews">
                    <figure class="rv">
                        <div class="stars">★★★★★</div>
                        <p>
                            Почистили систему охлаждения и поменяли термопасту, поставили дополнительный SSD. Ноутбук перестал
                            греться и тормозить. Всё супер, рекомендую 👌
                        </p>
                        <figcaption class="who"><b>Monara Samokat</b> · 2ГИС</figcaption>
                    </figure>
                    <figure class="rv">
                        <div class="stars">★★★★★</div>
                        <p>
                            Чинил тут ноутбук, меняли матрицу и харддиск на ссд, сделали всё профессионально и быстро, персонал
                            вежливый.
                        </p>
                        <figcaption class="who"><b>Артур Николаев</b> · 2ГИС</figcaption>
                    </figure>
                    <figure class="rv">
                        <div class="stars">★★★★★</div>
                        <p>
                            Сегодня почистили комп, заменили кулер, поставили антивирусник. Константин всё сделал быстро и доходчиво
                            объяснил!
                        </p>
                        <figcaption class="who"><b>Сардана Я.</b> · 2ГИС</figcaption>
                    </figure>
                </div>
                <div class="links2gis">
                    <a class="btn btn-line" href="https://2gis.ru/yakutsk/firm/70000001115142576/tab/reviews" target="_blank"
                       rel="noopener">
                        Все отзывы — Якутск <svg class="ico">
                            <use href="#i-ext" />
                        </svg>
                    </a>
                    <a class="btn btn-line" href="https://2gis.ru/vladivostok/firm/70000001118184524/tab/reviews" target="_blank"
                       rel="noopener">
                        Отзывы — Владивосток <svg class="ico">
                            <use href="#i-ext" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <?php if ($latestNews): ?>
        <section id="news">
          <div class="wrap">
            <div class="kicker">Новости</div>
            <h2>Что нового в сервисе</h2>
            <div class="news-grid-home">
              <?php foreach ($latestNews as $n):
                $d = date('d.m.Y', strtotime($n['published_at'] ?? 'now')); ?>
                <a class="news-card-home" href="/news.php?slug=<?= urlencode($n['slug']) ?>">
                  <?php if ($n['cover']): ?>
                    <div class="nc-img" style="background-image:url('<?= htmlspecialchars($n['cover']) ?>')"></div>
                  <?php endif; ?>
                  <div class="nc-body">
                    <div class="nc-date"><?= $d ?></div>
                    <h3><?= htmlspecialchars($n['title']) ?></h3>
                    <p><?= htmlspecialchars($n['excerpt']) ?></p>
                    <span class="nc-more">Читать →</span>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
            <p style="margin-top:24px"><a class="btn btn-line" href="/news.php">Все новости →</a></p>
          </div>
        </section>

        <style>
        .news-grid-home{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:32px}
        .news-card-home{background:var(--panel);border:1px solid var(--line-soft);border-radius:18px;overflow:hidden;color:inherit;text-decoration:none;display:flex;flex-direction:column;transition:border-color .25s,transform .25s}
        .news-card-home:hover{border-color:rgba(26,229,138,.45);transform:translateY(-3px)}
        .nc-img{height:160px;background-size:cover;background-position:center}
        .nc-body{padding:20px;display:flex;flex-direction:column;flex:1}
        .nc-date{font-size:12px;color:var(--ink-dim);margin-bottom:8px;text-transform:uppercase;letter-spacing:.12em;font-weight:700}
        .nc-body h3{font-family:var(--t);font-size:17px;margin:0 0 8px;line-height:1.3}
        .nc-body p{color:var(--ink-dim);font-size:14px;flex:1;margin:0 0 12px}
        .nc-more{color:var(--green);font-weight:700;font-size:13.5px}
        @media(max-width:880px){.news-grid-home{grid-template-columns:1fr 1fr}}
        @media(max-width:600px){.news-grid-home{grid-template-columns:1fr}}
        </style>
        <?php endif; ?>

        <section class="b2b" id="b2b">
            <div class="wrap">
                <div>
                    <div class="kicker">Организациям</div>
                    <h2>Обслуживаем компьютеры офисов и магазинов</h2>
                    <p class="lead">Работаем с юрлицами и ИП по договору.</p>
                    <ul>
                        <li>
                            <svg class="ico">
                                <use href="#i-check" />
                            </svg>Ввод рабочих мест: компьютеры, принтеры, МФУ
                        </li>
                        <li>
                            <svg class="ico">
                                <use href="#i-check" />
                            </svg>Локальная сеть, Wi-Fi, VPN
                        </li>
                        <li>
                            <svg class="ico">
                                <use href="#i-check" />
                            </svg>Плановая чистка и профилактика
                        </li>
                        <li>
                            <svg class="ico">
                                <use href="#i-check" />
                            </svg>Срочный выезд при поломке
                        </li>
                    </ul>
                    <div class="hero-cta" style="margin-bottom:0">
                        <a class="btn btn-green js-wa" href="https://wa.me/79148212918" target="_blank" rel="noopener">
                            <svg class="ico">
                                <use href="#i-wa" />
                            </svg>Обсудить задачу
                        </a>
                        <a class="btn btn-ghost js-tel" href="tel:+79148212918">
                            <svg class="ico">
                                <use href="#i-phone" />
                            </svg>Позвонить
                        </a>
                    </div>
                </div>
                <div class="ph">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQwGNiHWRhYP5wcDJzpLMezUpo0qg9hP4tk8fc5ddMgjs3OvovON0BprzgU&s=10"
                         alt="Сетевой шкаф" loading="lazy">
                </div>
            </div>
        </section>

        <section class="reach" id="reach">
            <div class="wrap">
                <div class="kicker">Связаться</div>
                <h2>Напишите — бот ответит сразу</h2>
                <p class="lead">В Telegram и MAX бот сразу покажет цены и статус заказа.</p>
                <div class="reach-grid">
                    <a class="btn btn-tg" href="https://t.me/gigabyte_remont14" target="_blank" rel="noopener">
                        <svg class="ico">
                            <use href="#i-tg" />
                        </svg>Telegram
                    </a>
                    <a class="btn btn-max" href="https://max.ru" target="_blank" rel="noopener">
                        <svg class="ico">
                            <use href="#i-max" />
                        </svg>MAX
                    </a>
                    <a class="btn btn-wa js-wa" href="https://wa.me/79148212918" target="_blank" rel="noopener">
                        <svg class="ico">
                            <use href="#i-wa" />
                        </svg>WhatsApp
                    </a>
                    <a class="btn btn-call js-tel" href="tel:+79148212918">
                        <svg class="ico">
                            <use href="#i-phone" />
                        </svg><span class="js-tel-txt">+7 (914) 821-29-18</span>
                    </a>
                </div>
                <p class="reach-note">10:00–21:00, воскресенье — по предварительной записи.</p>
            </div>
        </section>

        <section class="contacts" id="contacts">
            <div class="wrap">
                <div class="kicker">Контакты</div>
                <h2>Два сервиса — Якутск и Владивосток</h2>
                <div class="cgrid">
                    <div class="ccard active">
                        <h3>
                            <svg class="ico">
                                <use href="#i-pin" />
                            </svg>Якутск
                        </h3>
                        <dl>
                            <dt>Адрес</dt>
                            <dd>ул. Ойунского, 24/2г</dd>
                            <dt>Телефон</dt>
                            <dd><a href="tel:+79148212918">+7 (914) 821-29-18</a></dd>
                            <dt>Часы</dt>
                            <dd>10:00–21:00, вс — по записи</dd>
                        </dl>
                        <div class="acts">
                            <a class="btn btn-wa" href="https://wa.me/79148212918" target="_blank" rel="noopener">
                                <svg class="ico">
                                    <use href="#i-wa" />
                                </svg>WhatsApp
                            </a>
                            <a class="btn btn-line" href="https://2gis.ru/yakutsk/firm/70000001115142576" target="_blank"
                               rel="noopener">
                                2ГИС <svg class="ico">
                                    <use href="#i-ext" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="ccard">
                        <h3>
                            <svg class="ico">
                                <use href="#i-pin" />
                            </svg>Владивосток · «Гигабит»
                        </h3>
                        <dl>
                            <dt>Адрес</dt>
                            <dd>ул. Баляева, 52 ст9</dd>
                            <dt>Телефон</dt>
                            <dd><a href="tel:+79240030088">+7 (924) 003-00-88</a></dd>
                            <dt>Часы</dt>
                            <dd>10:00–21:00, вс — по записи</dd>
                        </dl>
                        <div class="acts">
                            <a class="btn btn-wa" href="https://wa.me/79240030088" target="_blank" rel="noopener">
                                <svg class="ico">
                                    <use href="#i-wa" />
                                </svg>WhatsApp
                            </a>
                            <a class="btn btn-line" href="https://2gis.ru/vladivostok/firm/70000001118184524" target="_blank"
                               rel="noopener">
                                2ГИС <svg class="ico">
                                    <use href="#i-ext" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="wrap">
            <div>
                <a class="logo" href="/">
                    <span class="mark">
                        <img src="111.png"
                             alt="Логотип Гигабайт" loading="lazy" width="38" height="38">
                    </span>Гигабайт
                </a>
                <p style="margin-top:16px;font-size:14px;color:var(--ink-dim)">
                    Центр по ремонту компьютерной техники. Якутск и
                    Владивосток.
                </p>
                <p style="margin-top:14px;font-size:13px;color:var(--ink-dim)">
                    <a href="#privacy" class="js-open-policy" data-doc="privacy">Политика конфиденциальности</a><br>
                    <a href="#terms" class="js-open-policy" data-doc="terms">Пользовательское соглашение</a>
                </p>
            </div>
            <div>
                <ul>
                    <li><a href="#prices">Цены</a></li>
                    <li><a href="#warranty">Гарантия</a></li>
                    <li><a href="#works">Работы</a></li>
                    <li><a href="/news.php">Новости</a></li>
                    <li><a href="#contacts">Контакты</a></li>
                </ul>
            </div>
            <div>
                <ul>
                    <li><a href="https://t.me/gigabyte_remont14" target="_blank" rel="noopener">Telegram-канал</a></li>
                    <li><a href="https://wa.me/79148212918" target="_blank" rel="noopener">WhatsApp</a></li>
                </ul>
            </div>
            <div class="legal">
                © 2026 Гигабайт · Якутск: ИП Менжуренко Евгений Александрович, ИНН 143518994549 · Владивосток
                до 01.11.2026 — «Гигабит» · Информация на сайте не является публичной офертой.
            </div>
        </div>
    </footer>

    <div class="fab" id="fab">
        <button class="fab-btn" id="fabBtn" type="button" aria-label="Связаться">
            <svg class="ico">
                <use href="#i-chat" />
            </svg>
        </button>
    </div>

    <!-- Модалка документов -->
    <div class="modal" id="docModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="docTitle">
        <div class="modal-backdrop" data-close></div>
        <div class="modal-panel">
            <button class="modal-close" type="button" aria-label="Закрыть" data-close>&times;</button>
            <div class="modal-body" id="docBody"></div>
        </div>
    </div>

    <template id="tpl-privacy">
        <div class="doc">
            <h2>Политика конфиденциальности и обработки персональных данных</h2>
            <div class="doc-meta">
                Редакция от 01.01.2026 · Действует для сайта сервисного центра «Гигабайт»<br>
                Оператор: ИП Менжуренко Евгений Александрович, ИНН 143518994549
            </div>

            <h3>1. Общие положения</h3>
            <p>
                1.1. Настоящая Политика определяет порядок обработки и защиты персональных данных пользователей сайта
                сервисного центра «Гигабайт» (далее — Сайт) и составлена в соответствии с Федеральным законом
                от 27.07.2006 № 152-ФЗ «О персональных данных».
            </p>
            <p>
                1.2. Оператором персональных данных является ИП Менжуренко Евгений Александрович, ИНН 143518994549,
                адрес: Республика Саха (Якутия), г. Якутск, ул. Ойунского, 24/2г (далее — Оператор).
            </p>
            <p>
                1.3. Используя Сайт и отправляя заявку через форму, пользователь подтверждает согласие с настоящей
                Политикой и даёт согласие на обработку своих персональных данных на изложенных ниже условиях.
            </p>
            <p>
                1.4. Политика применяется ко всем персональным данным, которые Оператор получает от пользователей
                Сайта.
            </p>

            <h3>2. Состав обрабатываемых персональных данных</h3>
            <p>2.1. Оператор обрабатывает следующие данные, которые пользователь указывает добровольно в форме заявки:</p>
            <ul>
                <li>имя (или иное обращение);</li>
                <li>номер телефона;</li>
                <li>город (Якутск / Владивосток);</li>
                <li>тип устройства и описание неисправности;</li>
                <li>адрес — только если пользователь выбрал выезд мастера;</li>
                <li>предпочтительный способ связи.</li>
            </ul>
            <p>2.2. Автоматически, без участия пользователя, могут обрабатываться технические данные:</p>
            <ul>
                <li>IP-адрес, тип браузера и устройства, разрешение экрана;</li>
                <li>дата и время обращения к Сайту, адрес запрошенной страницы;</li>
                <li>файлы cookie и данные систем веб-аналитики (при их подключении).</li>
            </ul>
            <p>
                2.3. Оператор <b>не обрабатывает</b> специальные категории персональных данных (раса, национальность,
                политические взгляды, состояние здоровья, интимная жизнь) и биометрические данные.
            </p>

            <h3>3. Цели обработки</h3>
            <ul>
                <li>приём и обработка заявок на ремонт и диагностику;</li>
                <li>связь с пользователем по указанным им контактам (звонок, сообщение в мессенджере, SMS);</li>
                <li>согласование стоимости, сроков и условий работ;</li>
                <li>исполнение договора на оказание услуг и гарантийных обязательств;</li>
                <li>улучшение работы Сайта и качества сервиса (в обезличенном виде);</li>
                <li>исполнение требований законодательства РФ.</li>
            </ul>

            <h3>4. Правовые основания обработки</h3>
            <p>4.1. Обработка осуществляется на основании:</p>
            <ul>
                <li>
                    согласия субъекта персональных данных, выраженного путём проставления отметки в чекбоксе формы
                    заявки (п. 1 ч. 1 ст. 6 152-ФЗ);
                </li>
                <li>
                    необходимости исполнения договора, стороной которого является субъект персональных данных
                    (п. 5 ч. 1 ст. 6 152-ФЗ);
                </li>
                <li>требований законодательства РФ в части оказания услуг и гарантий.</li>
            </ul>

            <h3>5. Порядок и сроки обработки</h3>
            <p>
                5.1. Обработка персональных данных осуществляется с использованием средств автоматизации и без
                таковых: сбор, запись, систематизация, накопление, хранение, уточнение, извлечение, использование,
                передача (предоставление, доступ), блокирование, удаление, уничтожение.
            </p>
            <p>
                5.2. Срок хранения персональных данных — до достижения целей обработки, но не более <b>3 (трёх) лет</b>
                с момента последнего обращения пользователя, если иное не предусмотрено законом или договором.
                Данные, связанные с гарантийными обязательствами, хранятся в течение гарантийного срока и срока
                исковой давности.
            </p>
            <p>
                5.3. При отзыве согласия Оператор прекращает обработку и уничтожает персональные данные в срок,
                не превышающий 30 дней, если иное не предусмотрено законом.
            </p>

            <h3>6. Передача персональных данных третьим лицам</h3>
            <p>6.1. Оператор не продаёт и не передаёт персональные данные третьим лицам, за исключением случаев:</p>
            <ul>
                <li>
                    передачи сервисам связи и мессенджерам (WhatsApp, Telegram, MAX) для ответа пользователю,
                    инициированного самим пользователем;
                </li>
                <li>передачи подрядчикам, участвующим в исполнении ремонта, в объёме, необходимом для оказания услуги;</li>
                <li>
                    предоставления данных по мотивированному запросу уполномоченных государственных органов
                    в порядке, установленном законодательством РФ.
                </li>
            </ul>
            <p>
                6.2. При передаче данных третьим лицам Оператор требует от них соблюдения конфиденциальности
                и безопасности персональных данных.
            </p>

            <h3>7. Меры защиты персональных данных</h3>
            <p>
                7.1. Оператор принимает необходимые правовые, организационные и технические меры для защиты
                персональных данных от неправомерного доступа, уничтожения, изменения, блокирования, копирования,
                распространения:
            </p>
            <ul>
                <li>ограничение доступа сотрудников к персональным данным по принципу должностной необходимости;</li>
                <li>использование защищённых каналов связи (HTTPS) при передаче данных через Сайт;</li>
                <li>хранение данных на защищённых серверах и носителях;</li>
                <li>ознакомление сотрудников с требованиями 152-ФЗ и настоящей Политикой.</li>
            </ul>

            <h3>8. Права пользователя</h3>
            <p>8.1. Пользователь вправе:</p>
            <ul>
                <li>получать информацию об обработке своих персональных данных;</li>
                <li>
                    требовать уточнения, блокирования или уничтожения данных, если они неполны, устарели,
                    неточны или незаконно получены;
                </li>
                <li>отозвать согласие на обработку персональных данных в любой момент;</li>
                <li>обращаться с жалобой в Роскомнадзор или суд.</li>
            </ul>
            <p>
                8.2. Для реализации прав направьте обращение на телефон <a href="tel:+79148212918">+7 (914) 821-29-18</a>
                (WhatsApp/Telegram) или лично в сервисный центр по адресу: г. Якутск, ул. Ойунского, 24/2г.
            </p>

            <h3>9. Файлы cookie и аналитика</h3>
            <p>
                9.1. Сайт может использовать файлы cookie для корректной работы интерфейса и сбора обезличенной
                статистики посещений. Пользователь может отключить cookie в настройках браузера — часть функций
                Сайта при этом может работать некорректно.
            </p>

            <h3>10. Заключительные положения</h3>
            <p>
                10.1. Оператор вправе вносить изменения в настоящую Политику. Новая редакция вступает в силу
                с момента её размещения на Сайте, если иное не указано в самой редакции.
            </p>
            <p>
                10.2. Действующая редакция всегда доступна по ссылке «Политика конфиденциальности» в форме заявки
                и в подвале Сайта.
            </p>

            <div class="doc-note">
                Документ носит информационный характер и не является публичной офертой. По вопросам обработки
                персональных данных обращайтесь: ИП Менжуренко Е. А., ИНН 143518994549.
            </div>
        </div>
    </template>

    <template id="tpl-terms">
        <div class="doc">
            <h2>Пользовательское соглашение</h2>
            <div class="doc-meta">
                Редакция от 01.01.2026 · Сайт сервисного центра «Гигабайт»<br>
                Оператор: ИП Менжуренко Евгений Александрович, ИНН 143518994549
            </div>

            <h3>1. Термины и общие положения</h3>
            <p>
                1.1. Настоящее Соглашение регулирует отношения между ИП Менжуренко Евгением Александровичем
                (ИНН 143518994549), далее — «Исполнитель», и любым лицом, использующим Сайт, далее — «Пользователь».
            </p>
            <p>
                1.2. Сайт — интернет-ресурс сервисного центра «Гигабайт», размещённый по адресу, на котором
                опубликовано настоящее Соглашение.
            </p>
            <p>
                1.3. Использование Сайта (просмотр, отправка заявки, обращение через мессенджеры) означает полное
                и безоговорочное согласие Пользователя с условиями настоящего Соглашения и
                <a href="#privacy" class="js-open-policy" data-doc="privacy">Политикой конфиденциальности</a>.
            </p>
            <p>
                1.4. Соглашение не является публичной офертой в смысле ст. 437 ГК РФ. Информация о ценах и услугах
                на Сайте носит справочный характер.
            </p>

            <h3>2. Предмет</h3>
            <p>2.1. Сайт предоставляет Пользователю возможность:</p>
            <ul>
                <li>ознакомиться с услугами и ориентировочными ценами сервисного центра;</li>
                <li>оставить заявку на диагностику и ремонт техники;</li>
                <li>связаться с Исполнителем через телефон, WhatsApp, Telegram, MAX;</li>
                <li>получить информацию об адресах, режиме работы и гарантии.</li>
            </ul>
            <p>
                2.2. Непосредственное оказание услуг по ремонту осуществляется на основании отдельного договора
                (заказ-наряда, акта выполненных работ), оформляемого при приёме техники.
            </p>

            <h3>3. Оставление заявки</h3>
            <p>
                3.1. Пользователь обязуется указывать достоверные данные: имя, телефон, при необходимости — адрес.
                Ответственность за достоверность данных несёт Пользователь.
            </p>
            <p>
                3.2. Отправляя заявку, Пользователь подтверждает согласие на обработку персональных данных
                в соответствии с Политикой конфиденциальности.
            </p>
            <p>
                3.3. Заявка считается принятой с момента её успешной отправки через форму Сайта либо получения
                сообщения в мессенджере. Исполнитель связывается с Пользователем в рабочее время.
            </p>

            <h3>4. Цены и условия оказания услуг</h3>
            <p>
                4.1. Цены, указанные на Сайте, являются ориентировочными (цены «от») и не являются окончательными.
                Точная стоимость определяется после диагностики.
            </p>
            <p>4.2. Стоимость запчастей и комплектующих оплачивается отдельно, если иное не согласовано сторонами.</p>
            <p>4.3. Сроки выполнения работ согласуются индивидуально и фиксируются в заказ-наряде.</p>
            <p>
                4.4. Гарантийные обязательства предоставляются в объёме и на сроки, указанные в акте выполненных
                работ, и не распространяются на повреждения, возникшие по вине Пользователя, а также на следы
                самостоятельного вскрытия и ремонта техники.
            </p>

            <h3>5. Права и обязанности сторон</h3>
            <p>5.1. Пользователь вправе:</p>
            <ul>
                <li>получать достоверную информацию об услугах и ценах;</li>
                <li>отказаться от услуг на любом этапе до начала работ;</li>
                <li>обращаться с претензиями и вопросами через контакты, указанные на Сайте.</li>
            </ul>
            <p>5.2. Пользователь обязуется:</p>
            <ul>
                <li>не использовать Сайт для противоправных действий;</li>
                <li>
                    не пытаться нарушить работу Сайта, не осуществлять автоматизированный сбор данных без согласия
                    Исполнителя;
                </li>
                <li>предоставлять достоверные персональные данные и сведения о технике.</li>
            </ul>
            <p>5.3. Исполнитель вправе:</p>
            <ul>
                <li>изменять состав услуг и цены на Сайте;</li>
                <li>приостанавливать работу Сайта для технического обслуживания;</li>
                <li>отказать в оказании услуг при наличии признаков противоправных действий Пользователя.</li>
            </ul>

            <h3>6. Интеллектуальная собственность</h3>
            <p>
                6.1. Содержимое Сайта (тексты, изображения, логотипы, дизайн) принадлежит Исполнителю или
                используется на законных основаниях. Копирование и коммерческое использование без письменного
                согласия Исполнителя не допускаются.
            </p>

            <h3>7. Ответственность сторон</h3>
            <p>7.1. Исполнитель не несёт ответственности за:</p>
            <ul>
                <li>невозможность связаться с Пользователем по указанным им контактам;</li>
                <li>
                    перерывы в работе Сайта, вызванные техническими причинами, действиями третьих лиц или
                    обстоятельствами непреодолимой силы;
                </li>
                <li>содержание внешних сайтов, ссылки на которые могут размещаться на Сайте.</li>
            </ul>
            <p>
                7.2. Ответственность за качество и сроки ремонта определяется условиями отдельного договора
                (заказ-наряда) и законодательством РФ о защите прав потребителей.
            </p>

            <h3>8. Персональные данные</h3>
            <p>
                8.1. Порядок обработки персональных данных Пользователя определяется
                <a href="#privacy" class="js-open-policy" data-doc="privacy">Политикой конфиденциальности</a>,
                размещённой на Сайте.
            </p>

            <h3>9. Разрешение споров</h3>
            <p>
                9.1. Споры разрешаются путём переговоров. Претензионный порядок обязателен, срок ответа на
                претензию — 10 рабочих дней.
            </p>
            <p>
                9.2. При недостижении согласия спор передаётся на рассмотрение в суд в соответствии с
                законодательством РФ.
            </p>

            <h3>10. Изменение соглашения</h3>
            <p>
                10.1. Исполнитель вправе изменять настоящее Соглашение в одностороннем порядке. Новая редакция
                вступает в силу с момента публикации на Сайте. Продолжение использования Сайта означает согласие
                Пользователя с изменениями.
            </p>

            <h3>11. Контакты</h3>
            <p>
                ИП Менжуренко Евгений Александрович, ИНН 143518994549<br>
                Адрес: г. Якутск, ул. Ойунского, 24/2г<br>
                Телефон: <a href="tel:+79148212918">+7 (914) 821-29-18</a>
            </p>

            <div class="doc-note">
                Настоящее Соглашение не заменяет договор на оказание услуг по ремонту, оформляемый при приёме
                техники в сервисный центр.
            </div>
        </div>
    </template>

    <script>
        (function () {
            var CITY = {
                yakutsk: { name: 'Якутск', in: 'в Якутске', tel: '+79148212918', telTxt: '+7 (914) 821-29-18', wa: 'https://wa.me/79148212918', visit: 'Выезд мастера с диагностикой — от 2 000 ₽' },
                vladivostok: { name: 'Владивосток', in: 'во Владивостоке', tel: '+79240030088', telTxt: '+7 (924) 003-00-88', wa: 'https://wa.me/79240030088', visit: 'Выезд мастера с диагностикой — от 2 000 ₽' }
            };
            var city = 'yakutsk';
            try { var q = new URLSearchParams(location.search).get('city'); if (CITY[q]) city = q; } catch (e) { }

            function setCity(c) {
                city = c; var d = CITY[c];
                document.querySelectorAll('.city button').forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.city === c ? 'true' : 'false'); });
                document.querySelectorAll('.ccard').forEach(function (card, i) {
                    card.classList.toggle('active', (c === 'yakutsk' && i === 0) || (c === 'vladivostok' && i === 1));
                });
                document.querySelectorAll('.js-city-name').forEach(function (el) { el.textContent = d.name; });
                document.querySelectorAll('.js-city-in').forEach(function (el) { el.textContent = d.in; });
                document.querySelectorAll('.js-visit').forEach(function (el) { el.textContent = d.visit; });
                document.querySelectorAll('.js-tel').forEach(function (el) { el.href = 'tel:' + d.tel; });
                document.querySelectorAll('.js-tel-txt').forEach(function (el) { el.textContent = d.telTxt; });
                document.querySelectorAll('.js-wa').forEach(function (el) { el.href = d.wa; });
                var r = document.querySelector('#cityRadios input[value="' + c + '"]'); if (r) r.checked = true;
            }
            document.querySelectorAll('.city button').forEach(function (b) { b.addEventListener('click', function () { setCity(b.dataset.city); }); });
            document.querySelectorAll('#cityRadios input').forEach(function (r) { r.addEventListener('change', function () { setCity(r.value); }); });
            setCity(city);

            var burger = document.getElementById('burger'), drawer = document.getElementById('drawer');
            burger.addEventListener('click', function () { var o = drawer.classList.toggle('open'); burger.setAttribute('aria-expanded', o); });
            drawer.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { drawer.classList.remove('open'); burger.setAttribute('aria-expanded', false); }); });

            document.querySelectorAll('.tab').forEach(function (t) {
                t.addEventListener('click', function () {
                    document.querySelectorAll('.tab').forEach(function (x) { x.setAttribute('aria-selected', x === t ? 'true' : 'false'); });
                    document.querySelectorAll('.plist').forEach(function (p) { p.classList.toggle('on', p.id === t.dataset.tab); });
                });
            });

            var form = document.getElementById('leadForm'), note = document.getElementById('formNote'), btn = document.getElementById('submitBtn');
            var dev = document.getElementById('f-device'), fault = document.getElementById('f-fault'), fgAddr = document.getElementById('fg-address');
            form.querySelectorAll('input[name=visit]').forEach(function (r) { r.addEventListener('change', function () { fgAddr.hidden = form.visit.value !== 'onsite'; }); });
            document.querySelectorAll('#chips .chip').forEach(function (c) {
                c.addEventListener('click', function () {
                    document.querySelectorAll('#chips .chip').forEach(function (x) { x.classList.remove('on'); });
                    c.classList.add('on'); dev.value = c.dataset.dev; if (!fault.value) fault.value = c.dataset.fault;
                    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    setTimeout(function () { document.getElementById('f-name').focus({ preventScroll: true }); }, 500);
                });
            });
            
            form.addEventListener('submit', function (e) {
    e.preventDefault();

    // -------- та же валидация, что была --------
    var ok = true, phoneDigits = form.phone.value.replace(/\D/g, '');
    var fgN = document.getElementById('fg-name'), fgP = document.getElementById('fg-phone');
    var cw  = document.getElementById('consentWrap'), consentErr = document.getElementById('consentErr');

    fgN.classList.toggle('err', !form.name.value.trim());
    if (!form.name.value.trim()) ok = false;

    var pOk = phoneDigits.length === 10 || (phoneDigits.length === 11 && /^[78]/.test(phoneDigits));
    fgP.classList.toggle('err', !pOk);
    if (!pOk) ok = false;

    var consentOk = form.consent.checked;
    cw.classList.toggle('err', !consentOk);
    consentErr.style.display = consentOk ? 'none' : 'block';
    if (!consentOk) ok = false;

    if (!ok) {
        note.className = 'note bad';
        note.textContent = 'Проверьте имя, телефон и согласие на обработку персональных данных.';
        return;
    }

    // -------- payload --------
    var payload = {
        name:     form.name.value.trim(),
        phone:    form.phone.value.trim(),
        city:     form.city.value,
        device:   dev.value,
        fault:    fault.value.trim(),
        address:  form.address ? form.address.value.trim() : '',
        visit:    form.visit.value,
        consent:  true,
        csrf: (document.getElementById('f-csrf') || {}).value || '',
        page:     location.href,
        utm:      location.search
    };

    btn.disabled = true;
    btn.textContent = 'Отправляем…';
    note.className = 'note';
    note.textContent = '';

    // -------- реальный fetch --------
    fetch('send.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
    })
    .then(function (r) {
        return r.text().then(function (txt) {
            var data;
            try { data = JSON.parse(txt); }
            catch (err) {
                throw new Error('Ответ сервера не JSON: ' + txt.slice(0, 200));
            }
            return { status: r.status, data: data };
        });
    })
    .then(function (res) {
        if (res.status === 200 && res.data && res.data.ok) {
            note.className = 'note ok';
            note.innerHTML = '<b>Заявка принята.</b> Мы свяжемся с вами. Срочно — <a href="tel:' + CITY[city].tel + '">позвоните</a>.';
            form.reset();
            fgAddr.hidden = true;
            setCity(city);
        } else {
            note.className = 'note bad';
            note.textContent = (res.data && res.data.error) || ('Ошибка сервера: HTTP ' + res.status);
        }
    })
    .catch(function (err) {
        note.className = 'note bad';
        note.textContent = 'Не удалось отправить: ' + err.message + '. Проверьте, что send.php существует и Apache запущен.';
        console.error('send error:', err);
    })
    .finally(function () {
        btn.disabled = false;
        btn.textContent = 'Отправить заявку';
    });
});

            var fabBtn = document.getElementById('fabBtn');
            fabBtn.addEventListener('click', function () { document.querySelector('.reach').scrollIntoView({ behavior: 'smooth' }); });
        })();
        /* ---------- Документы: политика и пользовательское соглашение ---------- */
        (function initDocs() {
            var modal = document.getElementById('docModal');
            if (!modal) return;
            var body = document.getElementById('docBody');
            var lastFocus = null;

            function openDoc(kind) {
                var tpl = document.getElementById('tpl-' + kind);
                if (!tpl) return;
                lastFocus = document.activeElement;
                body.innerHTML = '';
                body.appendChild(tpl.content.cloneNode(true));
                body.scrollTop = 0;
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
                var closeBtn = modal.querySelector('.modal-close');
                if (closeBtn) closeBtn.focus();
            }

            function closeDoc() {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                if (lastFocus && lastFocus.focus) lastFocus.focus();
            }

            document.querySelectorAll('.js-open-policy').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    openDoc(link.dataset.doc || 'privacy');
                });
            });

            modal.querySelectorAll('[data-close]').forEach(function (el) {
                el.addEventListener('click', closeDoc);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.classList.contains('open')) closeDoc();
            });

            // ловушка фокуса внутри модалки
            modal.addEventListener('keydown', function (e) {
                if (e.key !== 'Tab') return;
                var f = modal.querySelectorAll('a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])');
                if (!f.length) return;
                var first = f[0], last = f[f.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            });
        })();
    </script>
</body>

</html>