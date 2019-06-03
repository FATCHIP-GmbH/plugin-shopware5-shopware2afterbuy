# Funktionsbeschreibung

## führendes System Shopware
Artikel und Kategorien (noch nicht übermittelt oder geändert seit letztem Export) werden von Shopware an Afterbuy übertragen.
Bestellungen (seit letztem Import) werden aus Afterbuy importiert.
Der Status von abgeschlossenen Bestellungen wird vor jedem Bestellimport an Afterbuy übermittelt.

## führendes System Afterbuy
Artikel und Kategorien werden von Afterbuy importiert.
Im Shop getätigte Bestellungen werden an Afterbuy übermittelt.

# Voraussetzungen
- php-curl
- PHP 7.1
- Shopware 5.3

# Beschränkungen
- max. 250 Varianten je Artikel

# Anmerkungen
## führendes System
Die Konfiguration bezüglich des führenden Systems sollte nicht im Betrieb geändert werden. Es werden stets die IDs des führenden Systems verwendet, was bei Änderung dazu führen kann, dass Duplikate erstellt werden.

Das nicht führende System sollte vor Beginn der Synchronisation geleert werden.

## Cronjobs & Commands
Diese Erweiterung stellt 2 Shopware-Cronjobs und alternativ 2 Commands für den Updateprozess bereit.

### Cronjobs
Die Cronjobs (Update Products & Update Orders) müssen nach Installation konfiguriert und aktiviert werden.

### Commands
Anstelle der Cronjobs können die Commands verwendet werden.

    php bin/console Afterbuy:Update:Products
    php bin/console Afterbuy:Update:Orders
    
# Installation

## Konfiguration

Die Konfiguration erfolgt über den Plugin-Manager nach Installation des Plugins.
    
## Cronjobs aktivieren

Die bereitgestellten Cronjobs müssen nach vorgenommener Konfiguration aktiviert und ggf. konfiguriert werden. (Einstellungen -> Grundeinstellungen -> Cronjobs)

    Sync Afterbuy Orders
    Sync Afterbuy Products
       
