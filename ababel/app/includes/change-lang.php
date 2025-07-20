<?php
session_start();
require_once __DIR__ . '/Translator.php';


if (isset($_GET['lang'])) {
    Translator::setLocale($_GET['lang']);
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
exit;
?>