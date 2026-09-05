<?php
declare(strict_types=1);

/**
 * VORLAGE — so nicht benutzen.
 *
 * Kopieren nach app/config/zugangsdaten.php und ausfuellen. Die ausgefuellte
 * Fassung gehoert NICHT ins Git; .gitignore faengt sie bereits ab. Sie liegt
 * ausserhalb der Dokumentwurzel und darf dort auch bleiben.
 *
 * Fehlt die Datei, geht keine Mail raus. Kaputt geht dabei nichts: Die Anfrage
 * ist zu diesem Zeitpunkt schon in data/anfragen/ abgelegt und steht im Panel
 * unter „Anfragen von der Website". Nur die Benachrichtigung per Mail bleibt
 * aus, und im Fehlerprotokoll steht, warum (siehe app/lib/mail.php).
 *
 * Die Werte unten stehen auf den Standardeinstellungen von IONOS. Wo sie
 * herkommen: Kundenmenue → E-Mail → das Postfach auswaehlen → dort stehen
 * Postausgangsserver, Port und der Benutzername.
 */

return [
    'smtp' => [
        // Postausgangsserver des Hosters. Bei IONOS immer dieser.
        'host' => 'smtp.ionos.de',

        // 587 mit STARTTLS ist der Normalfall. 465 geht auch, dann unten
        // 'ssl' eintragen — das Skript stellt bei 465 von selbst darauf um.
        'port' => 587,

        // 'tls' (Verbindung wird nachtraeglich verschluesselt, Port 587) oder
        // 'ssl' (von Anfang an verschluesselt, Port 465). Weglassen laesst das
        // Skript anhand des Ports entscheiden.
        'verschluesselung' => 'tls',

        // Die vollstaendige Mailadresse des Postfachs, nicht nur der Teil vor
        // dem @. Das ist der haeufigste Grund, warum die Anmeldung scheitert.
        'benutzer' => 'website@smartrepair-reutter.de',

        // Das Passwort des Postfachs — nicht das Passwort des IONOS-Kontos.
        'passwort' => '',

        // Absenderadresse der Formularmails. Muss zum Postfach oben passen,
        // sonst weist der Server sie ab. Weglassen nimmt 'benutzer'.
        //
        // Bewusst ein eigenes Postfach und nicht info@: Formularmails laufen
        // automatisch, und wenn dabei etwas klemmt, soll das nicht das
        // Postfach beruehren, ueber das der Betrieb seine Kundschaft
        // erreicht.
        'absender' => 'website@smartrepair-reutter.de',

        // Steht im Posteingang als Absendername.
        'absendername' => 'Smartrepair Reutter — Website',

        // Sekunden, die auf den Server gewartet wird. Zehn reichen; hoeher
        // heisst nur, dass der Besucher laenger auf einer haengenden Seite
        // sitzt, wenn der Mailserver Probleme hat.
        'zeitlimit' => 10,
    ],
];
