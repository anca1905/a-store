/**
 * Global SweetAlert2 & Notification Handler
 * A STORE Management System
 */

// Global Toast configuration
const SwalToast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

// Show Toast helper
function showToast(icon, title, message) {
    return SwalToast.fire({
        icon: icon,
        title: title || (icon === 'success' ? 'Berhasil' : icon === 'error' ? 'Gagal' : 'Informasi'),
        text: message || ''
    });
}

// Show Modal Alert helper
function showAlert(icon, title, text, timer = null) {
    const config = {
        icon: icon,
        title: title,
        text: text,
        confirmButtonColor: '#111111',
        confirmButtonText: 'Tutup'
    };
    if (timer) {
        config.timer = timer;
        config.timerProgressBar = true;
    }
    return Swal.fire(config);
}

// Success Modal
function showSuccess(message, title = 'Berhasil!') {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonColor: '#111111',
        confirmButtonText: 'OK',
        timer: 3000,
        timerProgressBar: true
    });
}

// Error Modal
function showError(message, title = 'Terjadi Kesalahan') {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonColor: '#111111',
        confirmButtonText: 'Tutup'
    });
}

// Warning Modal
function showWarning(message, title = 'Perhatian') {
    return Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        confirmButtonColor: '#111111',
        confirmButtonText: 'Mengerti'
    });
}

// Info Modal
function showInfo(message, title = 'Informasi') {
    return Swal.fire({
        icon: 'info',
        title: title,
        text: message,
        confirmButtonColor: '#111111',
        confirmButtonText: 'Tutup'
    });
}

// Confirmation Dialog helper
function confirmAction(options) {
    const defaults = {
        title: 'Apakah Anda yakin?',
        text: 'Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#111111',
        cancelButtonColor: '#64748b',
        isDanger: false
    };
    const config = Object.assign({}, defaults, options);
    if (config.isDanger) {
        config.confirmButtonColor = '#ef4444';
    }
    return Swal.fire({
        title: config.title,
        text: config.text,
        icon: config.icon,
        showCancelButton: true,
        confirmButtonColor: config.confirmButtonColor,
        cancelButtonColor: config.cancelButtonColor,
        confirmButtonText: config.confirmButtonText,
        cancelButtonText: config.cancelButtonText,
        reverseButtons: true
    });
}

// Confirm Delete for Form submission
function confirmDeleteForm(event, form, text = 'Data yang dihapus tidak dapat dikembalikan!') {
    if (event) event.preventDefault();
    confirmAction({
        title: 'Konfirmasi Hapus',
        text: text,
        icon: 'warning',
        confirmButtonText: 'Ya, Hapus Data',
        cancelButtonText: 'Batal',
        isDanger: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}

// Confirm Delete for Link navigation
function confirmDeleteLink(event, url, text = 'Data yang dihapus tidak dapat dikembalikan!') {
    if (event) event.preventDefault();
    confirmAction({
        title: 'Konfirmasi Hapus',
        text: text,
        icon: 'warning',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        isDanger: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
    return false;
}

// Confirm Logout
function confirmLogout(event) {
    if (event) event.preventDefault();
    confirmAction({
        title: 'Konfirmasi Keluar',
        text: 'Apakah Anda yakin ingin keluar dari sistem A STORE?',
        icon: 'question',
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Tetap di Sini',
        confirmButtonColor: '#111111'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php';
        }
    });
    return false;
}

// Bersihkan parameter flash dari URL agar tidak muncul lagi saat refresh (F5)
function cleanUrlFlashParams() {
    if (window.history.replaceState) {
        const url = new URL(window.location);
        let changed = false;
        ['msg', 'error', 'sukses', 'warning', 'info'].forEach(param => {
            if (url.searchParams.has(param)) {
                url.searchParams.delete(param);
                changed = true;
            }
        });
        if (changed) {
            window.history.replaceState({}, document.title, url.toString());
        }
    }
}

// Override window.alert default peramban agar otomatis memakai SweetAlert
window.alert = function(msg) {
    Swal.fire({
        icon: 'info',
        title: 'Pemberitahuan',
        text: msg,
        confirmButtonColor: '#111111',
        confirmButtonText: 'Tutup'
    });
};

// Auto listener untuk elemen dengan atribut data-confirm
document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('click', function(e) {
        const trigger = e.target.closest('[data-confirm]');
        if (!trigger) return;
        
        e.preventDefault();
        const confirmMsg = trigger.getAttribute('data-confirm') || 'Apakah Anda yakin?';
        const isDelete = trigger.classList.contains('btn-delete') || 
                         trigger.getAttribute('data-confirm-danger') === 'true' ||
                         confirmMsg.toLowerCase().includes('hapus') || 
                         confirmMsg.toLowerCase().includes('delete');
        
        confirmAction({
            title: isDelete ? 'Konfirmasi Hapus' : 'Konfirmasi Tindakan',
            text: confirmMsg,
            icon: isDelete ? 'warning' : 'question',
            confirmButtonText: isDelete ? 'Ya, Hapus' : 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            isDanger: isDelete
        }).then((result) => {
            if (result.isConfirmed) {
                if (trigger.tagName.toLowerCase() === 'a') {
                    window.location.href = trigger.href;
                } else if (trigger.tagName.toLowerCase() === 'form') {
                    trigger.submit();
                } else if (trigger.form) {
                    trigger.form.submit();
                }
            }
        });
    });
});
