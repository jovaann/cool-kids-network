# cool-kids-network

Cool Kids Network is a proof of concept membership WordPress plugin.

**Features:**
- User registration
- User login
- Custom user roles (Cool Kid, Cooler Kid, Coolest Kid)
- Role-based user data depth access
- Role-based user data alteration

**Usage:**
Plugin offers 3 shortcodes:
  -- [ckn_registration] : Adds registration form
  -- [ckn_login] : Adds login form
  -- [ckn_character] : Adds my character panel

**How it works:**
The Cool Kids Network plugin is a custom WordPress plugin that allows users to register, log in, and manage characters based on specific roles: Cool Kid, Cooler Kid, and Coolest Kid. When users register, a character (name, country, etc.) is generated for them using the randomuser.me API, and they are assigned the "Cool Kid" role by default.

Users with the Cooler Kid role can view basic information (name and country) of all users, while those with the Coolest Kid role have access to more detailed data (email and role). "Coolest Kid" users can also change other users' roles via a secure front-end form, protected by a secret API key. The plugin includes a custom logout feature that displays a success message and redirects users to the homepage after logging out. All role-based functionalities are handled through a combination of REST API calls, AJAX, and role validation logic.
