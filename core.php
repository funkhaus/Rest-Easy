<?php

/*
 * Output JSON data when requested
 */
	function rez_output_api_data($wp){

        // detect if client is trying to get json
		$json_header = $_SERVER['CONTENT_TYPE'] == 'application/json';
		$json_type = $_REQUEST['contentType'] == 'json';

        // if so...
		if ( $json_header || $json_type ){

            // notify client we are sending json
			header('Content-Type: application/json');

            // load data, encode, output
			echo json_encode(rez_build_all_data());
			exit();
		}

	}
	add_action('wp', 'rez_output_api_data');

/*
 * Localize data into first available script
 */
    function rez_localize_data() {
        global $wp_scripts;

        // make sure we have at least one script queued
        if ( $first_script = reset($wp_scripts->queue) ){

            // get all data and localize to first script
            wp_localize_script($first_script, 'jsonData', rez_build_all_data());
        }
    }
    add_action('wp_enqueue_scripts', 'rez_localize_data', 100);
