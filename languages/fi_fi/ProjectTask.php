<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

$languageStrings = [
    'Project Task Name' => 'Projektitehtävän nimi',
    'LBL_PROGRESS_HOURS_PER_DAY_HELP' => 'Käytetään, kun tehtävälle ei ole annettu suunniteltuja tunteja.',
    'LBL_PROGRESS_FALLBACK_HOURS_HELP' => 'Kuinka monta tuntia tehtävän ilman arviota ja täydellisiä päivämääriä tulisi edustaa laskennassa.',
    'LBL_PROGRESS_COMPLETED_STATUSES_HELP' => 'Näissä tiloissa olevat tehtävät katsotaan täysin valmiiksi.',
    'LBL_PROGRESS_EXCLUDED_STATUSES_HELP' => 'Näissä tiloissa olevia tehtäviä ei lasketa projektin edistymiseen.',

    'LBL_PROJECT_PROGRESS_CALCULATION_TITLE' => 'Miten projektin edistyminen lasketaan',
    'LBL_PROJECT_PROGRESS_CALCULATION' => '**Suuremmat tehtävät** vaikuttavat enemmän projektin edistymiseen.

Laskenta käyttää ensin **suunniteltuja tunteja**. Jos ne puuttuvat, käytetään tehtävän päivämääriä ja tunteja kalenteripäivässä. Ilman täydellisiä päivämääriä käytetään **korvaavia tunteja**.

**Valmiit tehtävät** lasketaan suoritetuiksi. **Pois jätettyjä tehtäviä** ei lasketa. Muut käyttävät syötettyä edistymistään.

Edistyminen päivittyy tehtävien muuttuessa. Voit muokata sitä käsin, mutta **seuraava tehtävän muutos laskee sen uudelleen**.

Poista **tämä toiminto tai prosessi** käytöstä pysäyttääksesi automaattiset päivitykset.',
    'LBL_PROJECT_PROGRESS_TASK' => 'Laske projektin edistyminen',
    'LBL_PROGRESS_HOURS_PER_DAY' => 'Tuntia kalenteripäivässä',
    'LBL_PROGRESS_FALLBACK_HOURS' => 'Tunnit tehtäville ilman arviota ja päivämääriä',
    'LBL_PROGRESS_COMPLETED_STATUSES' => 'Valmiit tilat (100%)',
    'LBL_PROGRESS_EXCLUDED_STATUSES' => 'Pois jätetyt tilat',

    'LBL_TASK_ASSIGNEE' => 'Vastuuhenkilö',
];

$jsLanguageStrings = [];
