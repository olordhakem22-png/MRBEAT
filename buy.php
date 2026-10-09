<?php
require __DIR__ . '/app/bootstrap.php';
header('Location: ' . mr_config('app.base_url') . '/index.php', true, 302);
exit;