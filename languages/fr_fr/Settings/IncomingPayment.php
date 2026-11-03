<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

$languageStrings = [
    'IncomingPayment' => 'Paiements entrants',
    'SINGLE_IncomingPayment' => 'Paiement entrant',
    'LBL_INCOMING_PAYMENT_INTEGRATION' => 'Intégration des paiements entrants',
    'LBL_INTEGRATION_INTRO' => 'Choisissez quels documents reçoivent des paiements et quels champs le module lit et met à jour.',
    'LBL_MODULE' => 'Module',
    'LBL_ACTIVE' => 'Actif',
    'LBL_STATUS_FIELD' => 'Champ de statut',
    'LBL_STATUS_VALUE' => 'Statut lorsque entièrement payé',
    'LBL_STATUS_VALUE_BACK' => 'Statut lorsqu\'il n\'est plus payé',
    'LBL_RELATED_TO_FIELD' => 'Champ d\'organisation',
    'LBL_TOTAL_FIELD' => 'Champ du montant total',
    'LBL_PAID_FIELD' => 'Champ du montant payé',
    'LBL_BALANCE_FIELD' => 'Champ du solde',
    'LBL_NONE' => 'Aucun',
    'LBL_SAVE' => 'Enregistrer',
    'LBL_FIELDS_NOTE' => 'Tant qu\'une intégration est active, le champ du montant payé du document est en lecture seule et calculé à partir des paiements reçus. Les champs payé et solde manquants sont créés automatiquement.',
    'LBL_NO_MODULES' => 'Aucun module d\'inventaire n\'est disponible pour l\'intégration.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Seuil de paiement insuffisant',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Un document dont le solde est au plus égal à ce montant reçoit le statut payé. Le montant est comparé dans la devise du document sans conversion et le solde conserve le montant réellement impayé. 0 signifie uniquement les documents entièrement payés.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'Le seuil de paiement insuffisant doit être un nombre supérieur ou égal à 0.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Aucun',
    'JS_INTEGRATION_SAVED' => 'Paramètres d\'intégration enregistrés',
    'JS_TOTAL_FIELD_REQUIRED' => 'Sélectionnez le champ du montant total pour chaque module actif',
];
