{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{if $VERSION_NOTICE}
    <div class="alert alert-{$VERSION_NOTICE.type|escape} mb-3" role="status">
        <div class="mb-2">
            <strong>{vtranslate('LBL_INSTALLED_VERSION', 'Installer')}:</strong> {$VERSION_NOTICE.installedVersion|escape}
            <span class="mx-2" aria-hidden="true">&rarr;</span>
            <strong>{vtranslate('LBL_AVAILABLE_VERSION', 'Installer')}:</strong> {$VERSION_NOTICE.packageVersion|escape}
        </div>
        <div>{vtranslate($VERSION_NOTICE.label, 'Installer')}</div>
    </div>
{/if}
