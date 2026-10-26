<?php
// ForwardAuth-Adresse für HedgeDoc: http://simple-auth-app-1/gate.php
// Traefik schickt X-Forwarded-Uri (immer vom echten Request, nicht vom Client,
// solange trustForwardHeader NICHT gesetzt ist).
require __DIR__ . '/gatelib.php';

function pad_exists(string $name): ?bool
{
    $cache = sys_get_temp_dir() . '/padgate-' . sha1($name);
    if (is_file($cache) && time() - filemtime($cache) < 30) {
        return true;                                    // nur "existiert" wird gemerkt
    }
    $base = getenv('HEDGEDOC_URL') ?: 'http://hedgedoc-app-1:3000';
    $ch = curl_init($base . '/' . rawurlencode($name) . '/info');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT        => 3,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($code === 200 || $code === 403) {               // 403 = privates Pad
        @touch($cache);
        return true;
    }
    return $code === 404 ? false : null;                // alles andere: unklar
}

$uri = $_SERVER['HTTP_X_FORWARDED_URI'] ?? '';
try {
    $needs = $uri === '' ? true : gate_needs_login($uri, 'pad_exists');
} catch (Throwable $e) {
    $needs = true;                                      // bei Fehlern lieber zu
}

if (!$needs) {
    http_response_code(200);
    exit;
}
require __DIR__ . '/index.php';                         // Anmeldung wie bisher
