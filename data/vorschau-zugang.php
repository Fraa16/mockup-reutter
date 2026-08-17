<?php
declare(strict_types=1);

/**
 * Zugang zum Redaktionsbereich — NUR FUER DIE VORSCHAU.
 *
 * Diese Datei liegt bewusst im Repository, damit man sich das Panel auf der
 * Vercel-Vorschau ansehen kann, ohne dort erst Umgebungsvariablen einzurichten
 * (die sind an eine Umgebung gebunden und greifen erst ab dem naechsten
 * Deployment — zwei Fallstricke, die man der Oberflaeche nicht ansieht).
 *
 * Sie wird ausschliesslich auf einem Host ohne beschreibbares data/ gelesen,
 * siehe auth_nur_lesbarer_host() in app/lib/auth.php. Auf dem richtigen
 * Hosting ist data/ beschreibbar — dort gilt allein data/users.php, angelegt
 * ueber `php bin/passwort-setzen.php`. Der Zugang hier kann also nicht
 * versehentlich auf der echten Domain wirksam werden.
 *
 * Trotzdem: VOR DEM LIVEGANG LOESCHEN. Steht so auch in
 * docs/offene-punkte.md.
 *
 * Gespeichert ist ein bcrypt-Hash mit Kostenfaktor 12, kein Klartext. Das
 * Passwort ist zufaellig erzeugt und ausschliesslich fuer diese Vorschau
 * gedacht — es wird nirgends sonst benutzt.
 */

return [
    'vorschau' => [
        'passwort' => '$2y$12$ndqLKlYxcTbOKrLr0VzvvuxPRBC2fJoM3p3dpz.JKFnUvulCVmw6W',
        'name'     => 'Vorschau',
    ],
];
