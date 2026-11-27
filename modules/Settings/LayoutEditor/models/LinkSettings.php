<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_LayoutEditor_LinkSettings_Model extends Vtiger_Base_Model
{
    protected static array $detailFieldLabels = [
        'linkid' => 'LBL_LINK_ID',
        'tabid' => 'LBL_LINK_MODULE',
        'linktype' => 'LBL_TYPE',
        'linkurl' => 'LBL_LINK_URL',
        'linkicon' => 'LBL_LINK_ICON',
        'sequence' => 'LBL_LINK_ORDER',
        'status' => 'LBL_STATUS',
        'handler_path' => 'LBL_LINK_HANDLER_FILE',
        'handler_class' => 'LBL_LINK_HANDLER_CLASS',
        'handler' => 'LBL_LINK_HANDLER_METHOD',
        'parent_link' => 'LBL_LINK_PARENT',
        'target' => 'LBL_LINK_TARGET',
        'quick_url' => 'LBL_QUICK_CREATE',
        'create_url' => 'LBL_FULL_FORM',
    ];

    protected static array $linkSectionDefinitions = [
        'detail' => ['label' => 'LBL_LINK_DETAIL_VIEW', 'groups' => [
            'DETAILVIEWBASIC' => 'LBL_LINK_DETAIL_BASIC',
            'DETAILVIEWADVANCED' => 'LBL_LINK_DETAIL_ADVANCED',
            'DETAILVIEW' => 'LBL_LINK_DETAIL_MORE',
        ]],
        'list' => ['label' => 'LBL_LINK_LIST_VIEW', 'groups' => [
            'LISTVIEWBASIC' => 'LBL_LINK_DETAIL_BASIC',
            'LISTVIEWMASSACTION' => 'LBL_LINK_DETAIL_ADVANCED',
            'LISTVIEW' => 'LBL_LINK_DETAIL_MORE',
        ]],
    ];

    public function getLinkSections(array $links): array
    {
        $sections = [];
        $targets = [];

        foreach (self::$linkSectionDefinitions as $key => $definition) {
            $sections[$key] = ['key' => $key, 'label' => $definition['label'], 'groups' => []];

            foreach ($definition['groups'] as $type => $label) {
                $sections[$key]['groups'][$type] = ['label' => $label, 'links' => []];
                $targets[$type] = [$key, $type];
            }
        }

        foreach ($links as $link) {
            if (!$this->isVisibleLink($link)) {
                continue;
            }

            [$section, $group] = $targets[$link['linktype']];
            $sections[$section]['groups'][$group]['links'][] = $link;
        }

        foreach ($sections as $key => $section) {
            $sections[$key]['groups'] = array_values($section['groups']);
        }

        return array_values($sections);
    }

    public function getSourceModule(string $moduleName): Vtiger_Module_Model
    {
        if (!isset(Settings_LayoutEditor_Module_Model::getSupportedModules()[$moduleName])) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return Vtiger_Module_Model::getInstance($moduleName);
    }

    public function getLinks(string $moduleName): array
    {
        $module = $this->getSourceModule($moduleName);
        $defaults = $this->getDefaultLinks($moduleName);
        $links = array_map(static fn(Vtiger_Link $link): array => get_object_vars($link), $module->getLinksForExport());
        usort($links, static fn(array $first, array $second): int =>
            [$first['linktype'], (int)$first['sequence'], (int)$first['linkid']]
            <=> [$second['linktype'], (int)$second['sequence'], (int)$second['linkid']]);

        $stored = [];

        foreach ($links as $index => $link) {
            $stored[$link['linktype']][$link['linklabel']] = true;
            $links[$index]['can_delete'] = true;
        }

        foreach ($defaults as $type => $definitions) {
            foreach ($definitions as $label => $definition) {
                if (!isset($stored[$type][$label])) {
                    $links[] = $definition;
                }
            }
        }

        $known = [];

        foreach ($links as $link) {
            $known[$this->getLinkIdentity($link)] = true;
        }

        foreach ($this->getRuntimeLinks($module, $links) as $link) {
            $identity = $this->getLinkIdentity($link);

            if (!isset($known[$identity])) {
                $links[] = $link;
                $known[$identity] = true;
            }
        }

        $links = array_values(array_filter($links, fn(array $link): bool => $this->isVisibleLink($link, $moduleName)));

        foreach ($links as $index => $link) {
            $links[$index]['details'] = $this->getLinkDetails($link, $moduleName);
            $links[$index]['title'] = vtranslate($link['linklabel'], $moduleName);
            $links[$index]['is_default'] = $this->isDefaultLink($link, $defaults);
            $links[$index]['is_installed'] = !empty($link['linkid']);
            $links[$index]['can_delete'] = $link['can_delete'] && !$links[$index]['is_default'];
        }

        return $links;
    }

    public function isDefaultLink(array $link, array $defaults): bool
    {
        return isset($defaults[$link['linktype']][$link['linklabel']]);
    }

    public function getLinkIdentity(array $link): string
    {
        return json_encode([$link['linktype'], $link['linklabel'], html_entity_decode((string)($link['linkurl'] ?? ''), ENT_QUOTES, 'UTF-8')]);
    }

    public function getRuntimeLinks(Vtiger_Module_Model $module, array $registrations = []): array
    {
        $name = $module->getName();
        $record = Vtiger_Record_Model::getCleanInstance($name);
        $record->setId(0);
        $detailClass = Vtiger_Loader::getComponentClassName('Model', 'DetailView', $name);
        $listClass = Vtiger_Loader::getComponentClassName('Model', 'ListView', $name);
        $detail = (new $detailClass())->setModule($module)->setRecord($record);
        $list = (new $listClass())->set('module', $module);
        $parameters = ['MODULE' => $name, 'RECORD' => 0, 'ACTION' => 'DETAIL'];
        $groups = $detail->getDetailViewLinks($parameters);
        $listParameters = ['MODULE' => $name, 'ACTION' => 'LIST'];
        $registeredBasicLabels = [];

        foreach ($registrations as $registration) {
            if ($registration['linktype'] === 'LISTVIEWBASIC') {
                $registeredBasicLabels[$registration['linklabel']] = true;
            }
        }

        $headerLinks = [];

        foreach ($module->getModuleBasicLinks() as $headerLink) {
            if (!isset($registeredBasicLabels[$headerLink['linklabel']])) {
                $headerLinks[] = Vtiger_Link_Model::getInstanceFromValues($headerLink);
            }
        }

        $collections = [$groups, $list->getListViewLinks($listParameters), $list->getListViewMassActions($listParameters), ['LISTVIEWBASIC' => $headerLinks]];
        $links = [];

        foreach ($collections as $collection) {
            foreach ($collection as $type => $items) {
                if (!$this->isActionLinkType($type)) {
                    continue;
                }

                foreach ($items as $item) {
                    // Persisted registrations are already included with their original values and delete permission.
                    if ($item->get('linkid')) {
                        continue;
                    }

                    $link = ['linkid' => null, 'tabid' => $module->getId(), 'linktype' => $type, 'can_delete' => false];

                    foreach (['linklabel', 'linkurl', 'linkicon', 'sequence', 'handler_path', 'handler_class', 'handler', 'parent_link', 'target', 'quick_url', 'create_url'] as $field) {
                        $link[$field] = $item->get($field);
                    }

                    // This is a definition preview: no existing CRM record is loaded or action executed.
                    foreach (['linkurl', 'quick_url', 'create_url'] as $field) {
                        $link[$field] = preg_replace('/([?&](?:record|sourceRecord|returnrecord|return_id|projectid|parent_id)=)0(?=&|$|["\x27])/', '$1\$RECORD\$', (string)$link[$field]);
                    }

                    if ($this->isVisibleLink($link, $name)) {
                        $links[] = $link;
                    }
                }
            }
        }

        return $links;
    }

    public function getLinkDetails(array $link, string $moduleName = ''): array
    {
        $details = [];

        foreach (self::$detailFieldLabels as $name => $label) {
            $value = $link[$name] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $iconClass = '';

            if ($name === 'linktype') {
                $value = $this->getLinkTypeLabel((string)$value);
            } elseif ($name === 'tabid' && $moduleName !== '') {
                $value = vtranslate($moduleName, $moduleName);
            } elseif ($name === 'linkicon') {
                $iconClass = $this->getIconClass((string)$value);

                if ($iconClass !== '') {
                    $value = '';
                }
            }

            $details[] = ['name' => vtranslate($label, 'Core'), 'value' => (string)$value, 'icon_class' => $iconClass];
        }

        return $details;
    }

    public function getLinkTypeLabel(string $type): string
    {
        foreach (self::$linkSectionDefinitions as $section) {
            if (isset($section['groups'][$type])) {
                return vtranslate($section['label'], 'Core') . ' / ' . vtranslate($section['groups'][$type], 'Core');
            }
        }

        return $type;
    }

    public function getIconClass(string $icon): string
    {
        $icon = trim($icon);

        if (preg_match('/^<(i|span)\\s+class=([\"\x27])([a-zA-Z0-9_ -]+)\\2(?:\\s+aria-hidden=([\"\x27])true\\4)?\\s*><\\/\\1>$/D', $icon, $matches)) {
            $icon = $matches[3];
        }

        return preg_match('/^[a-zA-Z0-9_-]+(?: +[a-zA-Z0-9_-]+)*$/D', $icon) ? $icon : '';
    }

    public function isVisibleLink(array $link, string $moduleName = ''): bool
    {
        if (!$this->isActionLinkType($link['linktype']) || $this->isStandardRecordAction($link, $moduleName)) {
            return false;
        }

        $url = html_entity_decode((string)($link['linkurl'] ?? ''), ENT_QUOTES, 'UTF-8');

        return !preg_match('/(?:[?&]|^)parent=Settings(?:&|$)/i', $url);
    }

    public function isActionLinkType(string $type): bool
    {
        return isset(self::$linkSectionDefinitions['detail']['groups'][$type])
            || isset(self::$linkSectionDefinitions['list']['groups'][$type]);
    }

    public function isStandardRecordAction(array $link, string $moduleName = ''): bool
    {
        $label = (string)($link['linklabel'] ?? '');

        foreach (['LBL_EDIT', 'LBL_DELETE', 'LBL_DUPLICATE', 'LBL_ADD_RECORD', 'LBL_ADD_TAG', 'LBL_REMOVE_TAG', 'LBL_TAGS'] as $key) {
            if ($label === $key || $label === vtranslate($key, $moduleName)) {
                return true;
            }
        }

        return false;
    }

    public function getDefaultLinks(string $moduleName): array
    {
        $defaults = [];

        foreach (Vtiger_Module_Model::getAll() as $module) {
            $owner = $module->getName();
            $class = $owner . '_Install_Model';

            if (!is_subclass_of($class, Core_Install_Model::class)) {
                continue;
            }

            $install = Core_Install_Model::getInstance('module.postupdate', $owner);

            foreach ($install->registerCustomLinks as $definition) {
                if (Core_Install_Model::isCustomLinkDeletion($definition)) {
                    continue;
                }

                if (($definition[0] ?: $owner) !== $moduleName) {
                    continue;
                }

                [, $type, $label, $url, $icon, $sequence, $handler] = array_pad($definition, 7, null);
                $defaults[$type][$label] = [
                    'linkid' => null,
                    'linktype' => $type,
                    'linklabel' => $label,
                    'linkurl' => str_replace('$LAYOUT$', Vtiger_Viewer::getDefaultLayoutName(), (string)$url),
                    'linkicon' => $icon,
                    'sequence' => $sequence,
                    'handler_path' => $handler['path'] ?? null,
                    'handler_class' => $handler['class'] ?? null,
                    'handler' => $handler['method'] ?? null,
                    'can_delete' => false,
                ];
            }
        }

        return $defaults;
    }

    public function deleteModuleLink(string $moduleName, string $linkId): ?array
    {
        $module = $this->getSourceModule($moduleName);

        if (!ctype_digit($linkId) || (int)$linkId < 1) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $links = array_column($module->getLinksForExport(), null, 'linkid');

        if (!isset($links[$linkId])) {
            return null;
        }

        $link = $links[$linkId];
        $linkValues = get_object_vars($link);

        if (!$this->isVisibleLink($linkValues, $moduleName) || $this->isDefaultLink($linkValues, $this->getDefaultLinks($moduleName))) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $module->deleteLink($link->linktype, $link->linklabel, $link->linkurl);

        foreach ($this->getLinks($moduleName) as $link) {
            if ($this->isMissingLinkDefinition($link, $linkValues)) {
                return $link;
            }
        }

        return null;
    }

    public function isMissingLinkDefinition(array $link, array $deletedLink): bool
    {
        return !$link['can_delete'] && $link['linktype'] === $deletedLink['linktype']
            && $link['linklabel'] === $deletedLink['linklabel'];
    }
}
