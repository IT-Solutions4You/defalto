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
    'OutgoingPayment' => 'Pagos emitidos',
    'SINGLE_OutgoingPayment' => 'Pago emitido',
    'LBL_OUTGOING_PAYMENT_INTEGRATION' => 'Integración de pagos emitidos',
    'LBL_INTEGRATION_INTRO' => 'Elija qué documentos se pagan con pagos emitidos y qué campos suyos lee y actualiza el módulo.',
    'LBL_MODULE' => 'Módulo',
    'LBL_ACTIVE' => 'Activo',
    'LBL_STATUS_FIELD' => 'Campo de estado',
    'LBL_STATUS_VALUE' => 'Estado al pagar por completo',
    'LBL_STATUS_VALUE_BACK' => 'Estado cuando deja de estar pagado',
    'LBL_RELATED_TO_FIELD' => 'Campo de proveedor u organización',
    'LBL_TOTAL_FIELD' => 'Campo de importe total',
    'LBL_PAID_FIELD' => 'Campo de importe pagado',
    'LBL_BALANCE_FIELD' => 'Campo de saldo',
    'LBL_NONE' => 'Ninguno',
    'LBL_SAVE' => 'Guardar',
    'LBL_FIELDS_NOTE' => 'Mientras una integración está activa, el campo de importe pagado del documento es de solo lectura y se calcula a partir de los pagos emitidos pagados. Los campos de pagado y saldo que falten se crean automáticamente.',
    'LBL_NO_MODULES' => 'No hay ningún módulo de inventario disponible para la integración.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Umbral de pago insuficiente',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Un documento cuyo saldo sea como máximo este importe recibe el estado pagado. El importe se compara en la moneda del documento sin conversión y el saldo conserva el importe pendiente real. 0 significa solo documentos pagados por completo.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'El umbral de pago insuficiente debe ser un número igual o mayor que 0.',
    'LBL_REFUND' => 'Reembolso',
    'LBL_REFUND_HELP' => 'Los pagos de este módulo son reembolsos del documento y se restan del importe pagado.',
    'LBL_REFUND_WITHOUT_PAYING' => 'Ningún módulo de pagos activo paga este documento; los reembolsos no se tienen en cuenta.',
    'LBL_TWO_PAYING_MODULES' => 'Otro módulo de pagos ya paga %s. Marque los pagos de uno de ellos como reembolsos.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Ninguno',
    'JS_INTEGRATION_SAVED' => 'Configuración de integración guardada',
    'JS_TOTAL_FIELD_REQUIRED' => 'Seleccione el campo de importe total para cada módulo activo',
];
