// voting.js - Handles post and answer voting functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle all vote buttons on the page
    const voteButtons = document.querySelectorAll('.vote-btn');

    voteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Check if user is logged in
            if (button.classList.contains('guest-vote')) {
                window.location.href = loginUrl;
                return;
            }

            const form = button.closest('form');
            const formData = new FormData(form);
            const url = form.getAttribute('action');
            const voteValue = formData.get('value');

            // Determine if this is a post or answer vote
            const isPostVote = url.includes('/posts/') && !url.includes('/answers/');
            const targetId = isPostVote
                ? url.match(/\/posts\/(\d+)\/vote/)[1]
                : url.match(/\/answers\/(\d+)\/vote/)[1];

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': formData.get('_token')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Update the score display
                const scoreElement = document.querySelector(
                    `.vote-score[data-${isPostVote ? 'post' : 'answer'}-id="${targetId}"]`
                );

                if (scoreElement) {
                    scoreElement.textContent = data.score;
                }

                // Find the vote container that contains both upvote and downvote buttons
                const voteContainer = button.closest('.vote-container');
                const upvoteBtn = voteContainer.querySelector('.upvote-btn');
                const downvoteBtn = voteContainer.querySelector('.downvote-btn');

                // Reset button styles
                upvoteBtn.classList.remove('active-vote', 'bg-green-100', 'dark:bg-green-900',
                    'text-green-600', 'dark:text-green-400');
                upvoteBtn.classList.add('bg-gray-100', 'dark:bg-gray-700',
                    'text-gray-600', 'dark:text-gray-400');

                downvoteBtn.classList.remove('active-vote', 'bg-red-100', 'dark:bg-red-900',
                    'text-red-600', 'dark:text-red-400');
                downvoteBtn.classList.add('bg-gray-100', 'dark:bg-gray-700',
                    'text-gray-600', 'dark:text-gray-400');

                // Set active style based on the user's current vote
                if (data.userVote === 1) {
                    upvoteBtn.classList.remove('bg-gray-100', 'dark:bg-gray-700',
                        'text-gray-600', 'dark:text-gray-400');
                    upvoteBtn.classList.add('active-vote', 'bg-green-100', 'dark:bg-green-900',
                        'text-green-600', 'dark:text-green-400');
                } else if (data.userVote === -1) {
                    downvoteBtn.classList.remove('bg-gray-100', 'dark:bg-gray-700',
                        'text-gray-600', 'dark:text-gray-400');
                    downvoteBtn.classList.add('active-vote', 'bg-red-100', 'dark:bg-red-900',
                        'text-red-600', 'dark:text-red-400');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
