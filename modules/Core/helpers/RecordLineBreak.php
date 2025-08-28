<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_RecordLineBreak_Helper extends Core_DatabaseData_Model
{
    private array $recordModels = [];
    private Core_RelatedBlock_Model $relateBlock;
    private array $breakLines = [];
    private array $calculations = [
        'subtotal' => 0,
    ];
    private int $columnsCount = 0;
    private string $templateModule = 'PDFMaker';
    private int $sequence = 0;

    public function getColumnsCount(): int
    {
        return $this->columnsCount;
    }

    public function setColumnsCount(int $columnsCount): void
    {
        $this->columnsCount = $columnsCount;
    }

    public function setTemplateModule(string $templateModule): void
    {
        $this->templateModule = $templateModule;
    }

    public function setRelatedBlock(Core_RelatedBlock_Model $relateBlock): self
    {
        $this->relateBlock = $relateBlock;

        return $this;
    }

    public function getRelatedBlock(): Core_RelatedBlock_Model
    {
        return $this->relateBlock;
    }

    public static function getInstance(): self
    {
        $instance = new self();
        $instance->sequence = 0;
        $instance->recordModels = [];
        $instance->calculations = [
            'subtotal' => 0,
        ];

        return $instance;
    }

    /**
     * @param Vtiger_Record_Model $recordModel
     * @return Core_RecordLineBreak_Helper
     */
    public function setRecord(Vtiger_Record_Model $recordModel): self
    {
        $this->recordModels[$this->sequence] = $recordModel;

        return $this;
    }

    public function getRecord(): Vtiger_Record_Model
    {
        return $this->recordModels[$this->sequence];
    }

    public function retrieveData(): self
    {
        $this->calculations['subtotal'] += $this->getRecord()->get('subtotal');

        return $this;
    }

    public function plusSequence(): self
    {
        $this->sequence++;

        return $this;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function getContent()
    {
        $content = '';
        $breakLines = $this->getBreakLines();
        $sequence = $this->getSequence();

        if (!empty($breakLines[$sequence])) {
            $info = $breakLines[$sequence];

            if (!empty($info['show_subtotal'])) {
                $content .= sprintf(
                    '<tr class="lbTr"><th class="lbTh" colspan="%s">%s:</th><td class="lbTd" colspan="1">%s $CURRENCYSYMBOL$</td></tr>',
                    $this->getColumnsCount() - 1,
                    '%G_Subtotal%',
                    $this->calculations['subtotal'],
                );
            }

            $content .= '<tr><td>#PAGEBREAK#</td></tr>';

            if (!empty($info['show_header'])) {
                $content .= '<tr><td>#COPYHEADER#</td></tr>';
            }
        }

        return $content;
    }

    /**
     * @return array
     */
    public function getBreakLines(): array
    {
        if (!empty($this->breakLines)) {
            return $this->breakLines;
        }

        if ($this->getRelatedBlock() && 'PDFMaker' === $this->getRelatedBlock()->getTemplateModule()) {
            $this->retrieveDB();
            $result = $this->getBreakLineTable()->selectResult([], ['crmid' => $this->getRelatedBlock()->getSourceRecord()->getId()]);

            while ($row = $this->getDB()->fetchByAssoc($result)) {
                $this->breakLines[(int)$row['sequence']] = $row;
            }
        }

        return $this->breakLines;
    }

    public function getBreakLineTable(): Core_DatabaseTable_Model
    {
        return $this->getTable('vtiger_pdfmaker_breakline', 'crmid');
    }

    public function retrieveColumnCountByContent($content): self
    {
        $html = Core_SimpleHtmlDom_Helper::getInstance($content);
        $tr = $html->getHtmlNode()->find('tr td');

        if ($tr) {
            $this->setColumnsCount(count($tr));
        }

        return $this;
    }
}


