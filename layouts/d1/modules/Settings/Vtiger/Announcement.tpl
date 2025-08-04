{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="listViewPageDiv" id="listViewContent">
        <div class="col-sm-12 col-xs-12 ">
            <div class="container-fluid" id="AnnouncementContainer">
                <div class="widget_header">
                    <h3>{vtranslate('LBL_ANNOUNCEMENTS', $QUALIFIED_MODULE)}</h3>
                </div>
                <hr>
                <div class="contents">
                    <textarea class="announcementContent textarea-autosize boxSizingBorderBox" rows="3" placeholder="{vtranslate('LBL_ENTER_ANNOUNCEMENT_HERE', $QUALIFIED_MODULE)}" style="width:100%">{$ANNOUNCEMENT->get('announcement')}</textarea>
                    <div class="textAlignCenter">
                        <br>
                        <button class="btn btn-success saveAnnouncement hide"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
                    </div>
                </div>
            </div>
{/strip}
