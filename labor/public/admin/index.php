<?php
// Default admin page - redirect to login
$_SERVER['REQUEST_URI'] = '/admin/login';
require __DIR__ . '/../index.php';