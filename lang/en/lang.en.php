<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
/* ========== GLOBAL ========== */

$lang['no'] = 'No';
$lang['yes'] = 'Yes';
$lang['members_only'] = 'Members Only';
$lang['del'] = 'Delete';
$lang['system_error'] = 'A system error has occurred';
 
/* ========== DATE/TIME ========== */

$lang['Monday'] = 'Monday';
$lang['Tuesday'] = 'Tuesday';
$lang['Wednesday'] = 'Wednesday';
$lang['Thursday'] = 'Thursday';
$lang['Friday'] = 'Friday';
$lang['Saturday'] = 'Saturday';
$lang['Sunday'] = 'Sunday';
$lang['Monday_s'] = 'Mon';
$lang['Tuesday_s'] = 'Tue';
$lang['Wednesday_s'] = 'Wed';
$lang['Thursday_s'] = 'Thu';
$lang['Friday_s'] = 'Fri';
$lang['Saturday_s'] = 'Sat';
$lang['Sunday_s'] = 'Sun';
$lang['January'] = 'January';
$lang['February'] = 'February';
$lang['March'] = 'March';
$lang['April'] = 'April';
$lang['May'] = 'May';
$lang['June'] = 'June';
$lang['July'] = 'July';
$lang['August'] = 'August';
$lang['September'] = 'September';
$lang['October'] = 'October';
$lang['November'] = 'November';
$lang['December'] = 'December';
$lang['January_s'] = 'Jan';
$lang['February_s'] = 'Feb';
$lang['March_s'] = 'Mar';
$lang['April_s'] = 'Apr';
$lang['May_s'] = 'May';
$lang['June_s'] = 'Jun';
$lang['July_s'] = 'Jul';
$lang['August_s'] = 'Aug';
$lang['September_s'] = 'Sep';
$lang['October_s'] = 'Oct';
$lang['November_s'] = 'Nov';
$lang['December_s'] = 'Dec';
 
/* ========== MESSAGES ========== */
$lang['message_blank_title'] = 'Error';
$lang['message_blank_subtitle'] = 'Fatal error encountered';
$lang['message_blank_text'] = 'There was an error processing your request. If the problem persists please contact an administrator.';

$lang['message_101_title'] = 'Success';
$lang['message_101_subtitle'] = 'Registration complete';
$lang['message_101_text'] = 'Your account has successfully been created, but is currently inactive. Check your email for a link to validate your membership.';

$lang['message_102_title'] = 'Success';
$lang['message_102_subtitle'] = 'Account validated';
$lang['message_102_text'] = 'Your account has been successfuly validated, and may now be used to login!';

$lang['message_103_title'] = 'Success';
$lang['message_103_subtitle'] = 'Validation email sent';
$lang['message_103_text'] = 'An activation link has been sent to your email address.';

$lang['message_104_title'] = 'Success';
$lang['message_104_subtitle'] = 'Password has been reset';
$lang['message_104_text'] = 'We have reset your password, and emailed it to you.';

$lang['message_105_title'] = 'Error';
$lang['message_105_subtitle'] = 'You are not allowed to do this';
$lang['message_105_text'] = 'You do not have sufficient rights to perform this action.';

$lang['message_106_title'] = 'Error';
$lang['message_106_subtitle'] = 'Account does not exist';
$lang['message_106_text'] = 'The requested member account does not exist in our database.';

$lang['message_107_title'] = 'Error';
$lang['message_107_subtitle'] = 'Registration disabled';
$lang['message_107_text'] = 'The registration process has been disabled by the system administrator.';

$lang['message_108_title'] = 'Success';
$lang['message_108_subtitle'] = 'Registration complete';
$lang['message_108_text'] = 'Your account has been successfully created, and you may now log in.';

$lang['message_109_title'] = 'Success';
$lang['message_109_subtitle'] = 'Registration complete';
$lang['message_109_text'] = 'Your account has been successfully created; however, system administrators require that they manually validate every account. You will be unable to login until your account has been validated.';

/* ========== REGISTRATION ERRORS ========== */
$lang['reg_un_format'] = 'Invalid username format';
$lang['reg_un_length'] = 'Username must be greater than 2 characters';
$lang['reg_un_exists'] = 'Username already exists';
$lang['reg_pwd_length'] = 'Password must be greater than 4 characters';
$lang['reg_pwd_nomatch'] = 'The passwords do not match';
$lang['reg_email_length'] = 'Email address must be greater than 10 characters';
$lang['reg_email_exists'] = 'Email already exists';
$lang['reg_email_format'] = 'Email address is not in the correct format example@domain.com';
$lang['reg_sq_length'] = 'The answer to your security question was too short';

/* ========== LOGIN ERRORS ========== */
$lang['login_un_empty'] = 'Username field was left blank';
$lang['login_pwd_empty'] = 'Password field was left blank';
$lang['login_invalid'] = 'Invalid username/password combination';
$lang['login_inactive'] = 'Your account has not yet been validated. Please check your email address, and follow the validation link.';
$lang['login_banned'] =  'This account has been permanently banned.';

/* ========== PASSWORD RECOVERY ========== */
$lang['validation_email'] = 'Hi %1$s,<br /><br />
						To use your account you need to activate it with this link:
						%2$s<br /><br />
						To cancel the recently inactive membership use this link:
						%3$s';
$lang['validation_admin'] = '<br /><br />Feel free to contact an administrator if you experience difficulties';
$lang['validation_reg'] = 'Registration';
$lang['validation_activate'] = 'Activate Account';
$lang['validation_deactivate'] = 'Remove Account';
$lang['token_not_exist'] = 'Error retrieving authentication token (member does not exist).';
$lang['validation_active'] = 'Your account is already active.';
$lang['reset_email'] = 'Hi %1$s,<br /><br />
						Your account has recently had a password reset. The new password is:
						%2$s<br /><br />
						If this was a mistake, you may want to contact the administrator.';
						
/* ========== PAGES ========== */
$lang['page_error_title_length'] = 'Page title is too short';
$lang['page_error_cat_missing'] = 'Page category is missing';
$lang['page_error_text_length'] = 'Page text is too short';
$lang['spam_error'] = 'System has flagged this comment as spam';

/* ========== PROFILE ========== */
$lang['profile_error_nomatch'] = 'Your current password was incorrect';
$lang['profile_error_nosame'] = 'You did not enter matching passwords';
$lang['gender_U'] = 'Unknown';
$lang['gender_M'] = 'Male';
$lang['gender_F'] = 'Female';

/* ========== ADMIN ========== */
$lang['admin_error_title_missing'] = 'Title is missing';
$lang['admin_error_desc_missing'] = 'Description is missing';
$lang['admin_error_path_missing'] = 'Path is missing';
$lang['admin_error_code_missing'] = 'Code is missing';

$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';
?>