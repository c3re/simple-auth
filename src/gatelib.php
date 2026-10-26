<?php
// Entscheidet, ob eine Anfrage an HedgeDoc eine Anmeldung braucht.
// Nur das Anlegen neuer Pads braucht sie, Lesen und Bearbeiten sind frei.

// Erste Pfadsegmente, die keine Pads sind (klein geschrieben).
const GATE_RESERVED = [
    's', 'p', 'socket.io',
    'robots.txt', 'favicon.ico', 'default.md', 'api', 'build', 'css', 'docs', 'fonts',
    'icons', 'js', 'uploads', 'vendor', 'views', 'banner',
    'status', '_health', 'metrics', 'config', 'temp', 'me', 'user', 'history',
    'logout', 'auth', '403', '404', '500',
];

/**
 * true  = Anmeldung nötig (Anlegen)
 * false = frei
 *
 * $padExists($name): true = Pad existiert, false = existiert nicht, null = unklar
 */
function gate_needs_login(string $uri, callable $padExists): bool
{
    $path = strtok($uri, '?#');
    if ($path === false || $path === '') {
        return false;                                   // Startseite
    }
    $segments = array_values(array_filter(explode('/', $path), 'strlen'));
    if (!$segments) {
        return false;
    }
    foreach ($segments as $s) {
        if ($s === '.' || $s === '..') {
            return true;                                // ungewöhnlicher Pfad, im Zweifel zu
        }
    }

    $first = rawurldecode($segments[0]);
    if (preg_match('/[\x00-\x1f\x7f\/\\\\]/', $first)) {
        return true;                                    // Steuerzeichen oder Schrägstrich im Namen
    }
    $lower = strtolower($first);                        // Express vergleicht Routen ohne Groß/Klein

    if ($lower === 'new' || $lower === 'uploadimage') {
        return true;
    }
    if (in_array($lower, GATE_RESERVED, true)) {
        return false;
    }
    if (count($segments) !== 1) {
        return false;                                   // /<name>/<aktion> legt nie etwas an
    }
    // Ein Segment: Pad-Name. Methode egal, HedgeDoc nimmt `?_method=GET` an.
    return $padExists($first) !== true;
}
