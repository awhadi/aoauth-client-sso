(function($) {
    $(document).ready(function() {
        // No interactive behavior - badge is purely visual
        // Just ensure it exists and is visible to SSO users
        var $badge = $('.aoauth-brand-badge');
        if (!$badge.length) return;
        
        // Optional: Add a small delay before showing for smoother page load
        setTimeout(function() {
            $badge.css('opacity', '1');
        }, 100);
    });
})(jQuery);