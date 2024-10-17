<?php

// Registration logic with "already logged in" check
function ckn_render_registration_form() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        // Display "Already logged in" message with a logout button
        return '
            <div class="ckn-message">You are already logged in.</div>
            <div class="ckn-logout-button-container">
                <form method="post" action="' . wp_logout_url(home_url()) . '">
                    <button type="submit" class="ckn-button">Log Out</button>
                </form>
            </div>
        ';
    }

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

        // Redirect to the login page after successful registration
        wp_redirect(home_url('/login'));
        exit;
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


// Login logic with "already logged in" check
function ckn_render_login_form() {
    // Check if the user is already logged in
    if (is_user_logged_in()) {
        // Display "Already logged in" message with a logout button
        return '
            <div class="ckn-message">You are already logged in.</div>
            <div class="ckn-logout-button-container">
                <form method="post" action="' . wp_logout_url(home_url()) . '">
                    <button type="submit" class="ckn-button">Log Out</button>
                </form>
            </div>
        ';
    }

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

// Display character data for logged-in users and a table for Cooler Kid / Coolest Kid
function ckn_render_character_view() {
    if (!is_user_logged_in()) {
        return '<div class="ckn-message">You need to log in to see your character.</div>';
    }

    $user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    $character = new CKN_Character();
    $data = $character->get_character($user_id);

    if (!$data) {
        return '<div class="ckn-message">Character not found.</div>';
    }

    // Logged-in user's character data
    $output = '<div class="ckn-character-info">';
    $output .= sprintf(
        '<h3 class="ckn-section-title">Your Character</h3>
        <div class="ckn-character-field">Name: %s %s</div>
        <div class="ckn-character-field">Country: %s</div>
        <div class="ckn-character-field">Email: %s</div>
        <div class="ckn-character-field">Role: %s</div>',
        $data['first_name'],
        $data['last_name'],
        $data['country'],
        wp_get_current_user()->user_email,
        $data['role']
    );
    $output .= '</div>';

    $output .= '
        <div class="ckn-logout-button-container">
            <button id="ckn-logout-button" class="ckn-button">Log Out</button>
        </div>
        <div id="ckn-logout-message" class="ckn-message green" style="display: none;">Logout successful!</div>
    ';

    if (in_array('cooler_kid', $current_user->roles) || in_array('coolest_kid', $current_user->roles)) {
        $users = ckn_get_users_by_role();

        // Table for Cooler Kid (Names, Countries)
        if (in_array('cooler_kid', $current_user->roles)) {
            $output .= '<div class="ckn-users-list">';
            $output .= '<h3 class="ckn-section-title">All Users (Names and Countries)</h3>';
            $output .= '<table class="ckn-table"><tr><th>Name</th><th>Country</th></tr>';

            foreach ($users as $user) {
                $output .= sprintf('<tr><td>%s</td><td>%s</td></tr>', $user['name'], $user['country']);
            }

            $output .= '</table>';
            $output .= '</div>';
        }

        // Table for Coolest Kid (Names, Countries, Emails, Roles)
        if (in_array('coolest_kid', $current_user->roles)) {
            $output .= '<div class="ckn-users-list">';
            $output .= '<h3 class="ckn-section-title">All Users (Names, Countries, Emails, and Roles)</h3>';
            $output .= '<table class="ckn-table"><tr><th>Name</th><th>Country</th><th>Email</th><th>Role</th></tr>';

            foreach ($users as $user) {
                $output .= sprintf(
                    '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                    $user['name'],
                    $user['country'],
                    $user['email'],
                    $user['role']
                );
            }

            $output .= '</table>';
            $output .= '</div>';

            // Role change form
            $output .= '<div class="ckn-role-change-form">';
            $output .= '
                <h3 class="ckn-section-title">Change User Role</h3>
                <form method="POST" id="role-change-form" class="ckn-form">
                    <label for="email">User Email:</label>
                    <input type="email" name="email" id="email" class="ckn-input" required>

                    <label for="role">Select New Role:</label>
                    <select name="role" id="role" class="ckn-input">
                        <option value="Cool Kid">Cool Kid</option>
                        <option value="Cooler Kid">Cooler Kid</option>
                        <option value="Coolest Kid">Coolest Kid</option>
                    </select>

                    <label for="secret_key">Secret API Key: (secret_api_key)</label>
                    <input type="password" name="secret_key" id="secret_key" class="ckn-input" required>

                    <button type="submit" class="ckn-button">Change Role</button>
                </form>
                <div id="role-change-message" class="ckn-message"></div>
            ';
            $output .= '</div>';
        }
    }

    return $output;
}