<?php

function adminer_object() {
    $plugins = array(
        new AdminerWPLogin,
    );
    return new AdminerPlugin($plugins);
}
