fheME
=====

fheME ist eine Web-Oberfläche für Tablets und bietet

* Kalender
* Haussteuerung über FHEM (FS20 und Weitere)
* Wettervorhersage
* RSS Feed anzeige
* Einkaufsliste

fheME Anwender HowTo
====================

Voraussetzungen
---------------

* Ein Webserver mit PHP 5.3 und MySQL 5
* Ein aktueller Browser


Installation
------------

* Bitte laden Sie im [Download-Bereich](https://github.com/nemiah/fheME/downloads) die aktuelle Version herunter.
* Entpacken Sie das Archiv in ein Verzeichnis auf Ihrem Server.
* Rufen Sie das Verzeichnis im Browser auf, das System erklärt alle weiteren Schritte.


fheME Entwickler HowTo
======================

* Installieren Sie zunächst die Version für Anwender wie oben beschrieben. Das sorgt dafür, dass alle Tabellen vorhanden sind.
* git clone https://github.com/nemiah/fheME.git
* Führen Sie die SQL-Befehle aus der Datei /SQL/0.5To0.6.sql auf Ihrer Datenbank aus.
* Tragen Sie die gleichen Datenbank-Zugangsdaten wie in der Anwenderversion ein.