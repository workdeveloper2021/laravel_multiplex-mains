/**
 * Advanced Video Protection System
 * Prevents video theft and unauthorized access
 */

class VideoProtection {
    constructor() {
        this.isDevelopment = window.location.hostname === 'localhost' || 
                           window.location.hostname === '127.0.0.1' ||
                           window.location.hostname.includes('local') ||
                           window.location.port === '8000' ||
                           window.location.port === '8001';
        this.init();
    }

    init() {
        // Only enable protection in production
        if (!this.isDevelopment) {
            this.disableContextMenu();
            this.disableKeyboardShortcuts();
            this.disableDeveloperTools();
            this.disableTextSelection();
            this.hideVideoSources();
            this.detectScreenRecording();
            this.obfuscateVideoUrls();
        } else {
            console.log('🔧 Development Mode: Video protection disabled for debugging');
            // Only apply basic video security in development
            this.basicVideoSecurity();
        }
    }

    basicVideoSecurity() {
        // Minimal security for development
        document.addEventListener('DOMContentLoaded', function() {
            const videos = document.querySelectorAll('video');
            videos.forEach(video => {
                video.controlsList = 'nodownload';
            });
        });
    }

    disableContextMenu() {
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        }, false);

        // Disable right click on video elements
        document.addEventListener('DOMContentLoaded', function() {
            const videos = document.querySelectorAll('video');
            videos.forEach(video => {
                video.addEventListener('contextmenu', e => e.preventDefault());
                video.controlsList = 'nodownload noremoteplayback';
                video.disablePictureInPicture = true;
            });
        });
    }

    disableKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Disable F12 (DevTools)
            if (e.keyCode === 123) {
                e.preventDefault();
                return false;
            }

            // Disable Ctrl+Shift+I (DevTools)
            if (e.ctrlKey && e.shiftKey && e.keyCode === 73) {
                e.preventDefault();
                return false;
            }

            // Disable Ctrl+Shift+J (Console)
            if (e.ctrlKey && e.shiftKey && e.keyCode === 74) {
                e.preventDefault();
                return false;
            }

            // Disable Ctrl+U (View Source)
            if (e.ctrlKey && e.keyCode === 85) {
                e.preventDefault();
                return false;
            }

            // Disable Ctrl+S (Save)
            if (e.ctrlKey && e.keyCode === 83) {
                e.preventDefault();
                return false;
            }

            // Disable Ctrl+A (Select All)
            if (e.ctrlKey && e.keyCode === 65) {
                e.preventDefault();
                return false;
            }

            // Disable Ctrl+P (Print)
            if (e.ctrlKey && e.keyCode === 80) {
                e.preventDefault();
                return false;
            }

            // Disable Print Screen
            if (e.keyCode === 44) {
                e.preventDefault();
                return false;
            }
        });
    }

    disableDeveloperTools() {
        let devtools = { open: false };
        const threshold = 160;

        setInterval(() => {
            if (window.outerHeight - window.innerHeight > threshold ||
                window.outerWidth - window.innerWidth > threshold) {
                if (!devtools.open) {
                    devtools.open = true;
                    this.blockAccess();
                }
            } else {
                devtools.open = false;
            }
        }, 500);

        // Additional DevTools detection
        let startTime = performance.now();
        const check = () => {
            const duration = performance.now() - startTime;
            if (duration > 100) {
                this.blockAccess();
            }
            startTime = performance.now();
        };

        Object.defineProperty(window, 'console', {
            get: function() {
                check();
                return console;
            }
        });
    }

    blockAccess() {
        document.body.innerHTML = `
            <div style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: #000;
                color: #ff0000;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                z-index: 999999;
            ">
                <div style="text-align: center;">
                    <h1>🚫 ACCESS DENIED</h1>
                    <p>Unauthorized access detected</p>
                    <p>This content is protected</p>
                </div>
            </div>
        `;

        // Clear any existing timers
        let id = window.setTimeout(function() {}, 0);
        while (id--) {
            window.clearTimeout(id);
        }
    }

    disableTextSelection() {
        document.onselectstart = () => false;
        document.ondragstart = () => false;

        const style = document.createElement('style');
        style.textContent = `
            * {
                -webkit-user-select: none !important;
                -moz-user-select: none !important;
                -ms-user-select: none !important;
                user-select: none !important;
                -webkit-touch-callout: none !important;
                -webkit-tap-highlight-color: transparent !important;
            }
        `;
        document.head.appendChild(style);
    }

    hideVideoSources() {
        // Override video source access
        const originalVideoSrc = Object.getOwnPropertyDescriptor(HTMLVideoElement.prototype, 'src');
        Object.defineProperty(HTMLVideoElement.prototype, 'src', {
            get: function() {
                return '[PROTECTED]';
            },
            set: originalVideoSrc.set
        });

        // Override currentSrc
        Object.defineProperty(HTMLVideoElement.prototype, 'currentSrc', {
            get: function() {
                return '[PROTECTED]';
            }
        });
    }

    detectScreenRecording() {
        // Detect screen recording attempts
        if (navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia) {
            const originalGetDisplayMedia = navigator.mediaDevices.getDisplayMedia;
            navigator.mediaDevices.getDisplayMedia = function() {
                console.warn('Screen recording attempt detected');
                return Promise.reject(new Error('Screen recording blocked'));
            };
        }

        // Detect video capture
        const originalGetUserMedia = navigator.mediaDevices?.getUserMedia;
        if (originalGetUserMedia) {
            navigator.mediaDevices.getUserMedia = function(constraints) {
                if (constraints?.video?.displaySurface) {
                    return Promise.reject(new Error('Screen capture blocked'));
                }
                return originalGetUserMedia.call(this, constraints);
            };
        }
    }

    obfuscateVideoUrls() {
        // Obfuscate video URLs in DOM
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.tagName === 'VIDEO' || node.tagName === 'SOURCE') {
                        this.protectVideoElement(node);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Initial protection
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('video, source').forEach(el => {
                this.protectVideoElement(el);
            });
        });
    }

    protectVideoElement(element) {
        // Add protection attributes
        element.setAttribute('controlsList', 'nodownload noremoteplayback');
        element.setAttribute('disablePictureInPicture', 'true');
        element.setAttribute('oncontextmenu', 'return false;');

        // Prevent seeking to end to avoid full download
        element.addEventListener('loadedmetadata', function() {
            this.preload = 'none';
        });

        // Monitor for suspicious activity
        element.addEventListener('loadstart', function() {
            console.log('Video loading monitored');
        });
    }

    // Generate secure token for video requests
    static generateSecureToken() {
        const timestamp = Date.now();
        const random = Math.random().toString(36).substr(2, 15);
        const data = { timestamp, random };
        return btoa(JSON.stringify(data)).replace(/[^a-zA-Z0-9]/g, '');
    }

    // Validate token
    static validateToken(token) {
        try {
            const decoded = JSON.parse(atob(token));
            const now = Date.now();
            const tokenAge = now - decoded.timestamp;
            return tokenAge < 3600000; // 1 hour
        } catch (e) {
            return false;
        }
    }
}

// Anti-debugging techniques (only in production)
(() => {
'use strict';

const isDevelopment = window.location.hostname === 'localhost' || 
                     window.location.hostname === '127.0.0.1' ||
                     window.location.hostname.includes('local') ||
                 window.location.port === '8000' ||
                     window.location.port === '8001';
    
if (!isDevelopment) {
    // Disable console methods
const noop = () => {};
    ['log', 'warn', 'error', 'info', 'debug', 'trace'].forEach(method => {
            console[method] = noop;
    });

    // Clear console periodically
setInterval(() => {
    console.clear();
}, 1000);

// Detect debug mode
let startTime = performance.now();
    const detectDebug = () => {
        const timeTaken = performance.now() - startTime;
            if (timeTaken > 100) {
                document.body.innerHTML = '<h1>Debug mode detected</h1>';
            }
            startTime = performance.now();
            requestAnimationFrame(detectDebug);
        };
        requestAnimationFrame(detectDebug);
    } else {
        console.log('🔧 Development Mode: Anti-debugging disabled');
    }
})();

// Initialize protection when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => new VideoProtection());
} else {
    new VideoProtection();
}

// Console warning (only in production)
const isDevelopmentMode = window.location.hostname === 'localhost' || 
                         window.location.hostname === '127.0.0.1' ||
                         window.location.hostname.includes('local') ||
                         window.location.port === '8000' ||
                         window.location.port === '8001';

if (!isDevelopmentMode) {
    console.log('%c⚠️ VIDEO CONTENT PROTECTED ⚠️', 'color: red; font-size: 20px; font-weight: bold;');
    console.log('%cThis content is protected by copyright. Unauthorized access or downloading is prohibited.', 'color: red; font-size: 12px;');
} else {
    console.log('%c🔧 Development Mode Active', 'color: green; font-size: 16px; font-weight: bold;');
    console.log('%cVideo protection disabled for debugging. Enable in production.', 'color: green; font-size: 12px;');
}

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VideoProtection;
}
