<?php

private $api_url = 'https://randomuser.me/api/';

// Generate a random character using RandomUser.me API
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