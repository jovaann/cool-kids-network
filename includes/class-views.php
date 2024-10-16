<?php

// Registration logic
function ckn_render_registration_form() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = sanitize_email($_POST['email']);

        if (!is_email($email) || email_exists($email)) {
            return '<div class="ckn-message">Invalid email or email already exists.</div>';
        }

        $user_id = wp_create_user($email, wp_generate_password(), $email);
        if (is_wp_error($user_id)) {
            return '<div class="ckn-message">Error creating user.</div>';
        }

        $user = new WP_User($user_id);
        $user->set_role('cool_kid');

        $character = new CKN_Character();
        $character_data = $character->generate_character();
        if ($character_data) {
            $character->save_character($user_id, $character_data);
        }

        return '<div class="ckn-message">Registration successful! Your character has been created.</div>';
    }

    // Registration form
    return '
        <div class="ckn-form-container">
            <h3 class="ckn-section-title">Register</h3>
            <form method="POST" class="ckn-form">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="ckn-input" required>
                <button type="submit" class="ckn-button">Confirm</button>
            </form>
        </div>
    ';
}

// Login logic
function ckn_render_login_form() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = sanitize_email($_POST['email']);
        $user = get_user_by('email', $email);

        if (!$user) {
            return '<div class="ckn-message">User not found.</div>';
        }

        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);

        wp_redirect(home_url('/my-account'));
        exit;
    }

    // Login form
    return '
        <div class="ckn-form-container">
            <h3 class="ckn-section-title">Login</h3>
            <form method="POST" class="ckn-form">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="ckn-input" required>
                <button type="submit" class="ckn-button">Login</button>
            </form>
        </div>
    ';
}

// Fetch users with roles 'Cool Kid', 'Cooler Kid', 'Coolest Kid'
function ckn_get_users_by_role() {
    $args = [
        'role__in' => ['cool_kid', 'cooler_kid', 'coolest_kid'],
        'fields' => ['ID', 'user_email'],
    ];

    $users = get_users($args);
    $user_data = [];

    foreach ($users as $user) {
        $user_id = $user->ID;
        $character = new CKN_Character();
        $character_data = $character->get_character($user_id);

        if ($character_data) {
            $user_data[] = [
                'name' => $character_data['first_name'] . ' ' . $character_data['last_name'],
                'country' => $character_data['country'],
                'email' => $user->user_email,
                'role' => $character_data['role'],
            ];
        }
    }

    return $user_data;
}