document.addEventListener('DOMContentLoaded', function() {
    const $ = document.querySelector.bind(document);
    const $$ = document.querySelectorAll.bind(document);

    const tabs = $$(".tab-item");
    const panes = $$(".tab-pane");
    const tabActive = $(".tab-item.active");
    const line = $(".tm-tabs .line");

    // Check if elements exist
    if (!tabs.length || !panes.length || !tabActive || !line) {
        console.log('WTM Settings: Required elements not found');
        return;
    }

    // Initialize active tab line position
    function initTabLine() {
        if (tabActive) {
            line.style.left = tabActive.offsetLeft + "px";
            line.style.width = tabActive.offsetWidth + "px";
        }
    }

    // Use requestIdleCallback with fallback
    if (window.requestIdleCallback) {
        requestIdleCallback(initTabLine);
    } else {
        setTimeout(initTabLine, 0);
    }

    // Add click handlers to tabs
    tabs.forEach((tab, index) => {
        const pane = panes[index];
        
        if (!pane) {
            console.log('WTM Settings: Pane not found for tab', index);
            return;
        }

        tab.onclick = function () {
            // Remove active class from all tabs and panes
            const activeTab = $(".tab-item.active");
            const activePane = $(".tab-pane.active");
            
            if (activeTab) activeTab.classList.remove("active");
            if (activePane) activePane.classList.remove("active");

            // Update line position
            line.style.left = this.offsetLeft + "px";
            line.style.width = this.offsetWidth + "px";

            // Add active class to current tab and pane
            this.classList.add("active");
            pane.classList.add("active");
        };
    });

    console.log('WTM Settings: Tab functionality initialized');
});
