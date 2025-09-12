import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Start Alpine after DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Alpine.start());
} else {
    Alpine.start();
}
