// hybrid-voting.js - Hybrid voting system with optimistic UI updates
document.addEventListener('DOMContentLoaded', function () {
    // Get CSRF token from meta tag for all AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Track active vote requests by target ID
    const activeVoteRequests = {};

    // Handle all vote buttons on the page
    const voteButtons = document.querySelectorAll('.vote-btn');

    voteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const form = button.closest('.vote-form');
            if (!form) {
                console.error('Could not find parent form element');
                return;
            }

            const formData = new FormData(form);
            const url = form.getAttribute('action');
            const voteValue = formData.get('value');

            // Determine if this is a post or answer vote
            const isPostVote = url.includes('/posts/') && !url.includes('/answers/');

            // Extract the target ID from the URL
            let targetId;
            if (isPostVote) {
                const match = url.match(/\/posts\/(\d+)\/vote/);
                targetId = match ? match[1] : null;
            } else {
                const match = url.match(/\/answers\/(\d+)\/vote/);
                targetId = match ? match[1] : null;
            }

            if (!targetId) {
                console.error('Could not determine target ID from URL:', url);
                return;
            }

            // Check if there's already an active request for this target
            if (activeVoteRequests[targetId]) {
                console.log(`Vote request already in progress for ${isPostVote ? 'post' : 'answer'} ${targetId}`);
                return;
            }

            // Find the vote container either by data attribute on the container or by searching for a score element
            const voteContainers = document.querySelectorAll(
                `.vote-container[data-${isPostVote ? 'post' : 'answer'}-id="${targetId}"], ` +
                `.vote-container:has(.vote-score[data-${isPostVote ? 'post' : 'answer'}-id="${targetId}"])`
            );

            // Find all vote buttons for this target to disable them
            const targetVoteButtons = [];
            voteContainers.forEach(container => {
                const btns = container.querySelectorAll('.vote-btn');
                btns.forEach(btn => targetVoteButtons.push(btn));
            });

            // Disable all vote buttons for this target
            targetVoteButtons.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });

            // Mark this target as having an active request
            activeVoteRequests[targetId] = true;

            // Store the previous states for potential rollback
            const previousStates = [];

            voteContainers.forEach(container => {
                // Find the score element, which might be hidden if showScore is false
                const scoreElement = container.querySelector(`.vote-score[data-${isPostVote ? 'post' : 'answer'}-id="${targetId}"]`);
                if (!scoreElement) return; // Skip if not related to current vote

                const upvoteBtn = container.querySelector('.upvote-btn');
                const downvoteBtn = container.querySelector('.downvote-btn');

                if (!upvoteBtn || !downvoteBtn) return;

                // Save previous state
                previousStates.push({
                    container,
                    scoreElement,
                    score: scoreElement.textContent.trim(),
                    upvoteBtn: {
                        element: upvoteBtn,
                        classList: [...upvoteBtn.classList],
                        disabled: upvoteBtn.disabled
                    },
                    downvoteBtn: {
                        element: downvoteBtn,
                        classList: [...downvoteBtn.classList],
                        disabled: downvoteBtn.disabled
                    }
                });
            });

            // Apply optimistic UI update immediately
            const upvoteActive = button.classList.contains('upvote-btn') && !button.classList.contains('active-vote');
            const downvoteActive = button.classList.contains('downvote-btn') && !button.classList.contains('active-vote');
            const removingVote = button.classList.contains('active-vote');

            voteContainers.forEach(container => {
                const scoreElement = container.querySelector(`.vote-score[data-${isPostVote ? 'post' : 'answer'}-id="${targetId}"]`);
                if (!scoreElement) return; // Skip if not related to current vote

                const currentScore = parseInt(scoreElement.textContent.trim());
                const upvoteBtn = container.querySelector('.upvote-btn');
                const downvoteBtn = container.querySelector('.downvote-btn');

                if (!upvoteBtn || !downvoteBtn) return;

                // Update score based on action type
                let newScore = currentScore;
                if (upvoteActive) {
                    // If already downvoted, remove downvote (+1) and add upvote (+1) = +2
                    if (downvoteBtn.classList.contains('active-vote')) {
                        newScore += 2;
                    } else {
                        // Just adding an upvote
                        newScore += 1;
                    }
                } else if (downvoteActive) {
                    // If already upvoted, remove upvote (-1) and add downvote (-1) = -2
                    if (upvoteBtn.classList.contains('active-vote')) {
                        newScore -= 2;
                    } else {
                        // Just adding a downvote
                        newScore -= 1;
                    }
                } else if (removingVote) {
                    // Removing an existing vote
                    if (button.classList.contains('upvote-btn')) {
                        newScore -= 1; // Remove upvote
                    } else {
                        newScore += 1; // Remove downvote
                    }
                }

                // Update the score display
                scoreElement.textContent = newScore.toString();

                // Update button styles
                resetButtonStyles(upvoteBtn, downvoteBtn);

                if (upvoteActive) {
                    setActiveUpvote(upvoteBtn);
                } else if (downvoteActive) {
                    setActiveDownvote(downvoteBtn);
                }
                // If removing a vote, both buttons remain grey (resetButtonStyles took care of this)
            });

            // Make the actual server request
            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    // Attempt to parse error response
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || `Server responded with ${response.status}`);
                    }).catch(err => {
                        // If we can't parse the JSON, just throw the status error
                        throw new Error(`Server responded with ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                // Server request successful, update the UI with the actual server data
                voteContainers.forEach(container => {
                    const scoreElement = container.querySelector(`.vote-score[data-${isPostVote ? 'post' : 'answer'}-id="${targetId}"]`);
                    if (!scoreElement) return;

                    // Update with the server's score value (just to be sure it's in sync)
                    scoreElement.textContent = data.score;

                    const upvoteBtn = container.querySelector('.upvote-btn');
                    const downvoteBtn = container.querySelector('.downvote-btn');

                    if (!upvoteBtn || !downvoteBtn) return;

                    // Reset button styles for both buttons
                    resetButtonStyles(upvoteBtn, downvoteBtn);

                    // Convert vote value to number and check
                    const voteValue = Number(data.userVote);

                    if (voteValue === 1) {
                        setActiveUpvote(upvoteBtn);
                    } else if (voteValue === -1) {
                        setActiveDownvote(downvoteBtn);
                    }
                    // If voteValue is 0 or NaN, both buttons remain grey (vote removed)
                });

                // Show success notification if needed
                if (data.message) {
                    showNotification(data.message, 'success');
                }
            })
            .catch(error => {
                console.error('Error:', error);

                // Show error notification
                showNotification('Error processing your vote. Please try again.', 'error');

                // Revert to previous state on error
                previousStates.forEach(state => {
                    // Restore score
                    state.scoreElement.textContent = state.score;

                    // Restore upvote button classes
                    const upvoteBtn = state.upvoteBtn.element;
                    upvoteBtn.className = ''; // Clear all classes
                    state.upvoteBtn.classList.forEach(cls => upvoteBtn.classList.add(cls));

                    // Restore downvote button classes
                    const downvoteBtn = state.downvoteBtn.element;
                    downvoteBtn.className = ''; // Clear all classes
                    state.downvoteBtn.classList.forEach(cls => downvoteBtn.classList.add(cls));
                });
            })
            .finally(() => {
                // Clean up: remove this target from active requests and re-enable buttons
                delete activeVoteRequests[targetId];

                // Re-enable all vote buttons for this target
                targetVoteButtons.forEach(btn => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            });
        });
    });

    // Helper function to reset button styles
    function resetButtonStyles(upvoteBtn, downvoteBtn) {
        // Determine if we're in compact mode
        const isCompact = upvoteBtn.classList.contains('text-sm');

        if (isCompact) {
            // Handle compact mode styling
            upvoteBtn.classList.remove('active-vote', 'text-green-600', 'dark:text-green-400');
            upvoteBtn.classList.add('text-gray-500', 'dark:text-gray-400');

            downvoteBtn.classList.remove('active-vote', 'text-red-600', 'dark:text-red-400');
            downvoteBtn.classList.add('text-gray-500', 'dark:text-gray-400');
        } else {
            // Handle normal mode styling
            upvoteBtn.classList.remove('active-vote', 'bg-green-100', 'dark:bg-green-900',
                'text-green-600', 'dark:text-green-400');
            upvoteBtn.classList.add('bg-gray-100', 'dark:bg-gray-700',
                'text-gray-600', 'dark:text-gray-400');

            downvoteBtn.classList.remove('active-vote', 'bg-red-100', 'dark:bg-red-900',
                'text-red-600', 'dark:text-red-400');
            downvoteBtn.classList.add('bg-gray-100', 'dark:bg-gray-700',
                'text-gray-600', 'dark:text-gray-400');
        }
    }

    // Helper function to set active upvote style
    function setActiveUpvote(upvoteBtn) {
        // Determine if we're in compact mode
        const isCompact = upvoteBtn.classList.contains('text-sm');

        if (isCompact) {
            // Handle compact mode styling
            upvoteBtn.classList.remove('text-gray-500', 'dark:text-gray-400');
            upvoteBtn.classList.add('active-vote', 'text-green-600', 'dark:text-green-400');
        } else {
            // Handle normal mode styling
            upvoteBtn.classList.remove('bg-gray-100', 'dark:bg-gray-700',
                'text-gray-600', 'dark:text-gray-400');
            upvoteBtn.classList.add('active-vote', 'bg-green-100', 'dark:bg-green-900',
                'text-green-600', 'dark:text-green-400');
        }
    }

    // Helper function to set active downvote style
    function setActiveDownvote(downvoteBtn) {
        // Determine if we're in compact mode
        const isCompact = downvoteBtn.classList.contains('text-sm');

        if (isCompact) {
            // Handle compact mode styling
            downvoteBtn.classList.remove('text-gray-500', 'dark:text-gray-400');
            downvoteBtn.classList.add('active-vote', 'text-red-600', 'dark:text-red-400');
        } else {
            // Handle normal mode styling
            downvoteBtn.classList.remove('bg-gray-100', 'dark:bg-gray-700',
                'text-gray-600', 'dark:text-gray-400');
            downvoteBtn.classList.add('active-vote', 'bg-red-100', 'dark:bg-red-900',
                'text-red-600', 'dark:text-red-400');
        }
    }

    // Helper function to show notifications
    function showNotification(message, type = 'success') {
        // Check if a notification container exists, if not create one
        let notificationContainer = document.getElementById('notification-container');

        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.id = 'notification-container';
            notificationContainer.className = 'fixed bottom-4 right-4 z-50';
            document.body.appendChild(notificationContainer);
        }

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `mb-2 p-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-0
            ${type === 'error' ? 'bg-red-100 border-l-4 border-red-600 text-red-700' : 'bg-green-100 border-l-4 border-green-600 text-green-700'}`;

        notification.innerHTML = `
            <div class="flex justify-between items-center">
                <span>${message}</span>
                <button class="ml-4 text-gray-500 hover:text-gray-700" onclick="this.parentElement.parentElement.remove()">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;

        // Add to the DOM
        notificationContainer.appendChild(notification);

        // Automatically remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.add('opacity-0');
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }
});
