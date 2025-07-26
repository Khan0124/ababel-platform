<?php
// Direct access to admin login without complex routing
// Change working directory so relative includes work
chdir(__DIR__ . '/../../admin');
require __DIR__ . '/../../admin/login.php';