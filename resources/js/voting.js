// resources/js/voting.js - Simplified dynamic voting system
class VotingSystem {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.activeRequests = new Set();
        this.init();
    }

    init() {
        // Use event delegation for dynamic content
        document.addEventListener('click', this.handleVoteClick.bind(this));
    }

    handleVoteClick(e) {
        const voteBtn = e.target.closest('.vote-btn');
        if (!voteBtn || voteBtn.classList.contains('guest-vote')) return;

        e.preventDefault();
        this.processVote(voteBtn);
    }

    async processVote(button) {
        const form = button.closest('.vote-form');
        if (!form) return;

        const url = form.getAttribute('action');
        const voteContainer = button.closest('.vote-container');
        if (!voteContainer) return;

        // Extract target info dynamically
        const targetInfo = this.extractTargetInfo(voteContainer, url);
        if (!targetInfo || this.activeRequests.has(targetInfo.key)) return;

        // Prevent multiple requests
        this.activeRequests.add(targetInfo.key);
        this.toggleButtonsState(voteContainer, true);

        // Store previous state for rollback
        const previousState = this.captureState(voteContainer, targetInfo);

        // Apply optimistic update
        const isUpvote = button.classList.contains('upvote-btn');
        const isActive = button.classList.contains('active-vote');
        this.updateVoteUI(voteContainer, targetInfo, isUpvote, isActive);

        try {
            const response = await this.sendVoteRequest(form, url);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `Server error: ${response.status}`);
            }

            // Update with server data
            this.updateUIWithServerData(voteContainer, targetInfo, data);

            if (data.showToast !== false && data.message) {
                this.showNotification(data.message, 'success');
            }
        } catch (error) {
            console.error('Vote error:', error);
            this.showNotification('Error processing your vote. Please try again.', 'error');
            this.restoreState(voteContainer, targetInfo, previousState);
        } finally {
            this.activeRequests.delete(targetInfo.key);
            this.toggleButtonsState(voteContainer, false);
        }
    }

    extractTargetInfo(container, url) {
        // Check for post or answer data attributes
        const postId = container.getAttribute('data-post-id');
        const answerId = container.getAttribute('data-answer-id');

        if (postId) {
            return {
                type: 'post',
                id: postId,
                key: `post-${postId}`,
                attr: 'data-post-id'
            };
        }

        if (answerId) {
            return {
                type: 'answer',
                id: answerId,
                key: `answer-${answerId}`,
                attr: 'data-answer-id'
            };
        }

        return null;
    }

    captureState(container, targetInfo) {
        const scoreEl = container.querySelector(`.vote-score[${targetInfo.attr}="${targetInfo.id}"]`);
        const upvoteCountEl = container.querySelector(`.upvote-count[${targetInfo.attr}="${targetInfo.id}"]`);
        const downvoteCountEl = container.querySelector(`.downvote-count[${targetInfo.attr}="${targetInfo.id}"]`);
        const upvoteBtn = container.querySelector('.upvote-btn');
        const downvoteBtn = container.querySelector('.downvote-btn');

        return {
            score: scoreEl?.textContent.trim() || '0',
            upvoteCount: upvoteCountEl?.textContent.trim() || '0',
            downvoteCount: downvoteCountEl?.textContent.trim() || '0',
            upvoteActive: upvoteBtn?.classList.contains('active-vote') || false,
            downvoteActive: downvoteBtn?.classList.contains('active-vote') || false,
            upvoteIcons: this.captureIconState(upvoteBtn),
            downvoteIcons: this.captureIconState(downvoteBtn)
        };
    }

    captureIconState(button) {
        if (!button) return null;
        const icons = button.querySelectorAll('.icon-container svg');
        return {
            normal: icons[0]?.classList.contains('hidden') || false,
            clicked: icons[1]?.classList.contains('hidden') !== false
        };
    }

    updateVoteUI(container, targetInfo, isUpvote, wasActive) {
        const scoreEl = container.querySelector(`.vote-score[${targetInfo.attr}="${targetInfo.id}"]`);
        const upvoteCountEl = container.querySelector(`.upvote-count[${targetInfo.attr}="${targetInfo.id}"]`);
        const downvoteCountEl = container.querySelector(`.downvote-count[${targetInfo.attr}="${targetInfo.id}"]`);
        const upvoteBtn = container.querySelector('.upvote-btn');
        const downvoteBtn = container.querySelector('.downvote-btn');

        if (!scoreEl || !upvoteBtn || !downvoteBtn) return;

        const currentScore = parseInt(scoreEl.textContent.trim()) || 0;
        const currentUpvotes = parseInt(upvoteCountEl?.textContent.trim() || '0');
        const currentDownvotes = parseInt(downvoteCountEl?.textContent.trim() || '0');

        const wasUpvoteActive = upvoteBtn.classList.contains('active-vote');
        const wasDownvoteActive = downvoteBtn.classList.contains('active-vote');

        // Reset both buttons
        this.resetButtonStyles(upvoteBtn, downvoteBtn);

        let newScore = currentScore;
        let newUpvotes = currentUpvotes;
        let newDownvotes = currentDownvotes;

        if (wasActive) {
            // Removing vote
            if (isUpvote) {
                newScore -= 1;
                newUpvotes -= 1;
            } else {
                newScore += 1;
                newDownvotes -= 1;
            }
        } else if (isUpvote) {
            // Adding upvote
            if (wasDownvoteActive) {
                newScore += 2;
                newDownvotes -= 1;
            } else {
                newScore += 1;
            }
            newUpvotes += 1;
            this.setActiveUpvote(upvoteBtn);
        } else {
            // Adding downvote
            if (wasUpvoteActive) {
                newScore -= 2;
                newUpvotes -= 1;
            } else {
                newScore -= 1;
            }
            newDownvotes += 1;
            this.setActiveDownvote(downvoteBtn);
        }

        // Update display
        scoreEl.textContent = newScore.toString();
        if (upvoteCountEl) upvoteCountEl.textContent = newUpvotes.toString();
        if (downvoteCountEl) downvoteCountEl.textContent = newDownvotes.toString();
    }

    updateUIWithServerData(container, targetInfo, data) {
        // Find ALL vote containers for this post/answer on the page
        const allVoteContainers = document.querySelectorAll(`.vote-container[${targetInfo.attr}="${targetInfo.id}"]`);

        allVoteContainers.forEach(voteContainer => {
            const scoreEl = voteContainer.querySelector(`.vote-score[${targetInfo.attr}="${targetInfo.id}"]`);
            const upvoteCountEl = voteContainer.querySelector(`.upvote-count[${targetInfo.attr}="${targetInfo.id}"]`);
            const downvoteCountEl = voteContainer.querySelector(`.downvote-count[${targetInfo.attr}="${targetInfo.id}"]`);
            const upvoteBtn = voteContainer.querySelector('.upvote-btn');
            const downvoteBtn = voteContainer.querySelector('.downvote-btn');

            if (!scoreEl || !upvoteBtn || !downvoteBtn) return;

            // Update counts and score
            scoreEl.textContent = data.score || '0';
            if (upvoteCountEl) upvoteCountEl.textContent = data.upvoteCount || data.upvotes || '0';
            if (downvoteCountEl) downvoteCountEl.textContent = data.downvoteCount || data.downvotes || '0';

            // Reset and set correct button state
            this.resetButtonStyles(upvoteBtn, downvoteBtn);

            const userVote = Number(data.userVote);
            if (userVote === 1) {
                this.setActiveUpvote(upvoteBtn);
            } else if (userVote === -1) {
                this.setActiveDownvote(downvoteBtn);
            }
        });
    }

    restoreState(container, targetInfo, state) {
        // Find ALL vote containers for this post/answer on the page
        const allVoteContainers = document.querySelectorAll(`.vote-container[${targetInfo.attr}="${targetInfo.id}"]`);

        allVoteContainers.forEach(voteContainer => {
            const scoreEl = voteContainer.querySelector(`.vote-score[${targetInfo.attr}="${targetInfo.id}"]`);
            const upvoteCountEl = voteContainer.querySelector(`.upvote-count[${targetInfo.attr}="${targetInfo.id}"]`);
            const downvoteCountEl = voteContainer.querySelector(`.downvote-count[${targetInfo.attr}="${targetInfo.id}"]`);
            const upvoteBtn = voteContainer.querySelector('.upvote-btn');
            const downvoteBtn = voteContainer.querySelector('.downvote-btn');

            if (scoreEl) scoreEl.textContent = state.score;
            if (upvoteCountEl) upvoteCountEl.textContent = state.upvoteCount;
            if (downvoteCountEl) downvoteCountEl.textContent = state.downvoteCount;

            if (upvoteBtn && downvoteBtn) {
                this.resetButtonStyles(upvoteBtn, downvoteBtn);

                if (state.upvoteActive) this.setActiveUpvote(upvoteBtn);
                if (state.downvoteActive) this.setActiveDownvote(downvoteBtn);
            }
        });
    }

    toggleButtonsState(container, disabled) {
        container.querySelectorAll('.vote-btn').forEach(btn => {
            btn.disabled = disabled;
        });
    }

    resetButtonStyles(upvoteBtn, downvoteBtn) {
        [upvoteBtn, downvoteBtn].forEach((btn, index) => {
            if (btn) {
                btn.classList.remove('active-vote', 'text-white', 'font-bold', 'bg-branding-primary', 'bg-branding-dark', 'hover:bg-opacity-90');
                btn.classList.add('text-black', 'dark:text-white', 'hover:bg-opacity-10');
                this.toggleIconVisibility(btn, false);
                this.updateSeparatorColor(btn, false);
            }
        });
    }

    setActiveUpvote(btn) {
        this.applyActiveStyles(btn, true);
    }

    setActiveDownvote(btn) {
        this.applyActiveStyles(btn, false);
    }

    applyActiveStyles(btn, isUpvote) {
        if (btn) {
            btn.classList.add('active-vote');
            if (isUpvote) {
                btn.classList.add('text-white', 'font-bold', 'bg-branding-primary', 'hover:bg-opacity-90');
                btn.classList.remove('text-black', 'dark:text-white', 'hover:bg-opacity-10');
            } else {
                btn.classList.add('text-white', 'font-bold', 'bg-branding-dark', 'hover:bg-opacity-90');
                btn.classList.remove('text-black', 'dark:text-white', 'hover:bg-opacity-10');
            }
            this.toggleIconVisibility(btn, true);
            this.updateSeparatorColor(btn, true);
        }
    }

    updateSeparatorColor(btn, isActive) {
        const separator = btn.querySelector('span.flex span:first-of-type');
        if (separator && separator.textContent === '|') {
            if (isActive) {
                separator.classList.remove('text-gray-500', 'dark:text-gray-400');
                separator.classList.add('text-white');
            } else {
                separator.classList.remove('text-white');
                separator.classList.add('text-gray-500', 'dark:text-gray-400');
            }
        }
    }

    toggleIconVisibility(button, active) {
        const icons = button?.querySelectorAll('.icon-container svg');
        if (icons && icons.length >= 2) {
            icons[0].classList.toggle('hidden', active);  // normal icon
            icons[1].classList.toggle('hidden', !active); // clicked icon
        }
    }

    async sendVoteRequest(form, url) {
        const formData = new FormData(form);

        return fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
            }
        });
    }

    showNotification(message, type = 'success') {
        let container = document.getElementById('notification-container');

        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'fixed bottom-4 right-4 z-50';
            document.body.appendChild(container);
        }

        const notification = document.createElement('div');
        const bgClass = type === 'error'
            ? 'bg-red-100 border-l-4 border-red-600 text-red-700'
            : 'bg-green-100 border-l-4 border-green-600 text-green-700';

        notification.className = `mb-2 p-3 rounded-lg shadow-lg transition-all duration-300 ${bgClass}`;
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
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new VotingSystem();
});
