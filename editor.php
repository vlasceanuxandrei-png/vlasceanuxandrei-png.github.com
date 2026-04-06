<?php
$file = 'special.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        "enabled" => isset($_POST['enabled']),
        "name" => $_POST['name'] ?? "",
        "message" => $_POST['message'] ?? "",
        "duration" => (int)($_POST['duration'] ?? 10),
        "video" => $_POST['video'] ?? "",
        "audio" => $_POST['audio'] ?? ""
    ];

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $success = true;
}

$current = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Velmora Editor</title>
<style>
body {
  font-family: Arial;
  background: #111;
  color: #F9F2DF;
  padding: 20px;
}
input, textarea {
  width: 100%;
  padding: 10px;
  margin: 6px 0;
  border-radius: 8px;
  border: none;
}
button {
  padding: 12px;
  background: #462121;
  color: #F9F2DF;
  border: none;
  border-radius: 10px;
  cursor: pointer;
}
.success {
  color: #4CAF50;
}
</style>
</head>
<body>

<h2>Velmora Special Editor</h2>

<?php if (!empty($success)): ?>
  <p class="success">Salvat ✔️</p>
<?php endif; ?>

<form method="POST">

<label>
  <input type="checkbox" name="enabled" <?= !empty($current['enabled']) ? 'checked' : '' ?>>
  Activ
</label>

<label>Nume:</label>
<input name="name" value="<?= $current['name'] ?? '' ?>">

<label>Mesaj:</label>
<textarea name="message"><?= $current['message'] ?? '' ?></textarea>

<label>Durata (secunde):</label>
<input type="number" name="duration" value="<?= $current['duration'] ?? 10 ?>">

<label>Video:</label>
<input name="video" value="<?= $current['video'] ?? '' ?>">

<label>Audio:</label>
<input name="audio" value="<?= $current['audio'] ?? '' ?>">

<br><br>
<button type="submit">Salvează</button>

</form>

</body>
</html>