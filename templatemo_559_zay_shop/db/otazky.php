<?php
require_once('../classes/contactClass.php');
use formular\ContactClass;

// Виводимо стилі inline
echo <<<HTML
<style>
  .faq-container {
      width: 90%;
      margin: 40px auto;
      padding: 20px;
      background: #f9f9f9;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  .faq-item {
      margin-bottom: 20px;
      padding: 15px;
      background: #fff;
      border-radius: 6px;
      border-left: 4px solid #28a745;
  }
  .faq-question h4 {
      margin: 0 0 10px;
      font-size: 1.2em;
      color: #333;
  }
  .faq-answer p {
      margin: 4px 0;
      font-size: 0.95em;
      color: #555;
  }
  .faq-answer strong {
      color: #222;
  }
  .container-name {
    text-align : center;
  }
</style>
HTML;

// Отримуємо дані
$kontakt = new ContactClass();
$otazky = $kontakt->getOtazkyZUdaje();

// Виводимо FAQ
echo '<div class="faq-container">';
echo '<div class="container-name">';
echo '<h1> FAQ by our users </h1> </div>';
foreach ($otazky as $zaznam) {
    echo '<div class="faq-item">';
      echo '<div class="faq-question">';
        echo '<h4>' . htmlspecialchars($zaznam['sprava']) . '</h4>';
      echo '</div>';
      echo '<div class="faq-answer">';
        echo '<p><strong>Meno:</strong> ' . htmlspecialchars($zaznam['meno']) . '</p>';
        echo '<p><strong>Objekt:</strong> ' . htmlspecialchars($zaznam['objekt']) . '</p>';
        echo '<h5><strong>Odpoveď:</strong> ' . htmlspecialchars($zaznam['odpoved']) . '</h5>';
      echo '</div>';
    echo '</div>';
}
echo '</div>';
?>
