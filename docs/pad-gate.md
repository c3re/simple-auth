# gate.php: Anmeldung nur beim Anlegen neuer HedgeDoc-Pads

`gate.php` ist eine zweite ForwardAuth-Adresse neben `index.php`. Sie lässt Lesen und Bearbeiten bestehender Pads ohne Anmeldung durch und verlangt die Anmeldung (wie `index.php`) nur dort, wo HedgeDoc 1.x ein neues Pad anlegen würde:

- `/new` (jede Methode), `/uploadimage`
- ein Pad-Name, den es noch nicht gibt (`GET /<name>` legt bei `allowFreeURL` ein Pad an)

Ob ein Pad existiert, fragt `gate.php` bei HedgeDoc über `GET /<name>/info` ab. Das legt nie etwas an (404, wenn es das Pad nicht gibt, 403 bei privaten Pads, die als vorhanden gelten). Bei Fehlern oder unklaren Antworten bleibt es zu.

## Einbinden (Traefik)

```
traefik.http.middlewares.HedgeDoc-auth.forwardauth.address=http://simple-auth-app-1/gate.php
```

`trustForwardHeader` darf **nicht** gesetzt sein, sonst könnte ein Client `X-Forwarded-Uri` fälschen und die Prüfung umgehen. Beide Container müssen im selben Docker-Netz hängen (bei uns `traefik_proxy`).

## Umgebung

| Variable | Standard | Zweck |
|---|---|---|
| `HEDGEDOC_URL` | `http://hedgedoc-app-1:3000` | interne Adresse von HedgeDoc |
| `AUTH_USER`, `AUTH_PASSWORD`, `AUTH_BYPASS_KEYS` | – | wie bei `index.php` |

## Reservierte Namen

Pfade wie `/s/…`, `/p/…`, `/socket.io`, `/build/…`, `/css/…`, `/status`, `/config` sind keine Pads und gehen ohne Prüfung durch. Die Liste steht in `src/gatelib.php` (`GATE_RESERVED`) und wurde aus HedgeDoc 1.10.0 übernommen. Bei neuen HedgeDoc-Versionen abgleichen.

## Tests

```
docker run --rm -v "$PWD":/app -w /app php:8.3-cli php tests/gate_test.php
docker run --rm -v "$PWD":/app -w /app php:8.3-cli bash tests/integration.sh
```

`gate_test.php` prüft die Entscheidungen mit einem Ersatz für die Existenzprüfung, `integration.sh` startet `gate.php` und einen Ersatz für HedgeDoc.
