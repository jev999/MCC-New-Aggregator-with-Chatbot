// Session Timeout Warning System
class SessionTimeoutWarning {
    constructor() {
        this.warningShown = false;
        this.countdownInterval = null;
        this.timeRemaining = 0;
        this.init();
    }

    init() {
        // Check for session timeout warning from server
        this.checkServerWarning();
        
        // Set up periodic checks
        this.setupPeriodicChecks();
        
        // Listen for user activity to reset timeout
        this.setupActivityListeners();
    }

    checkServerWarning() {
        // Check if server has set a timeout warning
        if (window.sessionTimeoutWarning) {
            this.showWarning(window.sessionTimeoutWarning);
        }
    }

    setupPeriodicChecks() {
        // Check every 5 minutes for session status
        setInterval(() => {
            this.checkSessionStatus();
        }, 5 * 60 * 1000); // 5 minutes
    }

    setupActivityListeners() {
        // Reset timeout on user activity
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => {
                this.resetTimeout();
            }, true);
        });
    }

    async checkSessionStatus() {
        try {
            const response = await fetch('/api/session-status', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.timeout_warning) {
                    this.showWarning(data.time_remaining);
                }
            }
        } catch (error) {
            console.warn('Session status check failed:', error);
        }
    }

    showWarning(timeRemaining) {
        if (this.warningShown) return;

        this.warningShown = true;
        this.timeRemaining = timeRemaining;

        // Create warning modal
        this.createWarningModal();
        
        // Start countdown
        this.startCountdown();
    }

    createWarningModal() {
        // Remove existing modal if any
        const existingModal = document.getElementById('session-timeout-modal');
        if (existingModal) {
            existingModal.remove();
        }

        const modal = document.createElement('div');
        modal.id = 'session-timeout-modal';
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Session Timeout Warning
                        </h5>
                    </div>
                    <div class="modal-body">
                        <p>Your session will expire in <strong id="timeout-countdown">${this.timeRemaining}</strong> minutes due to inactivity.</p>
                        <p>Click "Stay Logged In" to extend your session, or you will be automatically logged out.</p>
                        <div class="progress mt-3">
                            <div class="progress-bar bg-warning" id="timeout-progress" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="logout-now">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Logout Now
                        </button>
                        <button type="button" class="btn btn-primary" id="stay-logged-in">
                            <i class="fas fa-clock me-1"></i>
                            Stay Logged In
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Add event listeners
        document.getElementById('logout-now').addEventListener('click', () => {
            this.logout();
        });

        document.getElementById('stay-logged-in').addEventListener('click', () => {
            this.extendSession();
        });

        // Prevent modal from being closed by clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                e.preventDefault();
            }
        });
    }

    startCountdown() {
        this.countdownInterval = setInterval(() => {
            this.timeRemaining--;
            
            // Update countdown display
            const countdownElement = document.getElementById('timeout-countdown');
            if (countdownElement) {
                countdownElement.textContent = this.timeRemaining;
            }

            // Update progress bar
            const progressElement = document.getElementById('timeout-progress');
            if (progressElement) {
                const totalTime = 5; // 5 minutes warning
                const percentage = (this.timeRemaining / totalTime) * 100;
                progressElement.style.width = `${Math.max(0, percentage)}%`;
                
                // Change color as time runs out
                if (percentage < 20) {
                    progressElement.className = 'progress-bar bg-danger';
                } else if (percentage < 50) {
                    progressElement.className = 'progress-bar bg-warning';
                }
            }

            // Auto logout when time runs out
            if (this.timeRemaining <= 0) {
                clearInterval(this.countdownInterval);
                this.logout();
            }
        }, 60000); // Update every minute
    }

    async extendSession() {
        try {
            const response = await fetch('/api/extend-session', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                // Hide warning modal
                this.hideWarning();
                
                // Show success message
                this.showSuccessMessage('Session extended successfully!');
            } else {
                throw new Error('Failed to extend session');
            }
        } catch (error) {
            console.error('Failed to extend session:', error);
            this.showErrorMessage('Failed to extend session. Please try again.');
        }
    }

    async logout() {
        try {
            const response = await fetch('/logout', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                window.location.href = '/login';
            } else {
                // Force redirect even if logout fails
                window.location.href = '/login';
            }
        } catch (error) {
            console.error('Logout failed:', error);
            window.location.href = '/login';
        }
    }

    hideWarning() {
        const modal = document.getElementById('session-timeout-modal');
        if (modal) {
            modal.remove();
        }
        
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }
        
        this.warningShown = false;
    }

    resetTimeout() {
        // Reset timeout warning if it was shown
        if (this.warningShown) {
            this.hideWarning();
        }
    }

    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }

    showErrorMessage(message) {
        this.showToast(message, 'danger');
    }

    showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        // Add to toast container
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        toastContainer.appendChild(toast);

        // Show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove toast element after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
}

// Initialize session timeout warning when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SessionTimeoutWarning();
});
