<?php
define('TEXT_SUCCESS', 'Ihre Nachricht wurde erfolgreich versandt.');

define('ENTRY_NAME', 'Name:');
define('ENTRY_EMAIL', 'E-Mailadresse:');
define('ENTRY_ENQUIRY', 'Nachricht:');

define('SEND_TO_TEXT','Sende Email an:');
define('ENTRY_EMAIL_NAME_CHECK_ERROR','Sind Sie sicher, dass Ihr Name korrekt ist? Unser System verlangt mindestens ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' Zeichen. Bitte versuchen Sie es noch einmal.');
define('ENTRY_EMAIL_CONTENT_CHECK_ERROR','Haben Sie vergessen, Ihre Nachricht an uns einzugeben? Wir freuen uns, von Ihnen zu lesen! Schreiben Sie Ihre Kommentare bitte in das Textfeld.');

define('NOT_LOGGED_IN_TEXT', 'nicht eingeloggt');

define('BTN_SEND','Senden');
define('BTN_BACK','Zurück');
define('BTN_NEW_REQUEST', 'Neue Anfrage');

/* CUSTOMER INFORMATION REQUEST FORM */
define('HEAD_CUSTOMER_INFORMATION', "Kontakt Information");
define('LABEL_CUSTOMER_NAME', "Name");
define('LABEL_CUSTOMER_COMPANY', "Firmenname");
define('LABEL_CUSTOMER_EMAIL', "E-Mailadresse");
define('LABEL_CUSTOMER_PHONE', "Telefonnummer");
define('REQUIRED_FLAG', '<span style="color:red;">*</span>');

define('HEAD_CONFIRMATION', 'Bestätigung: Bitte Daten nochmal prüfen und dann absenden');
define('HEAD_SUCCESS', 'Anfrage gesendet!');

/* EMAIL */
define('LABEL_CUSTOMER_ID', "Kundennummer: ");
define('LABEL_IP_ADDRESS', "IP: ");

define('LABEL_PLATFORM', "Plattform: ");
define('LABEL_IS_MOBILE', "Mobile? ");
define('LABEL_BROWSER', "Browser: ");
define('LABEL_BROWSER_VERSION', "Version: ");
define('LABEL_USER_AGENT', "User Agent (full): ");
define('LABEL_YES', 'Ja');
define('LABEL_NO', 'Nein');
define('DEFAULT_REQUEST_STATUS', 'Received');

define('EMAIL_SUBJECT_ADMIN', 'Nachricht an ' . STORE_NAME . ' - Anfrage ID: %s');
define('EMAIL_SUBJECT_CUSTOMER', 'Nachricht gesendet an ' . STORE_NAME);

define('EMAIL_CONTACT_TITLE', 'Kontakt Information');
define('EMAIL_PRODUCT_DESCRIPTION_TITLE', '');
define('EMAIL_FILE_UPLOAD_LINKS_TITLE', 'hochgeladene Dateien');

define('JSON_LINE_BREAK_PLACEHOLDER', '|||');

/* MESSAGE */
define('MESSAGE_REQUIRED_FIELD_MISSING', 'Pflichtfeld nicht ausgefüllt: %s');
define('MESSAGE_FORM_SUBMITION_SUCCESS', 'Danke für Ihre Anfrage! Wir prüfen Ihr Anliegen umgehend. Wenn Sie innerhalb einiger Werktage nichts von uns hören, kontaktieren Sie uns bitte telefonisch oder per E-Mail.');
define('MESSAGE_FORM_SUBMITION_ERROR', 'Beim Senden Ihrer Anfrage ist ein Fehler aufgetreten. Bitte kontaktieren Sie uns telefonisch oder per E-Mail, um uns von dem Problem in Kenntnis zu setzen. Wir bedauern die Unannehmlichkeiten.');
define('MESSAGE_NO_CUSTOM_FORM', 'Dieses Formular ist nicht verfügbar! Bitte wenden Sie sich an den technischen Support.');

define('MESSAGE_FILE_TYPE_ERROR', 'Sie haben versucht, eine Datei hochzuladen, die wir momentan nicht akzeptieren. Bitte laden Sie nur folgenden Dateitypen hoch: jpg, png, gif, pdf');