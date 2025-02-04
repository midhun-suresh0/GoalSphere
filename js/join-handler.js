document.addEventListener('DOMContentLoaded', () => {
    const joinForm = document.getElementById('join-form');
    
    if (joinForm) {
        joinForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Get form data
            const formData = new FormData(joinForm);

            try {
                const response = await fetch('register.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    alert(data.message);
                    // Redirect to login page
                    window.location.href = 'signin.html';
                } else {
                    // Show error message
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred during registration');
            }
        });
    }
}); 