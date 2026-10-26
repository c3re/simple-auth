set -u
export AUTH_USER=u AUTH_PASSWORD=p AUTH_BYPASS_KEYS=geheim
php -S 127.0.0.1:3000 /app/tests/stub_hedgedoc.php >/dev/null 2>&1 &
STUB=$!
HEDGEDOC_URL=http://127.0.0.1:3000 php -S 127.0.0.1:8080 -t /app/src >/dev/null 2>&1 &
sleep 1
chk() { # Beschreibung, erwarteter Status, Header...
  d="$1"; want="$2"; shift 2
  got=$(curl -s -o /tmp/o -w '%{http_code}' "$@" http://127.0.0.1:8080/gate.php)
  [ "$got" = "$want" ] && r=ok || r=FEHLER
  printf '%-7s %-52s erwartet %s, erhalten %s\n' "$r" "$d" "$want" "$got"
}
chk "Start /"                           200 -H "X-Forwarded-Uri: /"
chk "bestehendes Pad lesen"             200 -H "X-Forwarded-Uri: /existing"
chk "privates Pad lesen"                200 -H "X-Forwarded-Uri: /private"
chk "veröffentlichte Ansicht /s/abc"    200 -H "X-Forwarded-Uri: /s/abc"
chk "neuer Name ohne Anmeldung"         403 -H "X-Forwarded-Uri: /neuer-name"
chk "Scanner /wp-login.php"             403 -H "X-Forwarded-Uri: /wp-login.php"
chk "/new ohne Anmeldung"               403 -H "X-Forwarded-Uri: /new"
chk "/NEW ohne Anmeldung"               403 -H "X-Forwarded-Uri: /NEW"
chk "Upload ohne Anmeldung"             403 -H "X-Forwarded-Uri: /uploadimage"
chk "ohne X-Forwarded-Uri"              403
chk "neuer Name mit Bypass-Schlüssel"   200 -H "X-Forwarded-Uri: /neuer-name" -H "X-Auth-Key: geheim"
chk "falscher Bypass-Schlüssel"         403 -H "X-Forwarded-Uri: /neuer-name" -H "X-Auth-Key: falsch"
# HedgeDoc nicht erreichbar -> im Zweifel zu
kill $STUB; sleep 1
chk "HedgeDoc aus, unbekannter Name"    403 -H "X-Forwarded-Uri: /irgendwas"
chk "HedgeDoc aus, /s/abc (reserviert)" 200 -H "X-Forwarded-Uri: /s/abc"
