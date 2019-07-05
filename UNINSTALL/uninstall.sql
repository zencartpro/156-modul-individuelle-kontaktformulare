##################################################################################
# UNINSTALL Individuelle Kontaktformulare 1.2.0 - 2019-02-23 - webchills
# UNINSTALL - NUR AUSFÜHREN WENN SIE DAS MODUL KOMPLETT ENTFERNEN WOLLEN!
##################################################################################

SET @gid=0;
SELECT @gid:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title = 'Individuelle Kontaktformulare' LIMIT 1;
DELETE FROM configuration WHERE configuration_group_id = @gid;
DELETE FROM configuration_group WHERE configuration_group_id = @gid;
DELETE FROM configuration_language WHERE configuration_key LIKE '%USTOM_FORMS%';
DELETE FROM admin_pages WHERE page_key='configCustomForms';
DELETE FROM admin_pages WHERE page_key='addonCustomFormDashboard';
DELETE FROM admin_pages WHERE page_key='addonCustomFormBuilder';
DROP TABLE IF EXISTS addon_custom_forms;
DROP TABLE IF EXISTS addon_custom_forms_fields;
DROP TABLE IF EXISTS addon_custom_forms_fields_options;
DROP TABLE IF EXISTS addon_custom_forms_hits;
DROP TABLE IF EXISTS addon_custom_forms_requests;