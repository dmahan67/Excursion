<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

/**
 * Input
 */
 
$R['code_option_empty'] = '---';
$R['code_time_separator'] = ':';
$R['member_image'] = '<img src="{$src}" class="{$class}" />';
$R['input_checkbox'] = '<label><input type="checkbox" name="{$name}" id="{$name}" value="{$value}"{$checked}{$attrs} /> {$title}</label>';
$R['input_default'] = '<input type="{$type}" class="span3" name="{$name}" value="{$value}"{$attrs} />{$error}';
$R['input_option'] = '<option value="{$value}"{$selected}>{$title}</option>';
$R['input_radio'] = '<label><input type="radio" name="{$name}" id="{$name}" value="{$value}"{$checked}{$attrs} /> {$title}</label>';
$R['input_radio_separator'] = ' ';
$R['input_select'] = '<select name="{$name}" id="{$name}" class="span3" {$attrs}>{$options}</select>{$error}';
$R['input_text'] = '<input type="text" class="span3" name="{$name}" id="{$name}" value="{$value}" {$attrs} />{$error}';
$R['input_text_disabled'] = '<input type="text" class="span2 disabled" name="{$name}" id="{$name}" value="{$value}" disabled {$attrs} />{$error}';
$R['input_text_medium'] = '<input type="text" class="span2" name="{$name}" id="{$name}" value="{$value}" {$attrs} />{$error}';
$R['input_text_small'] = '<input type="text" class="span1" name="{$name}" id="{$name}" value="{$value}" {$attrs} />{$error}';
$R['input_text_custom'] = '<input type="text" class="span1n5" name="{$name}" id="{$name}" value="{$value}" {$attrs} />{$error}';
$R['input_password'] = '<input type="password" class="span3" name="{$name}" id="{$name}" value="{$value}" {$attrs} />{$error}';
$R['input_textarea'] = '<textarea name="{$name}" id="{$name}" rows="{$rows}" cols="{$cols}"{$attrs}>{$value}</textarea>{$error}';
$R['input_textarea_editor'] =  '<textarea class="ckeditor" name="{$name}" rows="{$rows}" cols="{$cols}"{$attrs}>{$value}</textarea>{$error}';
$R['input_textarea_minieditor'] =  '<textarea class="ckeditor" name="{$name}" id="minieditor" rows="{$rows}" cols="{$cols}"{$attrs}>{$value}</textarea>{$error}';
$R['input_file'] = '<input type="file" class="span3" name="{$name}" {$attrs} />{$error}';
$R['input_file_empty'] = '<input type="file" name="{$name}" {$attrs} />{$error}';
$R['input_date'] =  '{$day} {$month} {$year} {$hour}: {$minute}';
$R['input_date_short'] =  '{$day} {$month} {$year}';

/**
 * Comments
 */
 
$R['link_deletecom'] = '<a href="{$url}">'.$lang['del'].'</a>';

/**
 * Global
 */
 
$R['javascript'] = '<script src="{$value}" type="text/javascript"></script>';
$R['css'] = '<link href="{$value}" rel="stylesheet">';
$R['code_title_page_num'] = ' (' . $lang['Page'] . ' {$num})';

/*
 * Installer
 */

$R['install_code_available'] = '<span class="valid">'.$lang['Available'].'</span>';
$R['install_code_found'] = '<span class="valid">'.$lang['Found'].'</span>';
$R['install_code_invalid'] = '<span class="invalid">{$text}</span>';
$R['install_code_not_available'] = '<span class="invalid">'.$lang['na'].'</span>';
$R['install_code_not_found'] = '<span class="invalid">'.$lang['nf'].'</span>';
$R['install_code_recommends'] = '<p class="recommends">'.$lang['install_recommends'].': '
	.$lang['Modules'].' - {$modules_list}; '.$lang['Plugins'].' - {$plugins_list}</p>';
$R['install_code_requires'] = '<p class="requires">'.$lang['install_requires'].': '
	.$lang['Modules'].' - {$modules_list}; '.$lang['Plugins'].' - {$plugins_list}</p>';
$R['install_code_valid'] = '<span class="valid">{$text}</span>';
$R['install_code_writable'] = '<span class="valid">'.$lang['install_writable'].'</span>';

?>