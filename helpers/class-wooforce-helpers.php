<?php

class WooForceHelpers {

    public static function get_sf_version_url() {

        $sf_version = wp_remote_get(BASE_URL . '/services/data/');
        $request_body = json_decode(wp_remote_retrieve_body($sf_version));
        $request_body = end($request_body);
        return $request_body->url;
    }

}


