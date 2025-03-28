<?php
require_once "../config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo json_encode(["success" => false, "message" => "Ungültige Anfrage."]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// check if all required data is set
if (!isset($data["id"], $data["name"], $data["vorname"], $data["email"])) {
  echo json_encode(["success" => false, "message" => "Fehlende Daten."]);
  exit;
}

// optional field hauptamt (default 0)
$hauptamt = isset($data["hauptamt"]) && ($data["hauptamt"] === '1' || $data["hauptamt"] === true) ? 1 : 0;

try {

  // check if the date is valid and set either the value or NULL
  function convertToDate($date)
  {
    return !empty($date) ? date("Y-m-d H:i:s", strtotime($date)) : null;
  }

  $sql = "UPDATE gp_employees SET 
          name = :name, 
          vorname = :vorname, 
          email = :email, 
          postadresse = :postadresse,
          fz_eingetragen = :fz_eingetragen,
          fz_abgelaufen = :fz_abgelaufen,
          fz_kontrolliert = :fz_kontrolliert,
          fz_kontrolliert_am = :fz_kontrolliert_am,
          gs_eingetragen = :gs_eingetragen,
          gs_erneuert = :gs_erneuert,
          gs_kontrolliert = :gs_kontrolliert,
          us_eingetragen = :us_eingetragen,
          us_abgelaufen = :us_abgelaufen,
          us_kontrolliert = :us_kontrolliert,
          sve_eingetragen = :sve_eingetragen,
          sve_kontrolliert = :sve_kontrolliert,
          hauptamt = :hauptamt 
        WHERE id = :id";

  $stmt = $pdo->prepare($sql);

  // Execute the statement with the form data
  $stmt->execute([
    ":name" => $data["name"],
    ":vorname" => $data["vorname"],
    ":email" => $data["email"],
    ":postadresse" => $data["postadresse"],
    ":fz_eingetragen" => convertToDate($data["fz_eingetragen"]),
    ":fz_abgelaufen" => convertToDate($data["fz_abgelaufen"]),
    ":fz_kontrolliert" => $data["fz_kontrolliert"],
    ":fz_kontrolliert_am" => convertToDate($data["fz_kontrolliert_am"]),
    ":gs_eingetragen" => convertToDate($data["gs_eingetragen"]),
    ":gs_erneuert" => convertToDate($data["gs_erneuert"]),
    ":gs_kontrolliert" => $data["gs_kontrolliert"],
    ":us_eingetragen" => convertToDate($data["us_eingetragen"]),
    ":us_abgelaufen" => convertToDate($data["us_abgelaufen"]),
    ":us_kontrolliert" => $data["us_kontrolliert"],
    ":sve_eingetragen" => convertToDate($data["sve_eingetragen"]),
    ":sve_kontrolliert" => $data["sve_kontrolliert"],
    ":hauptamt" => $hauptamt,
    ":id" => $data["id"]
  ]);

  echo json_encode(["success" => true, "message" => "Mitarbeiter erfolgreich aktualisiert."]);
} catch (PDOException $e) {
  echo json_encode(["success" => false, "message" => "Fehler beim Aktualisieren des Mitarbeiters.", "error" => $e->getMessage()]);
}
