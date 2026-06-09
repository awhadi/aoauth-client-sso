(function() {
    var result = document.getElementById('aoauth-silent-auto-login-result');
    if (!result || !window.parent || window.parent === window) {
        return;
    }

    window.parent.postMessage({
        type: 'aoauthSilentAutoLogin',
        success: result.getAttribute('data-success') === '1',
        redirectUrl: result.getAttribute('data-redirect-url') || '',
        reason: result.getAttribute('data-reason') || ''
    }, window.location.origin);
})();
