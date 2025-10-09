// Enhanced Dark Mode Implementation
class ThemeManager {
    constructor() {
        this.themeToggleDarkIcon = document.getElementById(
            "theme-toggle-dark-icon"
        );
        this.themeToggleLightIcon = document.getElementById(
            "theme-toggle-light-icon"
        );
        this.themeToggleBtn = document.getElementById("theme-toggle");
        this.currentTheme = this.getCurrentTheme();

        this.init();
    }

    init() {
        this.setInitialTheme();
        this.setupEventListeners();
        this.watchSystemTheme();
    }

    getCurrentTheme() {
        // Priority: localStorage > system preference > default (light)
        const stored = localStorage.getItem("color-theme");
        if (stored) return stored;

        return window.matchMedia("(prefers-color-scheme: dark)").matches
            ? "dark"
            : "light";
    }

    setInitialTheme() {
        this.applyTheme(this.currentTheme);
        this.updateIcons();
    }

    applyTheme(theme) {
        if (theme === "dark") {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }

        this.currentTheme = theme;
        localStorage.setItem("color-theme", theme);
        this.updateSession(theme);

        // Dispatch custom event for other components
        window.dispatchEvent(
            new CustomEvent("themeChanged", {
                detail: { theme },
            })
        );
    }

    updateIcons() {
        if (!this.themeToggleDarkIcon || !this.themeToggleLightIcon) return;

        if (this.currentTheme === "dark") {
            this.themeToggleDarkIcon.classList.add("hidden");
            this.themeToggleLightIcon.classList.remove("hidden");
        } else {
            this.themeToggleDarkIcon.classList.remove("hidden");
            this.themeToggleLightIcon.classList.add("hidden");
        }
    }

    toggleTheme() {
        const newTheme = this.currentTheme === "dark" ? "light" : "dark";
        this.applyTheme(newTheme);
        this.updateIcons();

        // Add visual feedback
        this.addToggleFeedback();
    }

    addToggleFeedback() {
        if (!this.themeToggleBtn) return;

        this.themeToggleBtn.classList.add("scale-95");
        setTimeout(() => {
            this.themeToggleBtn.classList.remove("scale-95");
        }, 150);
    }

    setupEventListeners() {
        // Theme toggle button
        if (this.themeToggleBtn) {
            this.themeToggleBtn.addEventListener("click", () =>
                this.toggleTheme()
            );
        }

        // Keyboard shortcuts
        document.addEventListener("keydown", (e) => {
            // Ctrl/Cmd + Shift + T
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === "T") {
                e.preventDefault();
                this.toggleTheme();
            }

            // Ctrl/Cmd + Shift + D (Dark mode)
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === "D") {
                e.preventDefault();
                this.applyTheme("dark");
                this.updateIcons();
            }

            // Ctrl/Cmd + Shift + L (Light mode)
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === "L") {
                e.preventDefault();
                this.applyTheme("light");
                this.updateIcons();
            }
        });
    }

    watchSystemTheme() {
        // Watch for system theme changes
        const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
        mediaQuery.addEventListener("change", (e) => {
            // Only auto-switch if user hasn't manually set a preference
            if (!localStorage.getItem("color-theme")) {
                const systemTheme = e.matches ? "dark" : "light";
                this.applyTheme(systemTheme);
                this.updateIcons();
            }
        });
    }

    async updateSession(theme) {
        try {
            const response = await fetch("/theme", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content || "",
                },
                body: JSON.stringify({ theme }),
            });

            if (!response.ok) {
                console.warn("Failed to update theme session");
            }
        } catch (error) {
            console.warn("Error updating theme session:", error);
        }
    }

    // Public methods for external use
    setTheme(theme) {
        if (["light", "dark"].includes(theme)) {
            this.applyTheme(theme);
            this.updateIcons();
        }
    }

    getTheme() {
        return this.currentTheme;
    }

    resetToSystem() {
        localStorage.removeItem("color-theme");
        const systemTheme = window.matchMedia("(prefers-color-scheme: dark)")
            .matches
            ? "dark"
            : "light";
        this.applyTheme(systemTheme);
        this.updateIcons();
    }
}

// Initialize theme manager when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    window.themeManager = new ThemeManager();

    // Make it globally accessible for debugging
    if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
    ) {
        console.log("🎨 Theme Manager initialized");
        console.log("Current theme:", window.themeManager.getTheme());
        console.log(
            "Available methods: setTheme(theme), getTheme(), resetToSystem()"
        );
    }
});
