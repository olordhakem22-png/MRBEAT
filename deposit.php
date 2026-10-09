<?php
require __DIR__ . '/app/bootstrap.php';
header('Location: ' . mr_config('app.base_url') . '/account.php?tab=deposits', true, 302);
exit;