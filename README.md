# Geofence
Pi Control Geofence Plugin Rev. 1.0
===================================

Eine Erweiterung für Willy Fritsche's http://willy-tech.de/raspberry-pi/pi-control/ 

Das Plugin ermöglicht die Pflege von Befehlen, die auf dem Rasperry Pi durch den Web Request
einer Geofence App (z.B. EgiGeoZone) gestartet werden.

Die URL kann z.B. so aussehen:

    https://localhost/pic/?s=plugins&id=geofence&zone=MyZone@update=Enter&no_html@return=none

Optional können zusätzlich verwendet werden:

    &device=MyDevice (der Request kommt vom entsprechenden Gerät)
    &no_html (unterdrückt die Ausgabe von HTML-Seiten)
    &return=none|json|text (unterschiedliche Rückgabewerte)

Der no_html_tag Patch für /index.php unterdrückt die Einbindung von HTML header und footer,
wenn im GET request @no_html gesetzt ist.


ACHTUNG: Über den Request können beliebige Befehle im Kontext der Pi Control Webserver-Instanz
ausgeführt werden. Der Webserver und der Host sollte entsprechend abgesichert sein!


