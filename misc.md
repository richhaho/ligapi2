Logging:

1) @Log zur Entity property
2) $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $keyy));



Deployment: messenger:consume neu starten



Kommissionen:
Entweder Nutzer, Projekt oder Freitext zugeordnet

Können aus Materialliste einzeln befüllt werden
Können aus Text Input befüllt werden, jede Zeile Suchbegriff, Doppelpunkt, Anzahl

Kommissionen-Übersicht in der App kann nach Nutzer/Projekt/Freitext gefiltert werden
Bei Klick auf Eintrag wird entweder die Menge aus einem vorhandenen Lagerort transferiert falls Bestand vorhanden oder gefragt, ob das Material auf "zu bestellen" gesetzt werden soll

Kommissionen können einem Lagerort zugewiesen werden

Kommissionen können Materialien, Werkzeuge oder Schlüssel beinhalten

Kommissionen können auf eine neue Kommission kopiert werden

Consignment:
id
company
createdAt
project?
user?
name?
commissionLinks
note?

ConsignmentItem:
id
company
createdAt
commission
materialId?
toolId?
keyyId?
amount?
