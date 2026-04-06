<?php
session_start();

$file = __DIR__ . '/special.json';

/*
|--------------------------------------------------------------------------
| LOGIN CONFIG
|--------------------------------------------------------------------------
*/
$EDITOR_USER = 'velmora';
$EDITOR_PASS = 'cafenea123';

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/
function getDefaultSpecialData(): array
{
    return [
        "enabled"  => false,
        "type"     => "classic",
        "name"     => "",
        "from"     => "",
        "to"       => "",
        "message"  => "",
        "duration" => 10,
        "video"    => "",
        "audio"    => ""
    ];
}

function readSpecialJson(string $file): array
{
    $defaults = getDefaultSpecialData();

    if (!file_exists($file)) {
        return $defaults;
    }

    $raw = file_get_contents($file);
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        return $defaults;
    }

    return array_merge($defaults, $data);
}

function writeSpecialJson(string $file, array $data): bool
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents($file, $json) !== false;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| LOGIN / LOGOUT
|--------------------------------------------------------------------------
*/
$loginError = '';
$flashMessage = '';
$flashType = 'ok';

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
| SAVE / RESET
|--------------------------------------------------------------------------
*/
$current = readSpecialJson($file);

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_action'])) {
    $newData = [
        "enabled"  => isset($_POST['enabled']),
        "type"     => ($_POST['type'] ?? 'classic') === 'dedication' ? 'dedication' : 'classic',
        "name"     => trim($_POST['name'] ?? ''),
        "from"     => trim($_POST['from'] ?? ''),
        "to"       => trim($_POST['to'] ?? ''),
        "message"  => trim($_POST['message'] ?? ''),
        "duration" => max(0, (int)($_POST['duration'] ?? 10)),
        "video"    => trim($_POST['video'] ?? ''),
        "audio"    => trim($_POST['audio'] ?? '')
    ];

    if (writeSpecialJson($file, $newData)) {
        $current = $newData;
        $flashMessage = 'Modificările au fost salvate.';
        $flashType = 'ok';
    } else {
        $flashMessage = 'Nu am putut salva fișierul special.json.';
        $flashType = 'err';
    }
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_action'])) {
    $resetData = getDefaultSpecialData();

    if (writeSpecialJson($file, $resetData)) {
        $current = $resetData;
        $flashMessage = 'special.json a fost resetat.';
        $flashType = 'ok';
    } else {
        $flashMessage = 'Nu am putut reseta fișierul special.json.';
        $flashType = 'err';
    }
}

$videoOptions = [
    "" => "Fără video",
    "videos/special.mp4" => "Artificii",
];

$audioOptions = [
    "" => "Fără audio",
    "audio/special_audio.mp3" => "Champions",
    "audio/special_audio2.mp3" => "Prezidentiala",
];
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
      --bg: #060606;
      --card: rgba(14, 10, 10, 0.9);
      --card-2: rgba(20, 14, 14, 0.78);
      --white: #F9F2DF;
      --white-soft: rgba(249,242,223,0.78);
      --white-mid: rgba(249,242,223,0.58);
      --accent: #462121;
      --accent-2: #6b3434;
      --border: rgba(249,242,223,0.12);
      --ok: #2ea866;
      --ok-soft: rgba(46,168,102,0.18);
      --danger: #c34646;
      --danger-soft: rgba(195,70,70,0.18);
      --shadow: 0 24px 80px rgba(0,0,0,0.42);
      --radius-xl: 28px;
      --radius-lg: 20px;
      --radius-md: 16px;
    }

    * {
      box-sizing: border-box;
    }

    html, body {
      margin: 0;
      min-height: 100%;
      background:
        radial-gradient(circle at 15% 20%, rgba(70,33,33,0.22), transparent 24%),
        radial-gradient(circle at 82% 20%, rgba(249,242,223,0.05), transparent 18%),
        linear-gradient(180deg, #080808, #111, #090909);
      color: var(--white);
      font-family: 'Inter', sans-serif;
    }

    body {
      padding: 18px;
    }

    .page {
      max-width: 980px;
      margin: 0 auto;
    }

    .login-wrap {
      min-height: calc(100vh - 36px);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-card,
    .editor-card {
      background: linear-gradient(145deg, var(--card), var(--card-2));
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow);
      backdrop-filter: blur(16px);
    }

    .login-card {
      width: 100%;
      max-width: 460px;
      padding: 24px;
    }

    .brand-head {
      display: flex;
      flex-direction: column;
      gap: 4px;
      margin-bottom: 18px;
    }

    .brand-title {
      font-family: 'Amarante', serif;
      font-size: clamp(34px, 6vw, 56px);
      line-height: 1;
      letter-spacing: 0.01em;
    }

    .brand-sub {
      color: var(--white-soft);
      font-size: 14px;
    }

    .status {
      margin-bottom: 14px;
      padding: 12px 14px;
      border-radius: 14px;
      font-size: 14px;
    }

    .status.ok {
      background: var(--ok-soft);
      border: 1px solid rgba(46,168,102,0.25);
      color: #8ce0b2;
    }

    .status.err {
      background: var(--danger-soft);
      border: 1px solid rgba(195,70,70,0.25);
      color: #f1aaaa;
    }

    .field {
      margin-bottom: 16px;
    }

    .field-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-size: 13px;
      color: var(--white-soft);
    }

    input[type="text"],
    input[type="password"],
    input[type="number"],
    textarea,
    select {
      width: 100%;
      border: 1px solid var(--border);
      background: rgba(255,255,255,0.05);
      color: var(--white);
      border-radius: var(--radius-md);
      padding: 14px 15px;
      outline: none;
      font: inherit;
    }

    textarea {
      min-height: 120px;
      resize: vertical;
    }

    input:focus,
    textarea:focus,
    select:focus {
      border-color: rgba(249,242,223,0.22);
      box-shadow: 0 0 0 3px rgba(70,33,33,0.18);
    }

    .btn {
      border: 0;
      border-radius: 16px;
      padding: 13px 18px;
      cursor: pointer;
      font: inherit;
      font-weight: 700;
      transition: transform 0.15s ease, opacity 0.15s ease;
    }

    .btn:hover {
      transform: translateY(-1px);
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      color: var(--white);
      box-shadow: 0 12px 30px rgba(70,33,33,0.35);
    }

    .btn-secondary {
      background: rgba(255,255,255,0.06);
      color: var(--white);
      border: 1px solid var(--border);
    }

    .btn-block {
      width: 100%;
    }

    .editor-topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      margin-bottom: 18px;
    }

    .logout-link {
      text-decoration: none;
      color: var(--white);
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 10px 14px;
      white-space: nowrap;
    }

    .editor-card {
      overflow: hidden;
    }

    .editor-body {
      padding: 20px;
    }

    .section {
      margin-bottom: 20px;
      padding: 18px;
      border-radius: 22px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(249,242,223,0.08);
    }

    .section-title {
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 14px;
    }

    .type-pills {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .type-pill {
      position: relative;
      display: inline-flex;
      align-items: center;
      cursor: pointer;
    }

    .type-pill input {
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }

    .type-pill span {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 130px;
      padding: 12px 16px;
      border-radius: 14px;
      border: 1px solid var(--border);
      background: rgba(255,255,255,0.05);
      color: var(--white-soft);
      transition: all 0.25s ease;
      font-weight: 700;
    }

    .type-pill input:checked + span {
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      color: var(--white);
      border-color: rgba(249,242,223,0.18);
      box-shadow: 0 10px 26px rgba(70,33,33,0.30);
    }

    .switch-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      flex-wrap: wrap;
    }

    .switch-meta {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .switch-title {
      font-size: 15px;
      font-weight: 700;
    }

    .switch-desc {
      font-size: 13px;
      color: var(--white-mid);
    }

    .toggle {
      position: relative;
      width: 86px;
      height: 44px;
      flex: 0 0 auto;
    }

    .toggle input {
      display: none;
    }

    .toggle-slider {
      position: absolute;
      inset: 0;
      border-radius: 999px;
      background: linear-gradient(135deg, #7d1d1d, #c34646);
      box-shadow: inset 0 0 0 1px rgba(255,255,255,0.06);
      transition: all 0.25s ease;
    }

    .toggle-slider::before {
      content: "";
      position: absolute;
      width: 34px;
      height: 34px;
      left: 5px;
      top: 5px;
      border-radius: 50%;
      background: #fff;
      box-shadow: 0 4px 16px rgba(0,0,0,0.24);
      transition: transform 0.25s ease;
    }

    .toggle-slider::after {
      content: "OFF";
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.08em;
      color: rgba(255,255,255,0.9);
      transition: all 0.25s ease;
    }

    .toggle input:checked + .toggle-slider {
      background: linear-gradient(135deg, #1f8f52, #33c172);
    }

    .toggle input:checked + .toggle-slider::before {
      transform: translateX(42px);
    }

    .toggle input:checked + .toggle-slider::after {
      content: "ON";
      right: auto;
      left: 14px;
    }

    .actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 8px;
    }

    .hint {
      margin-top: 10px;
      color: var(--white-mid);
      font-size: 13px;
      line-height: 1.55;
    }

    @media (max-width: 820px) {
      body {
        padding: 10px;
      }

      .login-wrap {
        min-height: calc(100vh - 20px);
      }

      .login-card,
      .editor-body {
        padding: 16px;
      }

      .field-row {
        grid-template-columns: 1fr;
      }

      .editor-topbar {
        flex-direction: column;
        align-items: flex-start;
      }

      .logout-link {
        width: 100%;
        text-align: center;
      }

      .switch-row {
        align-items: flex-start;
      }

      .toggle {
        width: 82px;
        height: 42px;
      }

      .toggle-slider::before {
        width: 32px;
        height: 32px;
      }

      .toggle input:checked + .toggle-slider::before {
        transform: translateX(40px);
      }

      .type-pill span {
        min-width: 100%;
      }

      .type-pills {
        display: grid;
        grid-template-columns: 1fr;
      }

      .actions {
        display: grid;
        grid-template-columns: 1fr;
      }

      .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
  <div class="page">
    <div class="login-wrap">
      <div class="login-card">
        <div class="brand-head">
          <div class="brand-title">Velmora</div>
          <div class="brand-sub">Special Overlay Editor</div>
        </div>

        <?php if ($loginError): ?>
          <div class="status err"><?= h($loginError) ?></div>
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

          <button type="submit" class="btn btn-primary btn-block">Autentificare</button>
        </form>
      </div>
    </div>
  </div>
<?php else: ?>
  <div class="page">
    <div class="editor-topbar">
      <div class="brand-head" style="margin:0;">
        <div class="brand-title">Velmora</div>
        <div class="brand-sub">Editor pentru special.json</div>
      </div>

      <a href="?logout=1" class="logout-link">Logout</a>
    </div>

    <div class="editor-card">
      <div class="editor-body">

        <?php if ($flashMessage): ?>
          <div class="status <?= $flashType === 'ok' ? 'ok' : 'err' ?>">
            <?= h($flashMessage) ?>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="section">
            <div class="switch-row">
              <div class="switch-meta">
                <div class="switch-title">Activare special overlay</div>
                <div class="switch-desc">Pornește sau oprește mesajul special de pe ecran.</div>
              </div>

              <label class="toggle">
                <input type="checkbox" name="enabled" <?= !empty($current['enabled']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>

          <div class="section">
            <div class="section-title">Tip mesaj</div>

            <div class="type-pills">
              <label class="type-pill">
                <input type="radio" name="type" value="classic" <?= (($current['type'] ?? 'classic') === 'classic') ? 'checked' : '' ?>>
                <span>Clasic</span>
              </label>

              <label class="type-pill">
                <input type="radio" name="type" value="dedication" <?= (($current['type'] ?? '') === 'dedication') ? 'checked' : '' ?>>
                <span>De la / Pentru</span>
              </label>
            </div>
          </div>

          <div class="section">
            <div class="section-title">Conținut</div>

            <div class="field-row">
              <div class="field">
                <label>Nume principal (pentru modul clasic)</label>
                <input type="text" name="name" value="<?= h($current['name'] ?? '') ?>" placeholder="Andrei">
              </div>

              <div class="field">
                <label>Durată (secunde)</label>
                <input type="number" name="duration" min="0" value="<?= h((string)($current['duration'] ?? 10)) ?>">
              </div>
            </div>

            <div class="field-row">
              <div class="field">
                <label>De la</label>
                <input type="text" name="from" value="<?= h($current['from'] ?? '') ?>" placeholder="Andrei">
              </div>

              <div class="field">
                <label>Pentru</label>
                <input type="text" name="to" value="<?= h($current['to'] ?? '') ?>" placeholder="Maria">
              </div>
            </div>

            <div class="field">
              <label>Text</label>
              <textarea name="message" placeholder="Mulțumim pentru comandă. Enjoy your coffee."><?= h($current['message'] ?? '') ?></textarea>
            </div>
          </div>

          <div class="section">
            <div class="section-title">Media</div>

            <div class="field">
              <label>Video special</label>
              <select name="video">
                <?php foreach ($videoOptions as $value => $label): ?>
                  <option value="<?= h($value) ?>" <?= (($current['video'] ?? '') === $value) ? 'selected' : '' ?>>
                    <?= h($label) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="field">
              <label>Audio special</label>
              <select name="audio">
                <?php foreach ($audioOptions as $value => $label): ?>
                  <option value="<?= h($value) ?>" <?= (($current['audio'] ?? '') === $value) ? 'selected' : '' ?>>
                    <?= h($label) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="actions">
            <button type="submit" name="save_action" value="1" class="btn btn-primary">Salvează</button>
            <button type="submit" name="reset_action" value="1" class="btn btn-secondary">Reset</button>
          </div>

          <div class="hint">
            Tipul implicit este <strong>Clasic</strong>.<br>
            Pentru a adăuga mai multe opțiuni în dropdown, completezi în array-urile
            <strong>$videoOptions</strong> și <strong>$audioOptions</strong> din partea de sus a fișierului.
          </div>
        </form>

      </div>
    </div>
  </div>
<?php endif; ?>

</body>
</html>