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