<?php
// Mini-Ersatz für HedgeDoc: antwortet auf /<name>/info wie die echte Anwendung.
$p = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$name = explode('/', $p)[0];
if (substr($p, -5) !== '/info') { http_response_code(500); exit; }
if ($name === 'existing') { header('Content-Type: application/json'); echo '{"title":"x"}'; exit; }
if ($name === 'private')  { http_response_code(403); exit; }
http_response_code(404);
