// ===== NOTYF NOTIFICATION SYSTEM =====
// Global notification event listener
window.addEventListener('notify', event => {
    const notyf = new Notyf({
        duration: 4000,
        position: {
            x: 'right',
            y: 'top',
        },
        types: [
            {
                type: 'info',
                background: '#0948B3',
                icon: {
                    className: 'fas fa-info-circle',
                    tagName: 'span',
                    color: '#fff'
                },
                dismissible: true
            },
            {
                type: 'warning',
                background: '#F5B759',
                icon: {
                    className: 'fas fa-exclamation-triangle',
                    tagName: 'span',
                    color: '#fff'
                },
                dismissible: true
            },
            {
                type: 'error',
                background: '#ef4444',
                icon: {
                    className: 'fas fa-times-circle',
                    tagName: 'span',
                    color: '#fff'
                },
                dismissible: true
            },
            {
                type: 'success',
                background: '#10b981',
                icon: {
                    className: 'fas fa-check-circle',
                    tagName: 'span',
                    color: '#fff'
                },
                dismissible: true
            }
        ]
    });

    notyf.open({
        type: event.detail[0],
        message: event.detail[1]
    });
});