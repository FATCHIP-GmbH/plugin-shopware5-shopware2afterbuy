Hi Markus,

habe nun den Testcse und die Testdaten so angepasst, dass wieder halbwegs vernünftig getestet werden kann. Es gibt aber noch eine Schwachstelle: Bei AB kann man direkt am Produkt ein Großes und ein kleines Bild speichern. Zusätzlich kann man in der Produktgalerie noch weitere Bilder speichern. Die Bilder, die direkt gespeichert werden, werden in der .csv Datei für die Produkte importiert. Die Galeriebilder, brauchen eine eigene Datei. Leider findet die Zuordnung der Bilder zu ihren Produkten über die ID statt und diese wird nach jedem Importieren der Produkte neu vergeben. SW macht diese Zuordnung über die Artikelnummer (Bestellnummer), welche ja nicht bei jedem Import neu vergeben wird.
Ich sehe erst mal keine Möglichkeit, die Galeriebilder auf diesem Wege zu importieren und korrekt zuzuordnen. Vielleicht fällt dir ja noch was schlaues dazu ein?

LG