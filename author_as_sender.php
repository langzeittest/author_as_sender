<?php

if (!defined('PHORUM')) return;

function mod_author_as_sender_email_user_start($array)
{

    include_once('./include/api/mail.php');

    $PHORUM = $GLOBALS['PHORUM'];

    $data = $array[1];

    if (!(is_array($data) && isset($data['author']))) return $array;

    $email_address = $data['from_address'];

    // Remove extraneous stuff from email addresses
    // look for angle braces
    $begin = strrpos($email_address, '<');
    $end = strrpos($email_address, '>');
    if ($begin !== false) {
        $email_address = substr($email_address, $begin + 1, $end - $begin - 1);
    }

    if (isset($data['message_id']) && $data['message_id'] && $data['message_id'] > 0) {
        //
        // Message
        //
        // Get message
        $message = phorum_db_get_message($data['message_id'], 'message_id', true);

        if (isset($message['user_id']) && $message['user_id']!=0) {
            // Registered user, get user info
            $user = phorum_api_user_get($message['user_id'], false);
            if ( isset($data['approve_url']) && $data['approve_url'] ) {
              // Moderator notification
              if (    isset($PHORUM['mod_author_as_sender']['use_email_mod'])
                   && $PHORUM['mod_author_as_sender']['use_email_mod']
                   && isset($user['email']) ) {
                $email_address = $user['email'];
              }
            } else {
              // User notification
              if (    isset($PHORUM['mod_author_as_sender']['use_email'])
                   && $PHORUM['mod_author_as_sender']['use_email']
                   && isset($user['email']) && $user['hide_email']==0 ) {
                $email_address = $user['email'];
              }
            }
        } else {
            // Guest
            if (    isset($PHORUM['mod_author_as_sender']['use_email'])
                 && $PHORUM['mod_author_as_sender']['use_email']
                 && isset($message['email']) && $message['email']!='') {
              $email_address = $message['email'];
            }
        }
    }

    if (isset($data['pm_message_id']) && $data['pm_message_id'] && $data['pm_message_id'] > 0) {
        //
        // Private message
        //
        // Get message
        $message = phorum_db_pm_get($data['pm_message_id']);

        if (isset($message['from_user_id']) && $message['from_user_id']!=0) {
            // Registered user, get user info
            $user = phorum_api_user_get($message['from_user_id'], false);
            if (    isset($PHORUM['mod_author_as_sender']['use_email'])
                 && $PHORUM['mod_author_as_sender']['use_email']
                 && isset($user['email']) && $user['hide_email']==0) {
              $email_address = $user['email'];
            }
        } else {
            // Guest
            if (    isset($PHORUM['mod_author_as_sender']['use_email'])
                 && $PHORUM['mod_author_as_sender']['use_email']
                 && isset($message['email']) && $message['email']!='') {
              $email_address = $message['email'];
            }
        }
    }

    if (isset($data['reportedby'])) {
      $author = $data['reportedby'];
    } else {
      $author = $data['author'];
    }

    // Build new from-address
    $data['from_address'] = phorum_api_mail_encode_header($author).' <'.$email_address.'>';

    $array[1] = $data;

    return $array;
}

//
// Add sanity checks
//
function mod_author_as_sender_sanity_checks($sanity_checks) {
    if (    isset($sanity_checks)
         && is_array($sanity_checks) ) {
        $sanity_checks[] = array(
            'function'    => 'mod_author_as_sender_do_sanity_checks',
            'description' => 'Author as Sender Module'
        );
    }
    return $sanity_checks;
}

//
// Do sanity checks
//
function mod_author_as_sender_do_sanity_checks() {

    include_once('./include/version_functions.php');

    global $PHORUM;

    // Check if the Phorum version is high enough.
    if (phorum_compare_version(PHORUM, '5.2.8') == -1) {
          return array(
                     PHORUM_SANITY_CRIT,
                     'The Phorum version is not high enough.',
                     'This module needs at least Phorum version 5.2.8.'
                 );
    }

    // Check if module settings exists.
    if (    !isset($PHORUM['mod_author_as_sender']['use_email'])
         || !isset($PHORUM['mod_author_as_sender']['use_email_mod']) ) {
          return array(
                     PHORUM_SANITY_CRIT,
                     'The default settings for the module are missing.',
                     "Login as administrator in Phorum's administrative "
                         .'interface and go to the "Modules" section. Open '
                         .'the module settings for the Author as Sender '
                         .'Module and save the default values.'
                 );
    }

    return array(PHORUM_SANITY_OK, NULL);
}

?>
