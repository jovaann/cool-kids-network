<?php

class CKN_REST_API {
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
    // Callback to change user role
    public function change_user_role($request) {
        $email = sanitize_email($request['email']);
        $first_name = sanitize_text_field($request['first_name']);
        $last_name = sanitize_text_field($request['last_name']);
        $new_role = sanitize_text_field($request['role']);

        $wp_role = $this->get_wp_role($new_role);
        if (!$wp_role) {
            return new WP_Error('invalid_role', 'The provided role is not valid.', ['status' => 400]);
        }

        if (!empty($email)) {
            $user = get_user_by('email', $email);
        } elseif (!empty($first_name) && !empty($last_name)) {
            $user = get_users([
                'meta_key' => 'first_name',
                'meta_value' => $first_name,
                'meta_query' => [
                    ['key' => 'last_name', 'value' => $last_name]
                ]
            ])[0] ?? null;
        } else {
            return new WP_Error('missing_info', 'No valid email or name provided.', ['status' => 400]);
        }

        if (!$user) {
            return new WP_Error('not_found', 'User not found.', ['status' => 404]);
        }

        $user->set_role($wp_role);

        return new WP_REST_Response(['message' => 'Role updated successfully.'], 200);
    }
}