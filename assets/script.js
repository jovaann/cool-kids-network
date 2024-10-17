document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('role-change-form');
    const messageBox = document.getElementById('role-change-message');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            messageBox.textContent = '';

            const email = document.getElementById('email').value;
            const role = document.getElementById('role').value;
            const secretKey = document.getElementById('secret_key').value;

            if (!email || !role || !secretKey) {
                messageBox.textContent = 'Please fill in all fields.';
                messageBox.style.color = 'red';
                return;
            }

            if (secretKey !== 'secret_api_key') {
                messageBox.textContent = 'Invalid Secret Key!';
                messageBox.style.color = 'red';
                return;
            }

            fetch('/wp-json/ckn/v1/change-role', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'x-api-key': secretKey
                },
                body: JSON.stringify({
                    email: email,
                    role: role
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    messageBox.textContent = data.message;
                    messageBox.style.color = 'green';
                } else if (data.code) {
                    messageBox.textContent = data.message || 'Error changing role';
                    messageBox.style.color = 'red';
                }
            })
            .catch(error => {
                messageBox.textContent = 'Error changing role';
                messageBox.style.color = 'red';
            });
        });
    }

    const logoutButton = document.getElementById('ckn-logout-button');
    const logoutMessage = document.getElementById('ckn-logout-message');

    if (logoutButton) {
        logoutButton.addEventListener('click', function () {
            fetch('/wp-admin/admin-ajax.php?action=ckn_logout', {
                method: 'POST',
                credentials: 'same-origin',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    logoutMessage.style.display = 'block';
                    
                    setTimeout(function () {
                        window.location.href = '/';
                    }, 2000);
                } else {
                    console.error('Logout failed');
                }
            })
            .catch(error => {
                console.error('Error logging out:', error);
            });
        });
    }
});
