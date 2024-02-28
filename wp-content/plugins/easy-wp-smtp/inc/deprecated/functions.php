<?php

function swpsmtp_uninstall() {
	// Don't delete plugin options. It is better to retain the options so if someone accidentally deactivates, the configuration is not lost.
	//delete_site_option('swpsmtp_options');
	//delete_option('swpsmtp_options');
}
