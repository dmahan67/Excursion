<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
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
$R['link_deletecom'] = '<a href="{$url}">'.$lang['del'].'</a>';
$R['javascript'] = '<script src="{$value}" type="text/javascript"></script>';
$R['css'] = '<link href="{$value}" rel="stylesheet">';
$R['code_title_page_num'] = ' (' . $L['Page'] . ' {$num})';

/**
 * Pagination
 */

$R['link_pagenav_current'] = '<span class="pagenav_current"><a href="{$url}"{$event}{$rel}>{$num}</a></span>';
$R['link_pagenav_first'] = '<span class="pagenav_first"><a href="{$url}"{$event}{$rel}>'.$L['pagenav_first'].'</a></span>';
$R['link_pagenav_gap'] = '<span class="pagenav_pages">...</span>';
$R['link_pagenav_last'] = '<span class="pagenav_last"><a href="{$url}"{$event}{$rel}>'.$L['pagenav_last'].'</a></span>';
$R['link_pagenav_main'] = '<span class="pagenav_pages"><a href="{$url}"{$event}{$rel}>{$num}</a></span>';
$R['link_pagenav_next'] = '<span class="pagenav_next"><a href="{$url}"{$event}{$rel}>'.$L['pagenav_next'].'</a></span>';
$R['link_pagenav_prev'] = '<span class="pagenav_prev"><a href="{$url}"{$event}{$rel}>'.$L['pagenav_prev'].'</a></span>';

?>