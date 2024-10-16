<?php

class CKN_Character {
    private $api_url = 'https://randomuser.me/api/';

    // Generate random character
    public function generate_character() {
        $response = wp_remote_get($this->api_url);
        if (is_wp_error($response)) {
            return null;
        }

        $data = json_decode(wp_remote_retrieve_body($response));
        if (!$data || empty($data->results)) {
            return null;
        }

        $user = $data->results[0];
        return [
            'first_name' => ucfirst($user->name->first),
            'last_name'  => ucfirst($user->name->last),
            'country'    => ucfirst($user->location->country)
        ];
    }

    // Store details in custom table
    public function save_character($user_id, $character_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ckn_characters';

        $wpdb->insert(
            $table_name,
            [
                'user_id'    => $user_id,
                'first_name' => $character_data['first_name'],
                'last_name'  => $character_data['last_name'],
                'country'    => $character_data['country'],
                'role'       => 'Cool Kid'
            ]
        );
    }

    public function get_character($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ckn_characters';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id), ARRAY_A);
    }
}