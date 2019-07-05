<?php
/* PLUGIN */
define('HEADING_TITLE','Individuelle Kontaktformulare erstellen und bearbeiten');
define('TBL_HEAD_COUNT', '#');
define('TBL_HEAD_FORM_ID', 'ID');
define('TBL_HEAD_FORM_TITLE', 'Formular Titel');
define('TBL_HEAD_PAGE_TITLE', 'Seite Titel');
define('TBL_HEAD_PAGE_HEADING', 'Seite Heading');
define('TBL_HEAD_FIELD_COUNT', 'Felder');
define('TBL_HEAD_HIT_COUNT', 'Hits');
define('TBL_HEAD_CREATED', 'angelegt am');
define('TBL_HEAD_ACTION', 'Aktion');

/* GENERAL */
define('BTN_ADD', 'Hinzufügen');
define('BTN_MODIFY', 'Aktualisieren');
define('BTN_ADD_OPTION', 'Optionen hinzufügen');
define('BTN_EDIT_OPTIONS', 'Optionen bearbeiten');
define('BTN_EDIT', 'Bearbeiten');
define('BTN_DELETE', 'Löschen');
define('BTN_GET_WIDGET', 'Widget abrufen');
define('BTN_SHOW_HITS', 'Hits');
define('LBL_YES', 'Ja');
define('LBL_NO', '-');
define('BTN_COPY', 'Kopieren');
define('JSON_LINE_BREAK_PLACEHOLDER', '|||');

/* RIGHT-SIDE INFO BOX */
/* HEAD / TITLES */
#FORMS
define('INFO_HEAD_FORM_NAME', 'Formular');
define('INFO_HEAD_FORM_INFO', 'Formular Info');
define('INFO_HEAD_ADD_FORM', 'Neues Formular anlegen');
define('INFO_HEAD_DELETE_FORM_CONFIRM', 'Bestätigung: Formular löschen');
define('INFO_HEAD_EDIT_FORM', 'Formular bearbeiten');

#FIELDS
define('INFO_HEAD_FIELDS_SECTION_TITLE', 'Felder');
define('INFO_HEAD_SELECTED_FIELD_TITLE', 'Ausgewähltes Feld');
define('INFO_HEAD_DELETE_FIELD_CONFIRM', 'Bestätigung: Formularfeld löschen');
define('INFO_HEAD_ADD_FIELD', 'Feld hinzufügen');
define('INFO_HEAD_EDIT_FIELD', 'Feld bearbeiten');
define('INFO_HEAD_EXISTING_FIELDS', 'Derzeitige Felder');

#OPTIONS
define('INFO_HEAD_SELECTED_OPTION_TITLE', 'Ausgewähltes Feld Option');
define('INFO_HEAD_DELETE_FIELD_OPTION_CONFIRM', 'Bestätigen: Formularfeld Option löschen');
define('INFO_HEAD_ADD_OPTION', 'Formularfeld Option hinzufügen');
define('INFO_HEAD_EDIT_FIELD_OPTIONS', 'Formularfeld Option bearbeiten');
define('INFO_HEAD_EDIT_FIELD_OPTION', 'Option bearbeiten');
define('INFO_HEAD_AVAILABLE_FIELD_OPTIONS', 'Verfügbare Option(en)');

/* LABELS */
#FORMS
define('INFO_FORM_ID_LABEL', 'Formular ID');
define('INFO_FORM_TITLE_LABEL', 'Formular Titel');
define('INFO_FORM_EMAILS_LABEL', 'E-Mail(s)');
define('INFO_FORM_CREATED_LABEL', 'Erstellt am');
define('INFO_FORM_CREATOR_LABEL', 'Erstellt von');
define('INFO_FORM_FIELDS_COUNTER_LABEL', 'Felder');
define('INFO_FORM_HITS_COUNTER_LABEL', 'Hits');
define('INFO_FORM_PAGE_LINK_LABEL', 'Link zur Seite');
define('INFO_FORM_DESCRIPTION_LABEL', 'Beschreibung');

define('INFO_PAGE_TITLE_LABEL', 'Seiten Titel');
define('INFO_PAGE_HEADER_LABEL', 'Header Titel');
define('INFO_NAVBAR_TITLE_LABEL', 'Navbar Titel');

#FIELDS
define('INFO_FIELD_ID_LABEL', 'Feld ID');
define('INFO_FIELD_TYPE_LABEL', 'Typ');
define('INFO_FIELD_NAME_LABEL', 'Name');
define('INFO_FIELD_LABEL_LABEL', 'Label');
define('INFO_FIELD_DESCRIPTION_LABEL', 'Beschreibung');
define('INFO_FIELD_REQUIRED_LABEL', 'Pflichtfeld? ');
define('INFO_FIELD_ORDER_LABEL', 'Sortierung');
define('INFO_FIELD_DEFAULT_LABEL', 'Default');
define('INFO_FIELD_DEFAULT_TEXT_LABEL', 'Default Text');
define('INFO_FIELD_REQUIRED_FLAG', '<span style="color:red;">*</span>');

#OPTIONS
define('INFO_OPTION_ID', 'ID');
define('INFO_OPTION_TEXT_LABEL', 'Text/Label');
define('INFO_OPTION_VALUE_LABEL', 'Wert');
define('INFO_OPTION_DEFAULT_VALUE_LABEL', 'Default');
define('INFO_OPTION_READ_ONLY_LABEL', 'nur lesen?');
define('INFO_OPTION_IS_READ_ONLY', '<span style="color:red;">N/A (nur lesen)</span>');
define('INFO_OPTION_SELECTED_LABEL', 'Vorausgewählt?');
define('INFO_OPTION_ORDER_LABEL', 'Sortierung');
define('INFO_OPTION_SORT_LABEL', 'Sortieren');
define('INFO_OPTION_OPTIONS_LABEL', 'Optionen:');
define('INFO_OPTION_ACTIONS_LABEL', 'Aktionen');

/* MESSAGES */
#FORMS
define('INFO_ADD_FORM_INFO', 'Bitte füllen Sie die folgenden Felder aus, um ein neues Formular zu erstellen.');
define('INFO_DELETE_FORM_CONFIRM', 'Sind Sie sicher, dass Sie das folgende Formular löschen möchten?');
define('MSG_FORM_ADDED', 'Formular erstellt!');
define('MSG_FORM_UPDATED', 'Formular aktualsiert!');
define('MSG_FORM_DELETED', 'Formular gelöscht!');
define('MSG_FORM_NOT_ADDED', 'Kann Formular nicht erstellen. Bitte versuchen Sie es noch einmal oder kontaktieren Sie den technischen Support.');
define('MSG_FORM_NOT_UPDATED', 'Kann Formular nicht aktualisieren. Bitte versuchen Sie es noch einmal oder kontaktieren Sie den technischen Support.');
define('MSG_FORM_NOT_DELETED', 'Kann Formular nicht löschen. Bitte versuchen Sie es noch einmal oder kontaktieren Sie den technischen Support.');

define('INFO_NO_FORM', 'Noch kein Formular erstellt.<br />Klicken Sie auf hinzufügen, um ein Formular zu erstellen.');
define('INFO_NO_FORM_ID', 'Formular ID nicht angegeben! Bitte wählen Sie ein Formular aus und versuchen Sie es erneut.');

#FIELDS
define('INFO_DELETE_FIELD_CONFIRM', 'Sind Sie sicher, dass Sie das folgende Formularfeld löschen möchten?');
define('MSG_FORM_FIELD_ADDED', 'Formularfeld hinzugefügt!');
define('MSG_FORM_FIELD_UPDATED', 'Formularfeld aktualsiert!');
define('MSG_FORM_FIELD_DELETED', 'Formularfeld gelöscht!');
define('MSG_FORM_FIELD_NOT_ADDED', 'Kann Formularfeld nicht hinzufügen. Bitte versuchen Sie es noch einmal oder kontaktieren Sie den technischen Support..');
define('MSG_FORM_FIELD_NOT_UPDATED', 'Kann Formularfeld nicht aktualisieren. Bitte versuchen Sie es noch einmal oder kontaktieren Sie den technischen Support.');
define('MSG_FORM_FIELD_NOT_DELETED', 'Kann Formularfeld nicht löschen. Bitte versuchen Sie es noch einmal oder kontaktieren Sie den technischen Support.');
define('MSG_FIELD_INFO_NOT_AVAILABLE', 'Formularfeld-Information nicht verfügbar');
define('INFO_NO_FIELD_ID', 'Formularfeld ID nicht angegeben! Bitte wählen Sie ein Formularfeld aus und versuchen Sie es erneut.');

#OPTIONS
define('INFO_DELETE_OPTION_CONFIRM', 'Sind Sie sicher, dass Sie die folgende Formularfeld-Option löschen möchten?');
define('MSG_OPTION_ADDED', 'Formularfeld-Option hinzugefügt!');
define('MSG_OPTION_UPDATED', 'Formularfeld-Option aktualsiert!');
define('MSG_OPTION_DELETED', 'Formularfeld-Option gelöscht!');
define('MSG_OPTION_NOT_ADDED', 'Kann Formularfeld-Option nicht hinzufügen. Bitte versuchen Sie es noch einmal oder kontaktieren Sie den technischen Support.');
define('MSG_OPTION_NOT_UPDATED', 'Kann Formularfeld-Option nicht hinzufügen aktualisieren. Bitte versuchen Sie es noch einmal oder kontaktieren Sie den technischen Support.');
define('MSG_OPTION_NOT_DELETED', 'Kann Formularfeld-Option nicht löschen. Bitte versuchen Sie es noch einmal oder kontaktieren Sie den technischen Support.');
define('MSG_OPTION_INFO_NOT_AVAILABLE', 'Option Information not available');

#OTHER
define('MSG_TITLE_REQUIRED_ERROR', 'Bitte fügen Sie einen Titel hinzu.');

define('MSG_PAGE_TITLE_REQUIRED_ERROR', 'Seitentitel unbedingt erforderlich.');
define('MSG_HEADER_REQUIRED_ERROR', 'Header-Titel unbedingt erforderlich.');
define('MSG_NAVBAR_TITLE_REQUIRED_ERROR', 'Navbar-Titel unbedingt erforderlich.');

define('MSG_INSTALL_ERROR', 'Möglicherweise gibt es Probleme mit dem Plug-In. Stellen Sie sicher, dass das Plug-In korrekt installiert wurde.');
define('TEXT_INFO_SELECT_FILE', 'Wählen Sie eine Seite');
define('LABEL_SELECT_PAGE', 'Seite wählen');
define('LABEL_BUTTON_WIDGET', 'Button Widget');
define('LABEL_WIDGET_URL', 'Link');

define('PAGINATION_LABEL','Zeige %s bis %s (von %s Formularen)');


#HIT REPORT
define('REPORT_HITS_TITLE','Hits Report');
define('REPORT_HITS_REFERER','Referer');
define('REPORT_HITS_COUNT','Hits');
define('REPORT_HITS_LAST_TIME', 'Last Hit');
define('REPORT_WIDGET_TITLE', 'Widget abrufen');