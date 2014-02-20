
-- settings

DELETE FROM `top`, `toc`, `to` USING `sys_options_types` AS `top` LEFT JOIN `sys_options_categories` AS `toc` ON `top`.`id`=`toc`.`type_id` LEFT JOIN `sys_options` AS `to` ON `toc`.`id`=`to`.`category_id` WHERE `top`.`name`='bx_antispam';

-- page: ip table

DELETE FROM `sys_objects_page` WHERE `object`='bx_antispam_ip_table';
DELETE FROM `sys_pages_blocks` WHERE `object`='bx_antispam_ip_table';

-- grid: ip table

DELETE FROM `sys_objects_grid` WHERE `object` = 'bx_antispam_grid_ip_table';
DELETE FROM `sys_grid_fields` WHERE `object` = 'bx_antispam_grid_ip_table';
DELETE FROM `sys_grid_actions` WHERE `object` = 'bx_antispam_grid_ip_table';

-- data list: ip table actions

DELETE FROM `sys_form_pre_lists` WHERE `key` = 'bx_antispam_ip_table_actions';
DELETE FROM `sys_form_pre_values` WHERE `Key` = 'bx_antispam_ip_table_actions';

-- form: IP table add/edit

DELETE FROM `sys_objects_form` WHERE `object`='bx_antispam_ip_table_form';
DELETE FROM `sys_form_displays` WHERE `object`='bx_antispam_ip_table_form';
DELETE FROM `sys_form_inputs` WHERE `object`='bx_antispam_ip_table_form';
DELETE FROM `sys_form_display_inputs` WHERE `display_name`='bx_antispam_ip_table_form_add';
DELETE FROM `sys_form_display_inputs` WHERE `display_name`='bx_antispam_ip_table_form_edit';
