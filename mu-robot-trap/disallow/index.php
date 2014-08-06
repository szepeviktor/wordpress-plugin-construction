<?php
// put this in directory called /disallow/

$rt_ban_count = 6;

for ($i = 1; $i <= $rt_ban_count; $i++) {
    error_log('File does not exist: robot_nofollow_trap');
}
die;
