<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * PDF Maker templates attached to an EMAIL Maker template, kept in the shared template
 * relations (Core_TemplateRelation_Model, df_template_rel: module EMAILMaker, related_module
 * PDFMaker): one e-mail template may carry several PDF templates of its module, in their order.
 * The send window preselects them; only the PDF templates the user may use on the record are
 * offered (PDF Maker's getAvailableTemplates() checks permissions, status and display
 * conditions), so a deleted, inactive or forbidden one is skipped without a word. Without an
 * active PDF Maker nothing is shown and the stored links stay as they are.
 */
class EMAILMaker_PDFTemplate_Model
{
    public const MODULE = 'EMAILMaker';
    public const RELATED_MODULE = 'PDFMaker';

    /**
     * @return bool PDF Maker is installed, active and offers its templates
     */
    public static function isAvailable(): bool
    {
        return EMAILMaker_Module_Model::isPDFMakerInstalled() && Core_TemplateRelation_Model::isInstalled();
    }

    /**
     * @param string   $moduleName
     * @param int|bool $recordId record the mail is sent from, false = no record (template edit, list view)
     *
     * @return array PDF template id => name, the templates of the module the current user may use
     */
    public static function getAvailableTemplates(string $moduleName, int|bool $recordId = false): array
    {
        if (!self::isAvailable() || '' === $moduleName) {
            return [];
        }

        $pdfMaker = PDFMaker_PDFMaker_Model::getInstance();

        if (!$pdfMaker->checkPermissions('DETAIL')) {
            return [];
        }

        $templates = [];

        foreach ((array)$pdfMaker->getAvailableTemplates($moduleName, false, $recordId) as $templateId => $template) {
            $templates[(int)$templateId] = decode_html((string)$template['templatename']);
        }

        return $templates;
    }

    /**
     * What the template edit offers: the PDF templates of the module the user may use and the
     * attached ones among them, in their order.
     *
     * @param int    $templateId e-mail template, 0 for a new one
     * @param string $moduleName module of the e-mail template
     *
     * @return array ['available' => bool, 'options' => id => name, 'selected' => int[]]
     */
    public static function getEditData(int $templateId, string $moduleName): array
    {
        $options = self::getAvailableTemplates($moduleName);
        $selected = array_values(array_filter(self::getLinkedIds([$templateId])[$templateId] ?? [], function ($pdfTemplateId) use ($options) {
            return isset($options[$pdfTemplateId]);
        }));

        return ['available' => self::isAvailable(), 'options' => $options, 'selected' => $selected];
    }

    /**
     * @param int[] $templateIds e-mail templates
     *
     * @return array e-mail template id => PDF template ids in their order (one query)
     */
    public static function getLinkedIds(array $templateIds): array
    {
        return Core_TemplateRelation_Model::getRelatedIds(self::MODULE, $templateIds, self::RELATED_MODULE);
    }

    /**
     * The PDF templates of an e-mail template for its detail — deleted ones are left out.
     *
     * @param int $templateId
     *
     * @return array of ['id', 'name']
     */
    public static function getLinkedTemplates(int $templateId): array
    {
        $pdfTemplateIds = self::getLinkedIds([$templateId])[$templateId] ?? [];

        if (!$pdfTemplateIds || !self::isAvailable()) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT templateid, filename FROM vtiger_pdfmaker WHERE deleted = 0 AND templateid IN (' . generateQuestionMarks($pdfTemplateIds) . ')',
            $pdfTemplateIds
        );
        $names = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $names[(int)$row['templateid']] = decode_html((string)$row['filename']);
        }

        $templates = [];

        foreach ($pdfTemplateIds as $pdfTemplateId) {
            if (isset($names[$pdfTemplateId])) {
                $templates[] = ['id' => $pdfTemplateId, 'name' => $names[$pdfTemplateId]];
            }
        }

        return $templates;
    }

    /**
     * @param int $templateId e-mail template
     *
     * @return array ['available' => bool (PDF Maker active), 'templates' => getLinkedTemplates()]
     */
    public static function getDetailData(int $templateId): array
    {
        return ['available' => self::isAvailable(), 'templates' => self::getLinkedTemplates($templateId)];
    }

    /**
     * Stores the PDF templates of an e-mail template in the given order — only templates of its
     * module the current user may use; nothing changes while PDF Maker is not available.
     *
     * @param int    $templateId
     * @param string $moduleName     module of the e-mail template
     * @param array  $pdfTemplateIds in their order
     *
     * @return void
     */
    public static function saveLinks(int $templateId, string $moduleName, array $pdfTemplateIds): void
    {
        if (!$templateId || !self::isAvailable()) {
            return;
        }

        $available = self::getAvailableTemplates($moduleName);
        $pdfTemplateIds = array_values(array_unique(array_filter(array_map('intval', $pdfTemplateIds), function ($pdfTemplateId) use ($available) {
            return isset($available[$pdfTemplateId]);
        })));

        Core_TemplateRelation_Model::saveRelated(self::MODULE, $templateId, self::RELATED_MODULE, $pdfTemplateIds);
    }

    /**
     * @param int[] $templateIds e-mail templates that were deleted
     *
     * @return void
     */
    public static function deleteLinks(array $templateIds): void
    {
        Core_TemplateRelation_Model::deleteByRecord(self::MODULE, $templateIds);
    }

    /**
     * Deletes the links of every e-mail template (the module's "delete all").
     *
     * @return void
     */
    public static function deleteAllLinks(): void
    {
        Core_TemplateRelation_Model::deleteByModule(self::MODULE);
    }

    /**
     * What the send window preselects: for each offered e-mail template its PDF templates that
     * are offered as well (the user may use them on this record).
     *
     * @param array $emailTemplateIds   offered e-mail templates
     * @param array $availablePDFIds    offered PDF templates (ids)
     *
     * @return array e-mail template id => PDF template ids; templates without one are left out
     */
    public static function getPreselection(array $emailTemplateIds, array $availablePDFIds): array
    {
        $available = array_flip(array_map('intval', $availablePDFIds));
        $preselection = [];

        foreach (self::getLinkedIds($emailTemplateIds) as $templateId => $pdfTemplateIds) {
            $offered = array_values(array_filter($pdfTemplateIds, function ($pdfTemplateId) use ($available) {
                return isset($available[$pdfTemplateId]);
            }));

            if ($offered) {
                $preselection[$templateId] = $offered;
            }
        }

        return $preselection;
    }

    /**
     * @param array $templates EMAILMaker_EMAILMaker_Model::getAvailableTemplatesArray() — [0 => options, 1 => category => options]
     *
     * @return int[] the e-mail template ids among them
     */
    public static function getTemplateIdsFromOptions(array $templates): array
    {
        $ids = [];

        foreach ((array)($templates[0] ?? []) as $option) {
            $ids[] = (int)$option['value'];
        }

        foreach ((array)($templates[1] ?? []) as $options) {
            foreach ((array)$options as $option) {
                $ids[] = (int)$option['value'];
            }
        }

        return array_values(array_filter($ids));
    }
}
