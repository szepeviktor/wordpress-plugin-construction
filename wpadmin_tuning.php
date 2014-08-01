<?php

// Toggles input field descriptions on any dashboard page.
// This is a snippet.


function o1_wpadmin_tuning() {
    //TODO get option, get nonce, set up ajax, set option in ajax function

    $screen = get_current_screen();
    $tuning_tab = '<p><label for="o1-disable-description">Hide descriptions
        <input id="o1-disable-description" name="o1-disable-description" type="checkbox"/></label>
        <script>jQuery("#o1-disable-description").click(function(){jQuery("p.description").slideToggle()});</script></p>';

    $screen->add_help_tab( array(
        'id' => 'o1_wpadmin_tuning',
        'title' => 'Dashboard tuning',
        'content' => $tuning_tab
    ) );
}
add_action( 'current_screen', 'o1_wpadmin_tuning' );

