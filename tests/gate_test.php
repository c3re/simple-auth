<?php
require __DIR__ . '/../src/gatelib.php';

$calls = 0;
$exists = function (string $name) use (&$calls) {
    $calls++;
    return ['existing' => true, 'private' => true, 'missing' => false, 'boom' => null][$name] ?? false;
};

// [Adresse, erwartet "Anmeldung nötig"]
$cases = [
    ['/', false],
    ['', false],
    ['/existing', false],
    ['/existing/', false],
    ['/existing/info', false],
    ['/missing/info', false],
    ['/missing', true],
    ['/missing/', true],
    ['/missing?_method=GET', true],
    ['/wp-login.php', true],
    ['/boom', true],
    ['/new', true],
    ['/NEW', true],
    ['/New/', true],
    ['/new/mein-name', true],
    ['//new', true],
    ['/%6eew', true],
    ['/%4Eew', true],
    ['/uploadimage', true],
    ['/UploadImage', true],
    ['/./new', true],
    ['/../missing', true],
    ['/a%2Fb', true],
    ['/%00', true],
    ['/s/abc', false],
    ['/s/missing', false],
    ['/p/abc', false],
    ['/socket.io/?EIO=4&transport=websocket', false],
    ['/build/app.js', false],
    ['/css/x.css', false],
    ['/js/x.js', false],
    ['/robots.txt', false],
    ['/favicon.ico', false],
    ['/status', false],
    ['/config', false],
    ['/uploads/bild.png', false],
    ['/private', false],
];
$fail = 0;
foreach ($cases as [$uri, $want]) {
    $got = gate_needs_login($uri, $exists);
    if ($got !== $want) {
        $fail++;
        echo "FEHLER  $uri  erwartet=" . var_export($want, true) . " erhalten=" . var_export($got, true) . "\n";
    }
}
// Reservierte Namen dürfen HedgeDoc nicht abfragen
$calls = 0;
foreach (['/s/x', '/build/a', '/status', '/new', '/robots.txt'] as $u) gate_needs_login($u, $exists);
if ($calls !== 0) { $fail++; echo "FEHLER  reservierte Namen lösen $calls Abfragen aus\n"; }

echo count($cases) . " Fälle geprüft, $fail Fehler\n";
exit($fail ? 1 : 0);
