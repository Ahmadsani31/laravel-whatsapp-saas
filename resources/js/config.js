// Configuration utilities
export class Config {
    /**
     * Get configuration value from meta tags
     * @param {string} name - Meta tag name
     * @param {string} defaultValue - Default value if not found
     * @returns {string}
     */
    static get(name, defaultValue = '') {
        const metaTag = document.querySelector(`meta[name="${name}"]`);
        const value = metaTag?.content || defaultValue;
        
        if (!metaTag) {
            console.warn(`⚠️ Configuration '${name}' not found, using default: ${defaultValue}`);
        }
        
        return value;
    }

    /**
     * Get WhatsApp Engine URL
     * @returns {string}
     */
    static getWhatsAppEngineUrl() {
        return this.get('whatsapp-engine-url', 'http://localhost:3000');
    }

    /**
     * Get CSRF token
     * @returns {string}
     */
    static getCsrfToken() {
        return this.get('csrf-token', '');
    }

    /**
     * Check if app is in debug mode
     * @returns {boolean}
     */
    static isDebug() {
        return this.get('app-debug', 'false') === 'true';
    }

    /**
     * Get theme preference
     * @returns {string}
     */
    static getThemePreference() {
        return this.get('theme-preference', 'light');
    }
}

// Export for global use
window.Config = Config;