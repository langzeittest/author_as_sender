<?php

// Make sure that this script is loaded from the admin interface.
if (!defined('PHORUM_ADMIN')) return;

// Save settings in case this script is run after posting
// the settings form.
if ( count($_POST) ) {

    // Create the settings array for this module.
    $PHORUM['mod_author_as_sender'] = array
        ( 'use_email' => $_POST['use_email'] ? 1 : 0,
          'use_email_mod' => $_POST['use_email_mod'] ? 1 : 0 );

    // Force the options to be integer values.
    settype($PHORUM['mod_author_as_sender']['use_email'], 'int');
    settype($PHORUM['mod_author_as_sender']['use_email_mod'], 'int');

    if (!phorum_db_update_settings(array('mod_author_as_sender'=>$PHORUM['mod_author_as_sender']))){
        $error = 'Database error while updating settings.';
    } else {
        phorum_admin_okmsg('Settings Updated');
    }
}

// Apply default values for the settings.
if (!isset($PHORUM['mod_author_as_sender']['use_email'])) {
    $PHORUM['mod_author_as_sender']['use_email'] = 1;
}

if (!isset($PHORUM['mod_author_as_sender']['use_email_mod'])) {
    $PHORUM['mod_author_as_sender']['use_email_mod'] = 1;
}

// We build the settings form by using the PhorumInputForm object.
include_once './include/admin/PhorumInputForm.php';
$frm = new PhorumInputForm ('', 'post', 'Save settings');
$frm->hidden('module', 'modsettings');
$frm->hidden('mod', 'author_as_sender');

// Here we display an error in case one was set by saving
// the settings before.
if (!empty($error)){
    phorum_admin_error($error);
}

$frm->addbreak('Edit settings for the Author as Sender Module');
$frm->addbreak('User notifications and private mail');
// Email address
$row = $frm->addrow('Use authors email-address instead of System Emails From Address: ', $frm->checkbox('use_email', '1', '', $PHORUM['mod_author_as_sender']['use_email']));
$frm->addhelp($row, 'Use authors email-address', 'If this option is marked and if the users email address is public the module will use the users email address. In other cases the primal email address is used which is by default "System Emails From Address" from "Global Settings". Other modules like the Mailing List Module can specify a differing email address.');
$frm->addbreak('Moderator notifications');
// Email address
$row = $frm->addrow('Use authors email-address instead of System Emails From Address: ', $frm->checkbox('use_email_mod', '1', '', $PHORUM['mod_author_as_sender']['use_email_mod']));
$frm->addhelp($row, 'Use authors email-address', 'If this option is marked the module will use the users email address. In other cases the primal email address is used which is by default "System Emails From Address" from "Global Settings". Other modules like the Mailing List Module can specify a differing email address.');
// Show settings form
$frm->show();

?>
