# GoveeDevice
   Diese Instanz bildet das Gerät in IP-Symcon ab.
   Über diese INstanz kann das Gerät geschaltet werden, ebenso wird der Status hier widerspiegelt.
     
   ## Inhaltverzeichnis
- [GoveeDevice](#shellybulb)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
  - [3. Aktionen](#3-aktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   Aktiv | Hier kann die Instanz deaktiviert werden.
   Aktualisierungsintervall | Hier wird der Intervall eingegeben, welcher zum Abfragen der Statuswerte genutzt werden soll
      
   ## 2. Funktionen

   ```php
   RequestAction($VariablenID, $Value);
   ```

   Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.
   
   **Beispiel:**
   Variable ID Status: 12345
   ```php
   RequestAction(12345, true); //Einschalten
   RequestAction(12345, false); //Auschalten
   ```

   Variable ID Helligkeit: 56789
   ```php
   RequestAction(56789, 50); //auf 50% setzen
   RequestAction(56789, 40); //auf 40% setzen
   ```

   Variable ID Farbtemperatur: 76543
   ```php
   RequestAction(76543, 2700); //auf 2700 K setzen
   RequestAction(76543, 2900); //auf 2900 K setzen
   ```

   Variable ID Farbe: 14725
   ```php
   RequestAction(14725, 0xff0000); //Farbe Rot
   ```