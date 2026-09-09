# Reporting

- Report definitions belong to `df_reporting` and `df_reportingcf`. `Reporting_Install_Model` extends `Core_Install_Model` for fields and widgets; `Reporting::$moduleVersion` controls install/update versioning.
- `Reporting_Field_Model::getReportTypeOptions()` supplies the fixed technical values `tabular` and `summary` with central Reporting translations. Both normal and editable picklist accessors use these options without querying picklist tables or requiring seeded values. Existing record values remain unchanged.
- `Reporting_Edit_View` owns the editor and `renderTable` preview. Normal and overlay script flows load the table controller and Chart.js; frontend assets are under `layouts/d1/modules/Reporting`.
- `Reporting_Edit_Js` derives Y-axis options from enabled calculation checkboxes. With no enabled calculation, disable the selector and show `chartCalculationWarning`; hide the warning when options become available. X-axis configuration follows grouping. `Reporting_Record_Model::getChartConfiguration()` restricts series to enabled calculations and valid numeric fields.
- `Reporting::save_module()` delegates sharing persistence to `Core_SharingRecord_Model`.
- Validate JavaScript with `node --check layouts/d1/modules/Reporting/resources/Edit.js` and ITS4You ScriptsValidator (`source_module=Reporting&only_errors=1`). Manually check an empty calculation selection, enabling/disabling calculations, and report type choices without seeded picklist values. Run `php -l` on changed PHP files and check inherited signatures for field-model changes.
