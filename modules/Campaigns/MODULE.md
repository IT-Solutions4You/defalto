# Campaigns

## Ownership

- `Campaigns` owns campaign records and the membership relations from campaigns to Accounts, Contacts, and Leads.
- `Campaigns_Relation_Model` owns membership relation persistence and campaign-member statuses.
- `Campaigns_RelationAjax_Action` owns campaign-specific relation requests, including bulk additions from a related-module filter and status updates.

## Runtime and data flow

- `Campaigns::save_related_module()` delegates campaign membership creation to `Campaigns_Relation_Model::saveCampaignRelation()` before invoking the standard Vtiger relation flow.
- A newly created campaign membership has no contact outcome (`campaignrelstatusid = 0`). A contact outcome is assigned only through an explicit status update.
- Bulk additions from a related-module filter require `EditView` permission for the source campaign record and `DetailView` permission for the related module.
- Memberships are stored in `vtiger_campaignaccountrel`, `vtiger_campaigncontrel`, and `vtiger_campaignleadrel`. Status definitions are stored in `vtiger_campaignrelstatus`.

## External contracts

- Supported campaign-member modules are Accounts, Contacts, and Leads, as defined by `Campaigns_Relation_Model::$tableCampaignRelation`.
- `Campaigns_RelationAjax_Action::addRelationsFromRelatedModuleViewId()` accepts `sourceRecord`, `relatedModule`, and `viewId` request values.
- `Campaigns_RelationAjax_Action::updateStatus()` accepts an explicit status identifier from `vtiger_campaignrelstatus`.

## Install lifecycle

- `Campaigns_Install_Model` owns fields, related lists, picklist values, links, and other module installation or update behavior.
- The module install/update version is declared in `modules/Campaigns/Campaigns.php`.

## Validation

- Run `php -l` on every touched Campaigns PHP file.
- Verify that adding Accounts, Contacts, or Leads creates a relation with status ID `0` and displays an empty status until the user chooses one.
- Verify that bulk addition from a filter is denied without `EditView` permission for the source campaign and succeeds with that permission.
