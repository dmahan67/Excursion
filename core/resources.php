<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */

$R['code_option_empty'] = '---';
$R['code_time_separator'] = ':';
$R['input_checkbox'] = '<label><input type="checkbox" name="{$name}" value="{$value}"{$checked}{$attrs} /> {$title}</label>';
$R['input_default'] = '<input type="{$type}" class="xlarge" name="{$name}" value="{$value}"{$attrs} />{$error}';
$R['input_option'] = '<option value="{$value}"{$selected}>{$title}</option>';
$R['input_radio'] = '<label><input type="radio" name="{$name}" value="{$value}"{$checked}{$attrs} /> {$title}</label>';
$R['input_radio_separator'] = ' ';
$R['input_select'] = '<select name="{$name}"{$attrs}>{$options}</select>{$error}';
$R['input_text'] = '<input type="text" class="xlarge" name="{$name}" value="{$value}" {$attrs} />{$error}';
$R['input_textarea'] = '<textarea name="{$name}" rows="{$rows}" cols="{$cols}"{$attrs}>{$value}</textarea>{$error}';
$R['input_textarea_editor'] =  '<textarea class="ckeditor" name="{$name}" rows="{$rows}" cols="{$cols}"{$attrs}>{$value}</textarea>{$error}';
$R['input_textarea_medieditor'] =  '<textarea class="editor" name="{$name}" rows="{$rows}" cols="{$cols}"{$attrs}>{$value}</textarea>{$error}';
$R['input_file'] = '<input type="file" class="xlarge" name="{$name}" {$attrs} />{$error}';
$R['input_file_empty'] = '<input type="file" name="{$name}" {$attrs} />{$error}';
$R['input_date'] =  '{$day} {$month} {$year} {$hour}: {$minute}';
$R['input_date_short'] =  '{$day} {$month} {$year}';

?>