-- ウィジェット登録
DELETE FROM _widgets WHERE wd_id = 'contactus';
INSERT INTO _widgets (wd_id, wd_name, wd_type, wd_version, wd_params, wd_author, wd_copyright, wd_license, wd_official_level, wd_description, wd_read_scripts, wd_read_css, wd_available, wd_editable, wd_has_admin, wd_enable_operation, wd_use_instance_def, wd_initialized, wd_launch_index, wd_cache_type, wd_view_control_type, wd_install_dt, wd_create_dt) VALUES
('contactus',          'お問い合わせ',            'CONU', '1.1.0',  '',        'Naoki Hirata', 'Magic3.org', 'GPL', 10, 'お問い合わせの入力データをメールで送る。',          false,           false,       true,         true,        true,        false,               false,true,               0, 0, 0, now(),    now());
