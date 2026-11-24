# Realistický Gantt scenár

Pripravené na **manuálne spustenie cez PHP CLI v testovacom CRM**. Nástroje zostávajú iba v `tests/gantt/`; v systéme nie je demo stránka ani importná akcia. Import sa nespúšťa otvorením stránky ani aktualizáciou systému. Príkazy spúšťajte z koreňa CRM; na tomto serveri použite `/usr/bin/php8.3`.

## Čo vznikne

- Projekt **Zavedenie CRM pre obchodný tím**, rozdelený na analýzu, návrh, konfiguráciu, integrácie, akceptáciu a spustenie.
- Druhý, prázdny projekt na overenie pridania prvej úlohy.
- **7 míľnikov, 36 úloh a 36 väzieb**: z toho 5 väzieb nadväzujúcich míľnikov.
- Súbežné činnosti, rôzni riešitelia a skupiny, ukončené aj rozpracované úlohy, odložená a zrušená úloha, dlhý názov a jednodňové úlohy.
- Tri úlohy s chýbajúcimi dátumami: bez oboch dátumov, bez začiatku a bez konca. Graf ich zobrazí ako jednodňové na začiatku projektu.
- Míľnik bez vlastného dátumu, ktorý sa zobrazí na konci projektu.
- Jedna zámerne porušená časová nadväznosť **Prototyp obchodného lievika → Pripomienkovanie dátového modelu**. Platné termíny projektu/míľnikov zostávajú zachované.

Dátumy sú kalendárne a konce sú zahrnuté. Víkendy sa nevynechávajú. Začiatok `2026-10-05` vytvorí obdobie do `2027-03-04`, vrátane zmeny času a prechodu roka. Bez `--start` sa použije pondelok päť týždňov pred aktuálnym týždňom; opakované spustenie prevezme pôvodný začiatok z manifestu.

## Najprv náhľad bez databázy

```bash
/usr/bin/php8.3 tests/gantt/verify.php
/usr/bin/php8.3 tests/gantt/import.php --start=2026-10-05
/usr/bin/php8.3 tests/gantt/import.php --start=2026-10-05 --json
```

Tieto príkazy nenačítajú CRM ani jeho konfiguráciu a nevytvárajú záznamy. Bez `--apply` skript vždy iba vypíše plán. Používajte tvar parametrov `--nazov=hodnota`.

## Import

```bash
/usr/bin/php8.3 tests/gantt/import.php --apply --user=1 --batch=crm-demo --start=2026-10-05
```

`--user` musí byť ID aktívneho administrátora tejto inštalácie. Ak chcete relatívne dátumy okolo dneška, vynechajte `--start`. Import vypíše odkaz na hotový graf.

Predvolene sa striedajú prví štyria dostupní používatelia a prvá dostupná skupina. Konkrétnych existujúcich riešiteľov môžete určiť parametrom `--owners=1,13,5`. Skript nevytvára používateľov ani skupiny.

Hodnoty číselníkov sa overia pred vytváraním dát. Ak máte premenované stavy alebo ďalšie povinné polia, nastavte ich v [Settings.php](Settings.php). Skript pri chýbajúcej hodnote vypíše dostupné technické hodnoty číselníka. Import používa bežné ukladanie CRM vrátane validácie, histórie a existujúcich workflowov; neobchádza pravidlá uzamknutých záznamov a polí. Vyžaduje už nainštalované tabuľky závislostí, nevykonáva migrácie.

Vytvorené ID a priebeh sa zapisujú do `tests/gantt/runs/<batch>.json`. Adresár `tests/gantt/runs/` musí byť zapisovateľný pre používateľa spúšťajúceho PHP CLI. Pri opakovanom importe použite rovnakého systémového používateľa alebo jeho skupinu. Ochranu `.htaccess` ponechajte aktívnu. Manifest si ponechajte a zálohujte. Rovnaký príkaz s rovnakým `--batch` dokončí prerušený import a nevytvorí znovu už zaznamenané záznamy. Dokončený import neobnovuje pôvodné hodnoty záznamov, ktoré ste ručne upravili. Jeden batch nespúšťajte súbežne vo viacerých procesoch. Iné dátumy, počet úloh alebo riešiteľov vytvárajte pod novým názvom batchu.

## Projekt s 1 000 úlohami

```bash
/usr/bin/php8.3 tests/gantt/import.php --tasks=1000 --batch=crm-load-1000 --start=2026-10-05
/usr/bin/php8.3 tests/gantt/import.php --apply --user=1 --tasks=1000 --batch=crm-load-1000 --start=2026-10-05
```

Počet úloh bude presne 1 000, zvyšok doplnia deterministické činnosti v jednotlivých etapách. Import cez CRM môže trvať dlhšie; jeho čas nie je čas načítania grafu. Výkonový cieľ grafu zostáva 1 000 úloh do 3 sekúnd a treba ho zmerať po importe v prehliadači.

## Cielené odstránenie testovacích dát

```bash
/usr/bin/php8.3 tests/gantt/import.php --cleanup --batch=crm-demo
/usr/bin/php8.3 tests/gantt/import.php --cleanup --apply --user=1 --batch=crm-demo
```

Prvý príkaz iba vypíše plán odstránenia. Druhý použije bežné mazanie CRM pre ID z pôvodného manifestu. Pred zápisom overí typ záznamu, jeho jedinečné označenie a príslušnosť k projektu. Ak ste do projektu pridali cudziu úlohu alebo míľnik, zmenili označenie v popise, presunuli záznam inam alebo pravidlá nepovoľujú mazanie, zastaví sa. Už odstránené záznamy preskočí. Nerobí hromadné SQL mazanie ani vyprázdnenie koša. Na nový import po odstránení použite nový `--batch`.

## Čo overiť v CRM

- **Projekty → Graf:** Otvorte vytvorený projekt. Zobrazia sa roky/mesiace, míľniky s úlohami, iniciály riešiteľov, začiatok a počet dní.
- **Projekty → Graf:** Rozbaľte a zbaľte míľnik, použite vyhľadávanie a výber viacerých riešiteľov. Tabuľka aj časová os zobrazia rovnaký výber.
- **Projekty → Graf:** Presuňte úlohu opakovane a upravte ju cez bočné okno. Zmeny sa uložia a graf ostane otvorený.
- **Projekty → Graf:** Pokúste sa ukončiť úlohu po termíne míľnika. Uloženie sa odmietne; rovnaký deň je povolený.
- **Projekty → Graf:** Sledujte nadväznosť míľnikov a zámerne porušenú väzbu úloh. Zobrazí sa upozornenie bez automatického presúvania termínov.
- **Projekty → Graf:** Nájdite tri úlohy s chýbajúcimi dátumami. Zobrazia sa na začiatku projektu s trvaním jeden deň; presunutie uloží nový rozsah.
- **Projekty → Graf:** Overte míľnik bez dátumu a prázdny projekt. Míľnik sa zobrazí na konci projektu; prázdny projekt ponúkne pridanie úlohy podľa oprávnení.
- **Projekty → Tlač:** Otvorte tlač s aktívnym filtrom. Obsah a filtre zodpovedajú grafu.

Adresár je určený pre CLI. `.htaccess` blokuje webový prístup na Apache; pri inom webovom serveri adresár `tests/` nesprístupňujte cez HTTP. Žiadny z importných príkazov sa pri príprave tohto balíka nespúšťa automaticky.

## Čitateľné názvy

Import nepridáva názov balíka ani prefix GANTT do názvov projektov, míľnikov a úloh. Príslušnosť k balíku zostáva v manifeste a internom označení v popise. Rôzne balíky preto môžu mať rovnaké čitateľné názvy bez vzájomného preberania záznamov.

Zo staršieho importu odstránite iba pôvodný prefix príkazom:

```bash
/usr/bin/php8.3 tests/gantt/import.php --clean-names --apply --user=1 --batch=crm-demo
```

Bez `--apply` sa vypíše iba plán. Kontrolujú sa ID a označenia pôvodného balíka. Ručne upravené názvy bez pôvodného prefixu zostávajú nezmenené; opakované spustenie už očistené názvy nemení.