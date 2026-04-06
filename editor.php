<?php
session_start();

$file = __DIR__ . '/special.json';

/*
|--------------------------------------------------------------------------
| CONFIG LOGIN
|--------------------------------------------------------------------------
*/
$EDITOR_USER = 'velmora';
$EDITOR_PASS = 'cafenea123';

/*
|--------------------------------------------------------------------------
| LOGIN / LOGOUT
|--------------------------------------------------------------------------
*/
$loginError = '';
$success = '';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: editor.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if ($user === $EDITOR_USER && $pass === $EDITOR_PASS) {
        $_SESSION['editor_logged_in'] = true;
        header('Location: editor.php');
        exit;
    } else {
        $loginError = 'User sau parolă incorectă.';
    }
}

$isLoggedIn = !empty($_SESSION['editor_logged_in']);

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/
function readSpecialJson(string $file): array
{
    if (!file_exists($file)) {
        return [
            "enabled" => false,
            "name" => "",
            "message" => "",
            "duration" => 10,
            "video" => "",
            "audio" => ""
        ];
    }

    $content = file_get_contents($file);
    $data = json_decode($content, true);

    if (!is_array($data)) {
        return [
            "enabled" => false,
            "name" => "",
            "message" => "",
            "duration" => 10,
            "video" => "",
            "audio" => ""
        ];
    }

    return array_merge([
        "enabled" => false,
        "name" => "",
        "message" => "",
        "duration" => 10,
        "video" => "",
        "audio" => ""
    ], $data);
}

function writeSpecialJson(string $file, array $data): bool
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents($file, $json) !== false;
}

/*
|--------------------------------------------------------------------------
| SAVE / RESET
|--------------------------------------------------------------------------
*/
$current = readSpecialJson($file);

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_action'])) {
    $newData = [
        "enabled"  => isset($_POST['enabled']),
        "name"     => trim($_POST['name'] ?? ''),
        "message"  => trim($_POST['message'] ?? ''),
        "duration" => max(0, (int)($_POST['duration'] ?? 10)),
        "video"    => trim($_POST['video'] ?? ''),
        "audio"    => trim($_POST['audio'] ?? '')
    ];

    if (writeSpecialJson($file, $newData)) {
        $success = 'Salvat cu succes.';
        $current = $newData;
    } else {
        $success = 'Nu am putut salva fișierul special.json.';
    }
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_action'])) {
    $resetData = [
        "enabled"  => false,
        "name"     => "",
        "message"  => "",
        "duration" => 0,
        "video"    => "",
        "audio"    => ""
    ];

    if (writeSpecialJson($file, $resetData)) {
        $success = 'special.json a fost resetat.';
        $current = $resetData;
    } else {
        $success = 'Nu am putut reseta fișierul special.json.';
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Velmora Editor</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Amarante&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #050505;
      --card: rgba(12, 8, 8, 0.88);
      --card-2: rgba(20, 12, 12, 0.78);
      --white: #F9F2DF;
      --white-soft: rgba(249, 242, 223, 0.75);
      --white-mid: rgba(249, 242, 223, 0.56);
      --accent: #462121;
      --accent-2: #6b3434;
      --border: rgba(249, 242, 223, 0.12);
      --ok: #7fd6a6;
      --danger: #d58d8d;
      --shadow: 0 25px 80px rgba(0, 0, 0, 0.42);
      --radius: 26px;
    }

    * {
      box-sizing: border-box;
    }

    html, body {
      margin: 0;
      min-height: 100%;
      background:
        radial-gradient(circle at 20% 20%, rgba(70,33,33,0.20), transparent 24%),
        radial-gradient(circle at 80% 22%, rgba(249,242,223,0.04), transparent 20%),
        linear-gradient(180deg, #080808, #111, #080808);
      color: var(--white);
      font-family: 'Inter', sans-serif;
    }

    body {
      padding: 24px;
    }

    .shell {
      max-width: 980px;
      margin: 0 auto;
    }

    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 20px;
    }

    .brand {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .brand-title {
      font-family: 'Amarante', serif;
      font-size: clamp(34px, 5vw, 56px);
      line-height: 1;
      letter-spacing: 0.01em;
    }

    .brand-sub {
      color: var(--white-soft);
      font-size: 14px;
    }

    .logout {
      text-decoration: none;
      color: var(--white);
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 10px 14px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .panel {
      background: linear-gradient(145deg, var(--card), var(--card-2));
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      backdrop-filter: blur(14px);
      overflow: hidden;
    }

    .panel-inner {
      padding: 24px;
    }

    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
    }

    .field {
      margin-bottom: 16px;
    }

    .field.full {
      grid-column: 1 / -1;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-size: 13px;
      color: var(--white-soft);
      letter-spacing: 0.02em;
    }

    input[type="text"],
    input[type="password"],
    input[type="number"],
    textarea {
      width: 100%;
      border: 1px solid var(--border);
      background: rgba(255,255,255,0.05);
      color: var(--white);
      border-radius: 16px;
      padding: 14px 16px;
      outline: none;
      font: inherit;
    }

    textarea {
      min-height: 120px;
      resize: vertical;
    }

    input:focus,
    textarea:focus {
      border-color: rgba(249,242,223,0.22);
      box-shadow: 0 0 0 3px rgba(70,33,33,0.18);
    }

    .checkbox-row {
      display: flex;
      align-items: center;
      gap: 10px;
      padding-top: 8px;
      color: var(--white);
      font-size: 15px;
    }

    input[type="checkbox"] {
      width: 18px;
      height: 18px;
      accent-color: var(--accent);
    }

    .actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 8px;
    }

    button {
      border: 0;
      border-radius: 16px;
      padding: 13px 18px;
      cursor: pointer;
      font: inherit;
      font-weight: 600;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      color: var(--white);
      box-shadow: 0 10px 30px rgba(70,33,33,0.35);
    }

    .btn-secondary {
      background: rgba(255,255,255,0.06);
      color: var(--white);
      border: 1px solid var(--border);
    }

    .status {
      margin-bottom: 18px;
      padding: 12px 14px;
      border-radius: 14px;
      font-size: 14px;
    }

    .status.ok {
      background: rgba(127,214,166,0.10);
      border: 1px solid rgba(127,214,166,0.28);
      color: var(--ok);
    }

    .status.err {
      background: rgba(213,141,141,0.10);
      border: 1px solid rgba(213,141,141,0.28);
      color: var(--danger);
    }

    .hint {
      margin-top: 18px;
      font-size: 13px;
      color: var(--white-mid);
      line-height: 1.55;
    }

    .login-wrap {
      min-height: calc(100vh - 48px);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-card {
      width: 100%;
      max-width: 460px;
      background: linear-gradient(145deg, var(--card), var(--card-2));
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      backdrop-filter: blur(14px);
      padding: 26px;
    }

    .login-title {
      font-family: 'Amarante', serif;
      font-size: 42px;
      text-align: center;
      margin-bottom: 6px;
    }

    .login-sub {
      text-align: center;
      color: var(--white-soft);
      font-size: 14px;
      margin-bottom: 22px;
    }

    @media (max-width: 820px) {
      .grid {
        grid-template-columns: 1fr;
      }

      .topbar {
        flex-direction: column;
        align-items: flex-start;
      }

      body {
        padding: 14px;
      }

      .panel-inner,
      .login-card {
        padding: 18px;
      }
    }
  </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
  <div class="login-wrap">
    <div class="login-card">
      <div class="login-title">Velmora</div>
      <div class="login-sub">Special Editor Access</div>

      <?php if ($loginError): ?>
        <div class="status err"><?= htmlspecialchars($loginError) ?></div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="login_action" value="1">

        <div class="field">
          <label>User</label>
          <input type="text" name="username" autocomplete="username" required>
        </div>

        <div class="field">
          <label>Parolă</label>
          <input type="password" name="password" autocomplete="current-password" required>
        </div>

        <button type="submit" class="btn-primary" style="width:100%;">Autentificare</button>
      </form>
    </div>
  </div>
<?php else: ?>
  <div class="shell">
    <div class="topbar">
      <div class="brand">
        <div class="brand-title">Velmora</div>
        <div class="brand-sub">Special Overlay Editor</div>
      </div>
      <a class="logout" href="?logout=1">Logout</a>
    </div>

    <div class="panel">
      <div class="panel-inner">
        <?php if ($success): ?>
          <div class="status ok"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="grid">
            <div class="field full">
              <label class="checkbox-row">
                <input type="checkbox" name="enabled" <?= !empty($current['enabled']) ? 'checked' : '' ?>>
                Activare special overlay
              </label>
            </div>

            <div class="field">
              <label>Nume</label>
              <input type="text" name="name" value="<?= htmlspecialchars($current['name'] ?? '') ?>" placeholder="Andrei">
            </div>

            <div class="field">
              <label>Durată (secunde)</label>
              <input type="number" name="duration" min="0" value="<?= htmlspecialchars((string)($current['duration'] ?? 10)) ?>">
            </div>

            <div class="field full">
              <label>Mesaj</label>
              <textarea name="message" placeholder="Mulțumim pentru comandă. Enjoy your coffee."><?= htmlspecialchars($current['message'] ?? '') ?></textarea>
            </div>

            <div class="field full">
              <label>Video special</label>
              <input type="text" name="video" value="<?= htmlspecialchars($current['video'] ?? '') ?>" placeholder="videos/special-bg.mp4">
            </div>

            <div class="field full">
              <label>Audio special</label>
              <input type="text" name="audio" value="<?= htmlspecialchars($current['audio'] ?? '') ?>" placeholder="audio/special.mp3">
            </div>
          </div>

          <div class="actions">
            <button type="submit" name="save_action" value="1" class="btn-primary">Salvează</button>
            <button type="submit" name="reset_action" value="1" class="btn-secondary">Reset</button>
          </div>
        </form>

        <div class="hint">
          Fișier editat: <strong>special.json</strong><br>
          Recomandare: după salvare, pagina principală va citi automat modificările dacă verificarea JSON-ului este deja activă în site.
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

</body>
</html>