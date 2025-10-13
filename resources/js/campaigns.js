// ===== CAMPAIGN MANAGER FUNCTIONALITY =====
document.addEventListener("DOMContentLoaded", function () {
    // Auto-refresh campaign stats every 30 seconds
    setInterval(function () {
        if (window.Livewire) {
            // Check if we're on campaign details page
            const campaignDetails = document.querySelector("[wire\\:poll]");
            if (campaignDetails) {
                window.Livewire.dispatch("refreshStats");
            }
        }
    }, 30000);

    // Smooth scroll to top when pagination changes
    document.addEventListener("livewire:navigated", function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

    // Enhanced tooltips
    const tooltipElements = document.querySelectorAll("[title]");
    tooltipElements.forEach((element) => {
        element.addEventListener("mouseenter", function () {
            const tooltip = document.createElement("div");
            tooltip.className = "tooltip show";
            tooltip.textContent = this.getAttribute("title");

            // Position tooltip
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + rect.width / 2 + "px";
            tooltip.style.top = rect.top - 40 + "px";

            document.body.appendChild(tooltip);
            this.tooltipElement = tooltip;
        });

        element.addEventListener("mouseleave", function () {
            if (this.tooltipElement) {
                this.tooltipElement.remove();
                this.tooltipElement = null;
            }
        });
    });

    // Progress bar animations
    const progressBars = document.querySelectorAll(".progress-bar");
    progressBars.forEach((bar) => {
        const width = bar.style.width;
        bar.style.width = "0%";
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });

    // Bulk actions functionality
    let selectedMessages = new Set();

    // Select all checkbox
    const selectAllCheckbox = document.getElementById("select-all");
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener("change", function () {
            const messageCheckboxes =
                document.querySelectorAll(".message-checkbox");
            messageCheckboxes.forEach((checkbox) => {
                checkbox.checked = this.checked;
                if (this.checked) {
                    selectedMessages.add(checkbox.value);
                } else {
                    selectedMessages.delete(checkbox.value);
                }
            });
            updateBulkActions();
        });
    }

    // Individual message checkboxes
    document.addEventListener("change", function (e) {
        if (e.target.classList.contains("message-checkbox")) {
            console.log(
                "Checkbox changed:",
                e.target.value,
                "checked:",
                e.target.checked
            );
            if (e.target.checked) {
                selectedMessages.add(e.target.value);
                console.log("Added to selectedMessages:", e.target.value);
            } else {
                selectedMessages.delete(e.target.value);
                console.log("Removed from selectedMessages:", e.target.value);
            }
            console.log(
                "Current selectedMessages:",
                Array.from(selectedMessages)
            );
            updateBulkActions();
        }
    });

    function updateBulkActions() {
        const bulkActionsContainer = document.getElementById("bulk-actions");
        const selectedCount = document.getElementById("selected-count");

        if (bulkActionsContainer && selectedCount) {
            if (selectedMessages.size > 0) {
                bulkActionsContainer.classList.remove("hidden");
                selectedCount.textContent = selectedMessages.size;

                // Enable/disable buttons based on selection
                const deleteBtn = bulkActionsContainer.querySelector(
                    '[onclick*="bulkDeleteMessages"]'
                );
                const resendBtn = bulkActionsContainer.querySelector(
                    '[onclick*="bulkResendMessages"]'
                );

                if (deleteBtn) deleteBtn.disabled = false;
                if (resendBtn) resendBtn.disabled = false;
            } else {
                bulkActionsContainer.classList.add("hidden");
            }
        }
    }

    // Clear all selections function
    function clearAllSelections() {
        console.log("Clearing all selections...");
        selectedMessages.clear();

        // Uncheck all message checkboxes
        const checkboxes = document.querySelectorAll(".message-checkbox");
        checkboxes.forEach((checkbox) => {
            checkbox.checked = false;
        });

        // Uncheck select all checkbox
        const selectAllCheckbox = document.getElementById("select-all");
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }

        updateBulkActions();
        console.log("All selections cleared");
    }

    // Clear selection function (for manual use)
    window.clearSelection = clearAllSelections;

    // Bulk delete messages
    window.bulkDeleteMessages = function () {
        if (selectedMessages.size === 0) {
            alert("Please select messages to delete.");
            return;
        }

        if (
            confirm(
                `Are you sure you want to delete ${selectedMessages.size} selected messages?`
            )
        ) {
            if (window.Livewire) {
                try {
                    const messageIds = Array.from(selectedMessages);
                    console.log("Selected messages Set:", selectedMessages);
                    console.log("Message IDs array:", messageIds);
                    console.log(
                        "Dispatching bulkDeleteMessages with:",
                        messageIds
                    );

                    if (messageIds.length === 0) {
                        alert(
                            "No message IDs found. Please try selecting messages again."
                        );
                        return;
                    }

                    // Dispatch the delete operation
                    window.Livewire.dispatch("bulkDeleteMessages", messageIds);

                    // Clear selections immediately (will be cleared again after operation completes)
                    clearAllSelections();
                } catch (error) {
                    console.error(
                        "Error dispatching bulkDeleteMessages:",
                        error
                    );
                    alert(
                        "Error occurred while deleting messages. Please try again."
                    );
                }
            } else {
                alert("Livewire is not available. Please refresh the page.");
            }
        }
    };

    // Bulk resend messages
    window.bulkResendMessages = function () {
        if (selectedMessages.size === 0) {
            alert("Please select messages to resend.");
            return;
        }

        if (
            confirm(
                `Are you sure you want to resend ${selectedMessages.size} selected messages?`
            )
        ) {
            if (window.Livewire) {
                try {
                    const messageIds = Array.from(selectedMessages);
                    console.log("Selected messages Set:", selectedMessages);
                    console.log("Message IDs array:", messageIds);
                    console.log(
                        "Dispatching bulkResendMessages with:",
                        messageIds
                    );

                    if (messageIds.length === 0) {
                        alert(
                            "No message IDs found. Please try selecting messages again."
                        );
                        return;
                    }

                    // Dispatch the resend operation
                    window.Livewire.dispatch("bulkResendMessages", messageIds);

                    // Clear selections immediately (will be cleared again after operation completes)
                    clearAllSelections();
                } catch (error) {
                    console.error(
                        "Error dispatching bulkResendMessages:",
                        error
                    );
                    alert(
                        "Error occurred while resending messages. Please try again."
                    );
                }
            } else {
                alert("Livewire is not available. Please refresh the page.");
            }
        }
    };

    // Search functionality with debounce
    const searchInput = document.getElementById("search-phone");
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener("input", function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                window.Livewire.dispatch("updateSearch", { query: this.value });
            }, 300);
        });
    }

    // Keyboard shortcuts
    document.addEventListener("keydown", function (e) {
        // Ctrl/Cmd + R to refresh stats
        if ((e.ctrlKey || e.metaKey) && e.key === "r") {
            e.preventDefault();
            if (window.Livewire) {
                window.Livewire.dispatch("refreshStats");
            }
        }

        // Escape to close modals
        if (e.key === "Escape") {
            const modals = document.querySelectorAll(".modal-overlay");
            modals.forEach((modal) => {
                modal.click();
            });
        }
    });

    // Smooth animations for stat cards
    const statCards = document.querySelectorAll(".stat-card");
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.style.animationDelay = Math.random() * 0.5 + "s";
                entry.target.classList.add("fade-in");
            }
        });
    });

    statCards.forEach((card) => {
        observer.observe(card);
    });

    // Listen for Livewire events
    document.addEventListener("livewire:initialized", () => {
        if (window.Livewire) {
            console.log("Livewire initialized successfully");

            // Test Livewire connection
            window.testLivewire = function () {
                console.log("Testing Livewire connection...");
                window.Livewire.dispatch("refreshStats");
            };

            // Listen for bulk operation completion
            window.Livewire.on("bulk-operation-completed", () => {
                console.log(
                    "Bulk operation completed - clearing all selections"
                );
                clearAllSelections();
            });

            // Listen for bulk operation events (with details)
            window.Livewire.on("bulk-operation", (event) => {
                console.log("Bulk operation event received:", event);
                if (event.success) {
                    // Clear selections after successful operation
                    setTimeout(() => {
                        clearAllSelections();
                    }, 500); // Small delay to ensure UI updates
                }
            });
        } else {
            console.error("Livewire not available");
        }
    });

    // Additional event listeners (outside of livewire:initialized)
    document.addEventListener("livewire:load", () => {
        console.log("Livewire loaded - setting up bulk operation listeners");

        if (window.Livewire) {
            // Listen for bulk operation completion
            window.Livewire.on("bulk-operation-completed", () => {
                console.log(
                    "Bulk operation completed (from livewire:load) - clearing selections"
                );
                clearAllSelections();
            });
        }
    });

    // Global event listener for bulk operations
    window.addEventListener("bulk-operation-completed", () => {
        console.log("Global bulk operation completed - clearing selections");
        clearAllSelections();
    });
});

// Export functions for global use
window.CampaignManager = {
    refreshStats: function () {
        if (window.Livewire) {
            window.Livewire.dispatch("refreshStats");
        }
    },

    exportCampaignData: function (campaignId) {
        if (window.Livewire) {
            window.Livewire.dispatch("exportResults", {
                campaignId: campaignId,
            });
        }
    },
};
