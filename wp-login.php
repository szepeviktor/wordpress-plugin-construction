<?php

// a trigger for fail2ban in non-WP projects

for ($i = 1; $i <= 6; $i++) {
    error_log('File does not exist: ' . 'login_no-wp-here');
}

die;

