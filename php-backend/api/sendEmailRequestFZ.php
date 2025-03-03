<?php
require_once "mailconfig.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo json_encode(["success" => false, "message" => "Ungültige Anfrage."]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email']) || !isset($data['name']) || !isset($data['vorname'])) {
  echo json_encode(["success" => false, "message" => "Falsche Eingaben"]);
  http_response_code(400);
  exit();
}

$email = $data['email'];
$name = $data['name'];
$vorname = $data['vorname'];

// E-Mail Inhalt
$message = "
    <h1>Führungszeugnis übermitteln</h1>
    <p>Hallo $vorname $name,</p>
    <div>Schicken Sie ihr Führungszeugnis bitte an diese E-Mail-Adresse: gewaltschutz@ecsa.de</div>
    <div>Im Anhang finden Sie die PDF.</div>
";

// Benutze die Funktion createMailConnection(), um eine Verbindung zu erstellen
$mail = createMailConnection();

// Absender & Empfänger
$mail->addAddress($email, "$vorname $name");

// PDF-Anhang
$pdfPath = __DIR__ . '/../assets/Aufforderung Polizeiliches Führungszeugnis 2023.pdf';
if (file_exists($pdfPath)) {
  $mail->addAttachment($pdfPath, 'Aufforderung Polizeiliches Führungszeugnis 2023.pdf');
} else {
  throw new Exception("PDF Datei nicht gefunden");
}

// E-Mail Format
$mail->isHTML(true);
$mail->Subject = 'Führungszeugnis übermitteln';
$mail->Body    = $message;
$mail->AltBody = "Hallo $vorname $name,\n\nSchicken Sie ihr Führungszeugnis bitte an diese E-Mail-Adresse: gewaltschutz@ecsa.de";

try {
  // Senden
  $mail->send();
  echo json_encode(["message" => "Email erfolgreich versendet"]);
} catch (Exception $e) {
  http_response_code($e->getCode() ?: 400);
  echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
