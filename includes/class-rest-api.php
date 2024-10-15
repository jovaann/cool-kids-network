<?php

// Register REST API routes
public function register_routes() {
    register_rest_route('ckn/v1', '/change-role', [
        'methods' => 'POST',
        'callback' => [$this, 'change_user_role'],
        'permission_callback' => [$this, 'is_request_authenticated'],
    ]);
}

// Authentication method
public function is_request_authenticated($request) {
    $api_key = $request->get_header('x-api-key');
    $valid_api_key = 'secret_api_key';
    if ($api_key && $api_key === $valid_api_key) {
        return true;
    }
    return new WP_Error('authentication_failed', 'Authentication failed.', ['status' => 401]);
}

// Map Cool Kid roles to WordPress roles
private function get_wp_role($role) {
    $role_mapping = [
        'Cool Kid' => 'cool_kid',
        'Cooler Kid' => 'cooler_kid',
        'Coolest Kid' => 'coolest_kid',
    ];
    return $role_mapping[$role] ?? null;
}