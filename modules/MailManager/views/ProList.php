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
 * Alias of the Pro inbox page. The menu entry (view=List) is delegated to
 * MailManager_ProIndex_View by MailManager_List_View; this alias keeps a direct
 * view=ProList link working the same way.
 */
class MailManager_ProList_View extends MailManager_ProIndex_View
{
}
