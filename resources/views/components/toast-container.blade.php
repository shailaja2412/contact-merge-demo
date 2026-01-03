<!-- Toast Container -->
<div id="toast-container" class="fixed top-4 right-4 z-[9999] space-y-2" style="max-width: 400px;"></div>

<script>
// Toast Notification System
(function() {
    // Prevent multiple initializations
    if (window.toastInitialized) return;
    window.toastInitialized = true;
    
    // Get or create container
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 right-4 z-[9999] space-y-2';
        container.style.maxWidth = '400px';
        document.body.appendChild(container);
    }

    window.showToast = function(message, type = 'success', duration = 10000) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        const isSuccess = type === 'success';
        
        const bgColor = isSuccess 
            ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' 
            : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800';
        
        const textColor = isSuccess
            ? 'text-green-800 dark:text-green-200'
            : 'text-red-800 dark:text-red-200';
        
        const iconColor = isSuccess
            ? 'text-green-400'
            : 'text-red-400';

        const icon = isSuccess
            ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
            : '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';

        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `${bgColor} ${textColor} border rounded-lg shadow-lg p-4 flex items-start gap-3 transform transition-all duration-300 ease-in-out opacity-0 translate-x-full`;
        
        // Handle multi-line messages (split by \n)
        const messageLines = message.split('\n').filter(line => line.trim());
        const messageHtml = messageLines.length > 1
            ? messageLines.map(line => `<p class="text-sm font-medium">${escapeHtml(line.trim())}</p>`).join('')
            : `<p class="text-sm font-medium">${escapeHtml(message)}</p>`;
        
        toast.innerHTML = `
            <div class="${iconColor} flex-shrink-0 mt-0.5">
                ${icon}
            </div>
            <div class="flex-1 min-w-0 space-y-1">
                ${messageHtml}
            </div>
            <button onclick="removeToast('${toastId}')" class="flex-shrink-0 ${textColor} hover:opacity-70 transition-opacity">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        `;

        container.appendChild(toast);

        // Trigger animation
        setTimeout(() => {
            toast.classList.remove('opacity-0', 'translate-x-full');
            toast.classList.add('opacity-100', 'translate-x-0');
        }, 10);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                removeToast(toastId);
            }, duration);
        }

        return toastId;
    };

    window.removeToast = function(toastId) {
        const toast = document.getElementById(toastId);
        if (!toast) return;

        toast.classList.remove('opacity-100', 'translate-x-0');
        toast.classList.add('opacity-0', 'translate-x-full');

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    };

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Show session messages as toasts on page load
    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif
        
        @if (session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif

        @if (session('status'))
            showToast('{{ session('status') }}', 'success');
        @endif
    });
})();
</script>

