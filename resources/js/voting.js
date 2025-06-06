// resources\js\voting.js
// optimized-voting.js - Hybrid voting system with optimistic UI updates
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const activeVoteRequests = {};

    document.querySelectorAll('.vote-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const form = button.closest('.vote-form');
            if (!form) return;

            const url = form.getAttribute('action');
            const isPostVote = url.includes('/posts/') && !url.includes('/answers/');

            // Extract the target ID from the vote container data attribute instead of URL
            const voteContainer = button.closest('.vote-container');
            if (!voteContainer) return;

            const typeAttr = isPostVote ? 'post' : 'answer';
            const targetId = voteContainer.getAttribute(`data-${typeAttr}-id`);

            if (!targetId || activeVoteRequests[targetId]) return;

            const voteContainers = document.querySelectorAll(
                `.vote-container[data-${typeAttr}-id="${targetId}"], ` +
                `.vote-container:has(.vote-score[data-${typeAttr}-id="${targetId}"])`
            );

            // Store previous states for potential rollback
            const previousStates = captureState(voteContainers, typeAttr, targetId);

            // Disable vote buttons and mark request as active
            activeVoteRequests[targetId] = true;
            toggleButtonsState(voteContainers, true);

            // Apply optimistic UI update
            const isUpvote = button.classList.contains('upvote-btn');
            const isActive = button.classList.contains('active-vote');
            updateVoteUI(voteContainers, typeAttr, targetId, isUpvote, isActive);

            // Make the server request
            const formData = new FormData(form);
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
                        return response.json()
                            .then(errorData => { throw new Error(errorData.message || `Server error: ${response.status}`); })
                            .catch(() => { throw new Error(`Server error: ${response.status}`); });
                    }
                    return response.json();
                })
                .then(data => {
                    // Update UI with server data
                    updateUIWithServerData(voteContainers, typeAttr, targetId, data);
                    // FIXED: Only show toast if showToast is not explicitly false
                    if (data.showToast !== false && data.message) {
                        showNotification(data.message, 'success');
                    }
                })
                .catch(error => {
                    // Revert to previous state on error
                    showNotification('Error processing your vote. Please try again.', 'error');
                    restoreState(previousStates);
                })
                .finally(() => {
                    delete activeVoteRequests[targetId];
                    toggleButtonsState(voteContainers, false);
                });
        });
    });

    // Helper function to capture current state
    function captureState(containers, typeAttr, targetId) {
        const states = [];
        containers.forEach(container => {
            const scoreElement = container.querySelector(`.vote-score[data-${typeAttr}-id="${targetId}"]`);
            const upvoteCountElement = container.querySelector(`.upvote-count[data-${typeAttr}-id="${targetId}"]`);
            const downvoteCountElement = container.querySelector(`.downvote-count[data-${typeAttr}-id="${targetId}"]`);

            if (!scoreElement) return;

            const upvoteBtn = container.querySelector('.upvote-btn');
            const downvoteBtn = container.querySelector('.downvote-btn');
            if (!upvoteBtn || !downvoteBtn) return;

            // Capture icon states as well
            const upvoteNormalIcon = upvoteBtn.querySelector('.icon-container svg:first-child');
            const upvoteClickedIcon = upvoteBtn.querySelector('.icon-container svg:last-child');
            const downvoteNormalIcon = downvoteBtn.querySelector('.icon-container svg:first-child');
            const downvoteClickedIcon = downvoteBtn.querySelector('.icon-container svg:last-child');

            states.push({
                scoreElement,
                score: scoreElement.textContent.trim(),
                upvoteCountElement,
                upvoteCount: upvoteCountElement ? upvoteCountElement.textContent.trim() : '0',
                downvoteCountElement,
                downvoteCount: downvoteCountElement ? downvoteCountElement.textContent.trim() : '0',
                upvoteBtn: {
                    element: upvoteBtn,
                    classList: [...upvoteBtn.classList],
                    active: upvoteBtn.classList.contains('active-vote')
                },
                downvoteBtn: {
                    element: downvoteBtn,
                    classList: [...downvoteBtn.classList],
                    active: downvoteBtn.classList.contains('active-vote')
                },
                icons: {
                    upvoteNormal: {
                        element: upvoteNormalIcon,
                        hidden: upvoteNormalIcon ? upvoteNormalIcon.classList.contains('hidden') : false
                    },
                    upvoteClicked: {
                        element: upvoteClickedIcon,
                        hidden: upvoteClickedIcon ? upvoteClickedIcon.classList.contains('hidden') : true
                    },
                    downvoteNormal: {
                        element: downvoteNormalIcon,
                        hidden: downvoteNormalIcon ? downvoteNormalIcon.classList.contains('hidden') : false
                    },
                    downvoteClicked: {
                        element: downvoteClickedIcon,
                        hidden: downvoteClickedIcon ? downvoteClickedIcon.classList.contains('hidden') : true
                    }
                }
            });
        });
        return states;
    }

    // Helper function to toggle button states (disabled/enabled)
    function toggleButtonsState(containers, disabled) {
        containers.forEach(container => {
            container.querySelectorAll('.vote-btn').forEach(btn => {
                btn.disabled = disabled;
                btn.classList.toggle('opacity-50', disabled);
                btn.classList.toggle('cursor-not-allowed', disabled);
            });
        });
    }

    // Helper function to update UI optimistically
    function updateVoteUI(containers, typeAttr, targetId, isUpvote, isActive) {
        containers.forEach(container => {
            const scoreElement = container.querySelector(`.vote-score[data-${typeAttr}-id="${targetId}"]`);
            const upvoteCountElement = container.querySelector(`.upvote-count[data-${typeAttr}-id="${targetId}"]`);
            const downvoteCountElement = container.querySelector(`.downvote-count[data-${typeAttr}-id="${targetId}"]`);

            if (!scoreElement) return;

            const upvoteBtn = container.querySelector('.upvote-btn');
            const downvoteBtn = container.querySelector('.downvote-btn');
            if (!upvoteBtn || !downvoteBtn) return;

            const currentScore = parseInt(scoreElement.textContent.trim());
            const currentUpvoteCount = upvoteCountElement ? parseInt(upvoteCountElement.textContent.trim()) : 0;
            const currentDownvoteCount = downvoteCountElement ? parseInt(downvoteCountElement.textContent.trim()) : 0;

            const wasDownvoteActive = downvoteBtn.classList.contains('active-vote');
            const wasUpvoteActive = upvoteBtn.classList.contains('active-vote');

            // Reset both buttons first
            resetButtonStyles(upvoteBtn, downvoteBtn);

            // Calculate new score and counts, set appropriate button active
            let newScore = currentScore;
            let newUpvoteCount = currentUpvoteCount;
            let newDownvoteCount = currentDownvoteCount;

            if (isActive) {
                // Removing vote
                if (isUpvote) {
                    newScore -= 1;
                    newUpvoteCount -= 1;
                } else {
                    newScore += 1;
                    newDownvoteCount -= 1;
                }
            } else if (isUpvote) {
                // Adding upvote
                if (wasDownvoteActive) {
                    newScore += 2;
                    newDownvoteCount -= 1;
                } else {
                    newScore += 1;
                }
                newUpvoteCount += 1;
                setActiveUpvote(upvoteBtn);
            } else {
                // Adding downvote
                if (wasUpvoteActive) {
                    newScore -= 2;
                    newUpvoteCount -= 1;
                } else {
                    newScore -= 1;
                }
                newDownvoteCount += 1;
                setActiveDownvote(downvoteBtn);
            }

            scoreElement.textContent = newScore.toString();
            if (upvoteCountElement) upvoteCountElement.textContent = newUpvoteCount.toString();
            if (downvoteCountElement) downvoteCountElement.textContent = newDownvoteCount.toString();
        });
    }

    // Helper function to update UI with server data
    function updateUIWithServerData(containers, typeAttr, targetId, data) {
        containers.forEach(container => {
            const scoreElement = container.querySelector(`.vote-score[data-${typeAttr}-id="${targetId}"]`);
            const upvoteCountElement = container.querySelector(`.upvote-count[data-${typeAttr}-id="${targetId}"]`);
            const downvoteCountElement = container.querySelector(`.downvote-count[data-${typeAttr}-id="${targetId}"]`);

            if (!scoreElement) return;

            const upvoteBtn = container.querySelector('.upvote-btn');
            const downvoteBtn = container.querySelector('.downvote-btn');
            if (!upvoteBtn || !downvoteBtn) return;

            scoreElement.textContent = data.score;
            if (upvoteCountElement) upvoteCountElement.textContent = data.upvoteCount || data.upvotes || 0;
            if (downvoteCountElement) downvoteCountElement.textContent = data.downvoteCount || data.downvotes || 0;

            resetButtonStyles(upvoteBtn, downvoteBtn);

            const voteValue = Number(data.userVote);
            if (voteValue === 1) {
                setActiveUpvote(upvoteBtn);
            } else if (voteValue === -1) {
                setActiveDownvote(downvoteBtn);
            }
        });
    }

    // Helper function to restore previous state
    function restoreState(states) {
        states.forEach(state => {
            state.scoreElement.textContent = state.score;
            if (state.upvoteCountElement) state.upvoteCountElement.textContent = state.upvoteCount;
            if (state.downvoteCountElement) state.downvoteCountElement.textContent = state.downvoteCount;

            // Restore upvote button
            const upvoteBtn = state.upvoteBtn.element;
            upvoteBtn.className = '';
            state.upvoteBtn.classList.forEach(cls => upvoteBtn.classList.add(cls));

            // Restore downvote button
            const downvoteBtn = state.downvoteBtn.element;
            downvoteBtn.className = '';
            state.downvoteBtn.classList.forEach(cls => downvoteBtn.classList.add(cls));

            // Restore icon states
            if (state.icons.upvoteNormal.element) {
                state.icons.upvoteNormal.element.classList.toggle('hidden', state.icons.upvoteNormal.hidden);
            }
            if (state.icons.upvoteClicked.element) {
                state.icons.upvoteClicked.element.classList.toggle('hidden', state.icons.upvoteClicked.hidden);
            }
            if (state.icons.downvoteNormal.element) {
                state.icons.downvoteNormal.element.classList.toggle('hidden', state.icons.downvoteNormal.hidden);
            }
            if (state.icons.downvoteClicked.element) {
                state.icons.downvoteClicked.element.classList.toggle('hidden', state.icons.downvoteClicked.hidden);
            }
        });
    }

    // Helper function to reset button styles
    function resetButtonStyles(upvoteBtn, downvoteBtn) {
        // Remove active class from both buttons
        upvoteBtn.classList.remove('active-vote');
        downvoteBtn.classList.remove('active-vote');

        // Reset icon visibility
        toggleIconVisibility(upvoteBtn, false);
        toggleIconVisibility(downvoteBtn, false);
    }

    // Helper function to set active upvote style
    function setActiveUpvote(upvoteBtn) {
        upvoteBtn.classList.add('active-vote');
        toggleIconVisibility(upvoteBtn, true);
    }

    // Helper function to set active downvote style
    function setActiveDownvote(downvoteBtn) {
        downvoteBtn.classList.add('active-vote');
        toggleIconVisibility(downvoteBtn, true);
    }

    // Helper function to toggle icon visibility - FIXED VERSION
    function toggleIconVisibility(button, active) {
        const iconContainer = button.querySelector('.icon-container');
        if (!iconContainer) return;

        // Get both icons directly - more reliable than complex selectors
        const icons = iconContainer.querySelectorAll('svg');
        if (icons.length < 2) return;  // We need both icons to work

        // First SVG is normal, second SVG is clicked
        const normalIcon = icons[0];
        const clickedIcon = icons[1];

        // Toggle hidden class appropriately
        normalIcon.classList.toggle('hidden', active);
        clickedIcon.classList.toggle('hidden', !active);
    }

    // Helper function to show notifications
    function showNotification(message, type = 'success') {
        let container = document.getElementById('notification-container');

        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'fixed bottom-4 right-4 z-50';
            document.body.appendChild(container);
        }

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

        container.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.add('opacity-0');
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }
});
