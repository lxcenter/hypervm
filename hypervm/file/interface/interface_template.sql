--
--  Create table interface_template if not exists
--  Add UNIQUE to nname field
--  Insert ignore will not insert if the unique name already exists (novice).
--  This avoids drop and create the table every /script/cleanup and at scavenge time. Saves time?
--
SET sql_notes = 0;      -- Temporarily disable the "Table already exists" warning
CREATE TABLE IF NOT EXISTS `interface_template` (
  `nname` varchar(255) NOT NULL,
  `parent_clname` varchar(255) default NULL,
  `parent_cmlist` text,
  `ser_domain_show_list` text,
  `ser_client_show_list` text,
  `ser_vps_show_list` text,
  PRIMARY KEY  (`nname`),
  KEY `parent_clname_interface_template` (`parent_clname`),
  UNIQUE (`nname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER IGNORE TABLE `interface_template` ADD UNIQUE (`nname`);

INSERT IGNORE INTO `interface_template` VALUES ('novice','client_s_vv_p_admin','','Tjs=','YTo0OntpOjA7czo0ODoiYnoxaGRYaHBiR2xoY25rbVlUMTFjR1JoZEdWbWIzSnRKbk5oUFhCaGMzTjNiM0prIjtpOjE7czozNjoiWVQxMWNHUmhkR1ZHYjNKdEpuTmhQV2x1Wm05eWJXRjBhVzl1IjtpOjI7czoxNjoiWVQxc2FYTjBKbU05ZG5CeiI7aTozO3M6MjA6IllUMXNhWE4wSm1NOWRYUnRjQT09Ijt9','YToxMzp7aTowO3M6MTI6Il9fdGl0bGVfbWFpbiI7aToxO3M6MzI6IllUMTFjR1JoZEdWbWIzSnRKbk5oUFhCaGMzTjNiM0prIjtpOjI7czozNjoiWVQxMWNHUmhkR1ZHYjNKdEpuTmhQV2x1Wm05eWJXRjBhVzl1IjtpOjM7czoyNDoiWVQxemFHOTNKbTg5ZG01amRtbGxkMlZ5IjtpOjQ7czoyODoiWVQxemFHOTNKbTg5WTI5dWMyOXNaWE56YUE9PSI7aTo1O3M6MzI6IllUMTFjR1JoZEdWbWIzSnRKbk5oUFc1bGRIZHZjbXM9IjtpOjY7czozMjoiWVQxMWNHUmhkR1ZtYjNKdEpuTmhQWEpsWW5WcGJHUT0iO2k6NztzOjQwOiJZVDExY0dSaGRHVm1iM0p0Sm5OaFBYSnZiM1J3WVhOemQyOXlaQT09IjtpOjg7czo0NDoiWVQxemFHOTNKbXhiWTJ4aGMzTmRQV1ptYVd4bEpteGJibTVoYldWZFBTOD0iO2k6OTtzOjI0OiJZVDExY0dSaGRHVW1jMkU5Y21WaWIyOTAiO2k6MTA7czoyODoiWVQxMWNHUmhkR1VtYzJFOWNHOTNaWEp2Wm1ZPSI7aToxMTtzOjI0OiJZVDFzYVhOMEptTTliM0JsYm5aNmNXOXoiO2k6MTI7czoyNDoiWVQxc2FYTjBKbU05WW14dlkydGxaR2x3Ijt9');

SET sql_notes = 1;      -- Enable warnings again

-- Unknown usage within HyperVM, anyone can tell what this is doing?
