// resources\js\share.js
class ShareManager {
    constructor() {
        this.initializeShareButtons();
    }

    initializeShareButtons() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.share-button')) {
                e.preventDefault();
                const button = e.target.closest('.share-button');
                const shareData = {
                    url: button.dataset.shareUrl,
                    title: button.dataset.shareTitle,
                    text: button.dataset.shareDescription || ''
                };
                this.handleShare(shareData);
            }
        });
    }

    async handleShare(shareData) {
        // Check if native Web Share API is supported (mobile and some desktop browsers)
        if (navigator.share && this.isMobileOrHasWebShare()) {
            try {
                await navigator.share({
                    title: shareData.title,
                    text: shareData.text,
                    url: shareData.url
                });
                // Successfully shared via native API - return early to prevent fallback
                return;
            } catch (err) {
                // Only show fallback if user didn't cancel (AbortError means user cancelled)
                if (err.name === 'AbortError') {
                    // User cancelled - don't show fallback
                    return;
                }
                // Other errors - fall through to show custom options
                console.log('Web Share API failed:', err);
            }
        }

        // Fallback: Show custom share options
        this.showShareOptions(shareData);
    }

    isMobileOrHasWebShare() {
        // Check if device is mobile or if desktop browser supports Web Share
        const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        const hasWebShare = 'share' in navigator;

        // On desktop, only use Web Share if it's fully supported (like Chrome on Windows with certain flags)
        return isMobile || (hasWebShare && navigator.share);
    }

    showShareOptions(shareData) {
        // Create a simple modal with share options
        const modal = this.createShareModal(shareData);
        document.body.appendChild(modal);

        // Show modal with animation
        requestAnimationFrame(() => {
            modal.classList.add('show');
        });

        // Handle clicks
        modal.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-backdrop') || e.target.classList.contains('close-modal')) {
                this.closeModal(modal);
            } else if (e.target.closest('.copy-link')) {
                this.copyToClipboard(shareData.url, modal);
            } else if (e.target.closest('.whatsapp-share')) {
                this.shareToWhatsApp(shareData);
                this.closeModal(modal);
            }
        });
    }

    createShareModal(shareData) {
        const modal = document.createElement('div');
        modal.className = 'share-modal-overlay';
        modal.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="share-modal">
                <div class="share-header">
                    <h3>Share Post</h3>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="share-content">
                    <p class="share-title">${shareData.title}</p>
                    <div class="share-options">
                        <button class="share-option whatsapp-share">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.570-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                            </svg>
                            Share on WhatsApp
                        </button>
                        <button class="share-option copy-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span class="copy-text">Copy Link</span>
                        </button>
                    </div>
                </div>
            </div>
        `;

        return modal;
    }

    async copyToClipboard(url, modal) {
        try {
            await navigator.clipboard.writeText(url);
            const copyText = modal.querySelector('.copy-text');
            const originalText = copyText.textContent;
            copyText.textContent = 'Copied!';
            setTimeout(() => {
                copyText.textContent = originalText;
            }, 2000);
        } catch (err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = url;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);

            const copyText = modal.querySelector('.copy-text');
            copyText.textContent = 'Copied!';
            setTimeout(() => {
                copyText.textContent = 'Copy Link';
            }, 2000);
        }
    }

    shareToWhatsApp(shareData) {
        const text = encodeURIComponent(`${shareData.title}\n\n${shareData.url}`);
        window.open(`https://wa.me/?text=${text}`, '_blank');
    }

    closeModal(modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ShareManager();
});

// CSS styles - add this to your CSS file or in a <style> tag
const shareStyles = `
.share-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.share-modal-overlay.show {
    opacity: 1;
    visibility: visible;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
}

.share-modal {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.9);
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    width: 90%;
    transition: transform 0.3s ease;
}

.share-modal-overlay.show .share-modal {
    transform: translate(-50%, -50%) scale(1);
}

.share-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.share-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

.close-modal {
    background: none;
    border: none;
    font-size: 24px;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-modal:hover {
    color: #374151;
}

.share-content {
    padding: 20px;
}

.share-title {
    font-weight: 500;
    color: #111827;
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 1.4;
}

.share-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.share-option {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border: none;
    border-radius: 8px;
    background: #f9fafb;
    cursor: pointer;
    transition: background-color 0.2s;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.share-option:hover {
    background: #f3f4f6;
}

.share-option svg {
    margin-right: 12px;
    flex-shrink: 0;
}

.whatsapp-share {
    background: #dcfce7;
    color: #166534;
}

.whatsapp-share:hover {
    background: #bbf7d0;
}

.copy-link {
    background: #dbeafe;
    color: #1d4ed8;
}

.copy-link:hover {
    background: #bfdbfe;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .share-modal {
        background: #1f2937;
    }

    .share-header {
        border-bottom-color: #374151;
    }

    .share-header h3 {
        color: #f9fafb;
    }

    .close-modal {
        color: #9ca3af;
    }

    .close-modal:hover {
        color: #d1d5db;
    }

    .share-title {
        color: #f9fafb;
    }

    .share-option {
        background: #374151;
        color: #d1d5db;
    }

    .share-option:hover {
        background: #4b5563;
    }
}

/* Mobile responsiveness */
@media (max-width: 640px) {
    .share-modal {
        position: fixed;
        top: auto;
        bottom: 0;
        left: 0;
        right: 0;
        transform: translateY(100%);
        border-radius: 16px 16px 0 0;
        max-width: none;
        width: 100%;
    }

    .share-modal-overlay.show .share-modal {
        transform: translateY(0);
    }
}
`;

// Inject styles
if (!document.getElementById('share-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'share-styles';
    styleSheet.textContent = shareStyles;
    document.head.appendChild(styleSheet);
}
