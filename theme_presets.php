<?php

if (!function_exists('mr_hex_to_rgb')) {
function mr_hex_to_rgb(string $hex): array {
$hex = trim($hex);
$hex = ltrim($hex, '#');

if (strlen($hex) === 3) {
$hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
}

if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
return [109, 94, 252];
}

return [
hexdec(substr($hex, 0, 2)),
hexdec(substr($hex, 2, 2)),
hexdec(substr($hex, 4, 2)),
];
}
}

if (!function_exists('mr_rgba')) {
function mr_rgba(string $hex, float $alpha): string {
[$r, $g, $b] = mr_hex_to_rgb($hex);
$alpha = max(0, min(1, $alpha));
return "rgba({$r},{$g},{$b},{$alpha})";
}
}

if (!function_exists('mr_theme_setting_get')) {
function mr_theme_setting_get(PDO $pdo, string $k, string $default = ''): string {
try {
$st = $pdo->prepare("SELECT v FROM settings WHERE k=? LIMIT 1");
$st->execute([$k]);
$v = $st->fetchColumn();
if ($v === false) {
return $default;
}
return (string)$v;
} catch (Throwable $e) {
return $default;
}
}
}

if (!function_exists('mr_theme_presets')) {
function mr_theme_presets(): array {
return [
'account_modern' => [
'name' => 'Account Modern',
'mode' => 'dark',
'bg_dark' => '#0b1020',
'card_dark' => '#121a2b',
'primary_dark' => '#6d5efc',
'text_dark' => '#eef2ff',
'g1_dark' => '#8b5cf6',
'g2_dark' => '#06b6d4',
'bg_light' => '#f6f8ff',
'card_light' => '#ffffff',
'primary_light' => '#6d5efc',
'text_light' => '#111827',
'g1_light' => '#8b5cf6',
'g2_light' => '#06b6d4',
'radius' => 22,
'shadow' => 30,
'border' => 1,
'glass' => 0,
],

'clean_light' => [
'name' => 'Clean Light',
'mode' => 'light',
'bg_dark' => '#051424',
'card_dark' => '#0b1b2b',
'primary_dark' => '#3b82f6',
'text_dark' => '#ffffff',
'g1_dark' => '#0f172a',
'g2_dark' => '#1d4ed8',
'bg_light' => '#f7f8fb',
'card_light' => '#ffffff',
'primary_light' => '#2563eb',
'text_light' => '#111827',
'g1_light' => '#dbeafe',
'g2_light' => '#f8fafc',
'radius' => 14,
'shadow' => 18,
'border' => 1,
'glass' => 0,
],

'glass_light' => [
'name' => 'Glass Light',
'mode' => 'light',
'bg_dark' => '#07111f',
'card_dark' => '#0d1726',
'primary_dark' => '#60a5fa',
'text_dark' => '#eef6ff',
'g1_dark' => '#0f172a',
'g2_dark' => '#2563eb',
'bg_light' => '#eef4ff',
'card_light' => '#ffffff',
'primary_light' => '#3b82f6',
'text_light' => '#0f172a',
'g1_light' => '#bfdbfe',
'g2_light' => '#eff6ff',
'radius' => 20,
'shadow' => 24,
'border' => 1,
'glass' => 1,
],

'violet_modern' => [
'name' => 'Violet Modern',
'mode' => 'dark',
'bg_dark' => '#120b1d',
'card_dark' => '#1c1330',
'primary_dark' => '#a855f7',
'text_dark' => '#f5f3ff',
'g1_dark' => '#7c3aed',
'g2_dark' => '#c084fc',
'bg_light' => '#faf5ff',
'card_light' => '#ffffff',
'primary_light' => '#7c3aed',
'text_light' => '#2e1065',
'g1_light' => '#ede9fe',
'g2_light' => '#ffffff',
'radius' => 22,
'shadow' => 24,
'border' => 1,
'glass' => 0,
],

'emerald_modern' => [
'name' => 'Emerald Modern',
'mode' => 'dark',
'bg_dark' => '#081510',
'card_dark' => '#10241b',
'primary_dark' => '#10b981',
'text_dark' => '#ecfdf5',
'g1_dark' => '#059669',
'g2_dark' => '#34d399',
'bg_light' => '#ecfdf5',
'card_light' => '#ffffff',
'primary_light' => '#059669',
'text_light' => '#064e3b',
'g1_light' => '#a7f3d0',
'g2_light' => '#ffffff',
'radius' => 20,
'shadow' => 22,
'border' => 1,
'glass' => 0,
],

'sunset_modern' => [
'name' => 'Sunset Modern',
'mode' => 'dark',
'bg_dark' => '#1a0d0b',
'card_dark' => '#2a1612',
'primary_dark' => '#f97316',
'text_dark' => '#fff7ed',
'g1_dark' => '#ef4444',
'g2_dark' => '#f59e0b',
'bg_light' => '#fff7ed',
'card_light' => '#ffffff',
'primary_light' => '#ea580c',
'text_light' => '#7c2d12',
'g1_light' => '#fdba74',
'g2_light' => '#ffffff',
'radius' => 20,
'shadow' => 22,
'border' => 1,
'glass' => 0,
],
];
}
}

if (!function_exists('mr_active_theme_name')) {
function mr_active_theme_name($pdo = null): string {
if (!$pdo instanceof PDO) {
return 'account_modern';
}

$preset = mr_theme_setting_get($pdo, 'theme_preset', 'account_modern');
$presets = mr_theme_presets();

if (!isset($presets[$preset])) {
$preset = 'account_modern';
}

return $preset;
}
}

if (!function_exists('mr_theme_tokens')) {
function mr_theme_tokens($pdo = null): array {
$presets = mr_theme_presets();

$preset_key = 'account_modern';
$theme_mode = 'dark';

if ($pdo instanceof PDO) {
$preset_key = mr_theme_setting_get($pdo, 'theme_preset', 'account_modern');
}

if (!isset($presets[$preset_key])) {
$preset_key = 'account_modern';
}

$preset = $presets[$preset_key];

if ($pdo instanceof PDO) {
$theme_mode = mr_theme_setting_get($pdo, 'theme_mode', $preset['mode']);
} else {
$theme_mode = $preset['mode'];
}

if ($theme_mode !== 'light' && $theme_mode !== 'dark') {
$theme_mode = $preset['mode'];
}

$ui_bg = $theme_mode === 'light' ? $preset['bg_light'] : $preset['bg_dark'];
$ui_card = $theme_mode === 'light' ? $preset['card_light'] : $preset['card_dark'];
$ui_primary = $theme_mode === 'light' ? $preset['primary_light'] : $preset['primary_dark'];
$ui_text = $theme_mode === 'light' ? $preset['text_light'] : $preset['text_dark'];
$ui_g1 = $theme_mode === 'light' ? $preset['g1_light'] : $preset['g1_dark'];
$ui_g2 = $theme_mode === 'light' ? $preset['g2_light'] : $preset['g2_dark'];

$theme_radius = (int)$preset['radius'];
$theme_shadow = (int)$preset['shadow'];
$theme_border = (int)$preset['border'];
$theme_glass = (int)$preset['glass'];

return [
'name' => $preset['name'],
'preset_key' => $preset_key,
'theme_mode' => $theme_mode,

'theme_bg' => $preset['bg_dark'],
'theme_card' => $preset['card_dark'],
'theme_primary' => $preset['primary_dark'],
'theme_text' => $preset['text_dark'],

'theme_bg_light' => $preset['bg_light'],
'theme_card_light' => $preset['card_light'],
'theme_primary_light' => $preset['primary_light'],
'theme_text_light' => $preset['text_light'],

'theme_gradient_from' => $preset['g1_dark'],
'theme_gradient_to' => $preset['g2_dark'],
'theme_gradient_from_light' => $preset['g1_light'],
'theme_gradient_to_light' => $preset['g2_light'],

'ui_bg' => $ui_bg,
'ui_card' => $ui_card,
'ui_primary' => $ui_primary,
'ui_text' => $ui_text,
'ui_g1' => $ui_g1,
'ui_g2' => $ui_g2,

'theme_radius' => $theme_radius,
'theme_shadow' => $theme_shadow,
'theme_border' => $theme_border,
'theme_glass' => $theme_glass,

'primary_08' => mr_rgba($ui_primary, 0.08),
'primary_12' => mr_rgba($ui_primary, 0.12),
'primary_18' => mr_rgba($ui_primary, 0.18),
'primary_24' => mr_rgba($ui_primary, 0.24),

'text_08' => mr_rgba($ui_text, 0.08),
'text_12' => mr_rgba($ui_text, 0.12),
'text_18' => mr_rgba($ui_text, 0.18),
'text_72' => mr_rgba($ui_text, 0.72),

'shadow_md' => '0 10px '.max(12, $theme_shadow - 4).'px rgba(0,0,0,.14)',
'shadow_lg' => '0 12px '.$theme_shadow.'px rgba(0,0,0,.18)',
'blur' => $theme_glass ? 'blur(14px)' : 'blur(0px)',
'surface_mix' => $theme_glass
? 'linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.04))'
: 'linear-gradient(180deg, color-mix(in srgb, var(--card) 96%, #fff 4%), color-mix(in srgb, var(--card) 86%, #000 14%))',
];
}
}

if (!function_exists('mr_theme_root_css')) {
function mr_theme_root_css(array $theme): string {
return ':root{' .
'--bg:' . $theme['ui_bg'] . ';' .
'--card:' . $theme['ui_card'] . ';' .
'--primary:' . $theme['ui_primary'] . ';' .
'--text:' . $theme['ui_text'] . ';' .
'--g1:' . $theme['ui_g1'] . ';' .
'--g2:' . $theme['ui_g2'] . ';' .
'--radius:' . (int)$theme['theme_radius'] . 'px;' .
'--border-size:' . (int)$theme['theme_border'] . 'px;' .
'--shadow-lg:' . $theme['shadow_lg'] . ';' .
'--shadow-md:' . $theme['shadow_md'] . ';' .
'--primary-08:' . $theme['primary_08'] . ';' .
'--primary-12:' . $theme['primary_12'] . ';' .
'--primary-18:' . $theme['primary_18'] . ';' .
'--primary-24:' . $theme['primary_24'] . ';' .
'--text-08:' . $theme['text_08'] . ';' .
'--text-12:' . $theme['text_12'] . ';' .
'--text-18:' . $theme['text_18'] . ';' .
'--text-72:' . $theme['text_72'] . ';' .
'--glass-blur:' . $theme['blur'] . ';' .
'--surface:' . $theme['surface_mix'] . ';' .
'}';
}
}
