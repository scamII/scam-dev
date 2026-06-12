<?php

class Scam_Dev_Theme_Updater {

    private $theme_slug;
    private $theme_version;
    private $update_url;

    public function __construct() {
        $theme = wp_get_theme();
        $this->theme_slug    = $theme->get_template();
        $this->theme_version = $theme->get( 'Version' );
        $this->update_url    = 'https://scam-dev.ru/theme-update.json';

        add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_update' ) );
        add_filter( 'auto_update_theme', array( $this, 'auto_update' ), 10, 2 );
    }

    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $response = wp_remote_get( $this->update_url, array( 'timeout' => 10 ) );
        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return $transient;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! isset( $data['version'] ) || ! isset( $data['download_url'] ) ) {
            return $transient;
        }

        if ( version_compare( $this->theme_version, $data['version'], '<' ) ) {
            $transient->response[ $this->theme_slug ] = array(
                'theme'       => $this->theme_slug,
                'new_version' => $data['version'],
                'url'         => isset( $data['url'] ) ? $data['url'] : '',
                'package'     => $data['download_url'],
                'requires'    => isset( $data['requires'] ) ? $data['requires'] : '6.5',
                'requires_php' => isset( $data['requires_php'] ) ? $data['requires_php'] : '8.0',
            );
        }

        return $transient;
    }

    public function auto_update( $update, $item ) {
        if ( isset( $item['theme'] ) && $item['theme'] === $this->theme_slug ) {
            return true;
        }
        return $update;
    }
}

new Scam_Dev_Theme_Updater();
