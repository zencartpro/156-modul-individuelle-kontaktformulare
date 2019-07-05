<?php
define('TEXT_SUCCESS', 'Your message has been successfully sent.');

define('ENTRY_NAME', 'Full Name:');
define('ENTRY_EMAIL', 'Email Address:');
define('ENTRY_ENQUIRY', 'Message:');

define('SEND_TO_TEXT','Send Email To:');
define('ENTRY_EMAIL_NAME_CHECK_ERROR','Sorry, is your name correct? Our system requires a minimum of ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' characters. Please try again.');
define('ENTRY_EMAIL_CONTENT_CHECK_ERROR','Did you forget your message? We would like to hear from you. You can type your comments in the text area below.');

define('NOT_LOGGED_IN_TEXT', 'Not logged in');

define('BTN_SEND','Send');
define('BTN_BACK','Back');
define('BTN_NEW_REQUEST', 'New Request');

/* CUSTOMER INFORMATION REQUEST FORM */
define('HEAD_CUSTOMER_INFORMATION', "Contact Information");
define('LABEL_CUSTOMER_NAME', "Full Name");
define('LABEL_CUSTOMER_COMPANY', "Company Name");
define('LABEL_CUSTOMER_EMAIL', "Email Address");
define('LABEL_CUSTOMER_PHONE', "Telephone");
define('REQUIRED_FLAG', '<span style="color:red;">*</span>');

define('HEAD_CONFIRMATION', 'Confirmation');
define('HEAD_SUCCESS', 'Request Sent!');

/* EMAIL */
define('LABEL_CUSTOMER_ID', "Customer ID: ");
define('LABEL_IP_ADDRESS', "IP: ");

define('LABEL_PLATFORM', "Platform: ");
define('LABEL_IS_MOBILE', "Mobile? ");
define('LABEL_BROWSER', "Browser: ");
define('LABEL_BROWSER_VERSION', "Version: ");
define('LABEL_USER_AGENT', "User Agent (full): ");
define('LABEL_YES', 'Yes');
define('LABEL_NO', 'No');
define('DEFAULT_REQUEST_STATUS', 'Received');

define('EMAIL_SUBJECT_ADMIN', 'Message from ' . STORE_NAME . ' - Request ID: %s');
define('EMAIL_SUBJECT_CUSTOMER', 'Message Sent to ' . STORE_NAME);

define('EMAIL_CONTACT_TITLE', 'Contact Information');
define('EMAIL_PRODUCT_DESCRIPTION_TITLE', 'Custom Product Description');
define('EMAIL_FILE_UPLOAD_LINKS_TITLE', 'Uploaded Files');

define('JSON_LINE_BREAK_PLACEHOLDER', '|||');

/* MESSAGE */
define('MESSAGE_REQUIRED_FIELD_MISSING', 'Required Field Missing: %s');
define('MESSAGE_FORM_SUBMITION_SUCCESS', 'Thank you for your request! Your request has been received and will be evaluated. If you do not hear from us within the next business day, please contact us by phone or email.');
define('MESSAGE_FORM_SUBMITION_ERROR', 'There was an error sending your request. Please, call or send us an e-mail to report the incident. We do apologize for the inconvenience.');
define('MESSAGE_NO_CUSTOM_FORM', 'Form not available! Please, contact technical support.');

define('MESSAGE_FILE_TYPE_ERROR', 'You tried to upload a file type we currently do not accept. Please, upload one of the following acceptable file types: jpg, png, gif, pdf');