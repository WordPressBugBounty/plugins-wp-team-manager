(function() {
    document.addEventListener("click", function(e) {
        if (e.target.closest(".wtm-device-btn")) {
            const btn = e.target.closest(".wtm-device-btn");
            const device = btn.dataset.device;
            const container = btn.closest(".wtm-responsive-grid-control");
            
            container.querySelectorAll(".wtm-device-btn").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            
            container.querySelectorAll(".wtm-preview-grid").forEach(g => g.classList.remove("active"));
            container.querySelector(".wtm-preview-grid." + device).classList.add("active");
        }
    });
    
    document.addEventListener("change", function(e) {
        if (e.target.matches("input[name*='mobile'], input[name*='tablet'], input[name*='desktop']")) {
            const device = e.target.name.includes("mobile") ? "mobile" : 
                         e.target.name.includes("tablet") ? "tablet" : "desktop";
            const cols = e.target.value;
            const container = e.target.closest(".wtm-responsive-grid-control");
            const preview = container.querySelector(".wtm-preview-grid." + device);
            
            const colTemplate = Array(parseInt(cols)).fill("1fr").join(" ");
            preview.style.gridTemplateColumns = colTemplate;
            
            preview.innerHTML = "";
            for (let i = 0; i < cols; i++) {
                const card = document.createElement("div");
                card.className = "wtm-preview-card";
                preview.appendChild(card);
            }
        }
    });
})();
(function() {
    function updateLayoutTip() {
        const selected = document.querySelector("input[name=\"' . $this->prefix . 'layout_option\"]:checked");
        const tips = document.querySelectorAll(".wtm-tip");
        
        tips.forEach(tip => tip.classList.remove("active"));
        
        if (selected) {
            const activeTip = document.querySelector(".wtm-tip[data-layout=\"" + selected.value + "\"]");
            if (activeTip) activeTip.classList.add("active");
        }
    }
    
    document.addEventListener("change", function(e) {
        if (e.target.name === "' . $this->prefix . 'layout_option") {
            updateLayoutTip();
        }
    });
    
    // Initialize on page load
    setTimeout(updateLayoutTip, 100);
})();