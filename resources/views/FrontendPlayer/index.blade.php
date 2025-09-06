<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'MultiplexPlay – Your Ultimate OTT Experience' }}</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        referrerpolicy="no-referrer" />

    <style>
        :root {
            --bg: #0b0b0b;
            --bg-soft: #121212;
            --text: #ffffff;
            --muted: #b5b5b5;
            --accent: #ff004c;
            --accent-2: #ff7b00;
            --card: #1a1a1a;
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            height: 100%
        }

        body {
            margin: 0;
            font-family: Inter, system-ui, sans-serif;
            background: var(--bg);
            color: var(--text)
        }

        a {
            color: inherit;
            text-decoration: none
        }

        img {
            display: block;
            max-width: 100%
        }

        button {
            font: inherit
        }

        /* Navbar */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            background: rgba(0, 0, 0, .7);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, .06)
        }

        .logo {
            font-weight: 800;
            font-size: 1.25rem;
            background: linear-gradient(45deg, var(--accent), var(--accent-2));
            -webkit-background-clip: text;
            color: transparent
        }

        .nav-links {
            display: flex;
            gap: 18px;
            align-items: center
        }

        .nav-links a {
            color: #e9e9e9;
            opacity: .9
        }

        .nav-links a:hover {
            color: var(--accent)
        }

        .cta {
            padding: 10px 14px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(45deg, var(--accent), var(--accent-2));
            color: #fff;
            font-weight: 700;
            cursor: pointer
        }

        /* Player */
        .player {
            max-width: 1280px;
            margin: 18px auto 0;
            padding: 0 16px
        }

        .player-box {
            position: relative;
            background: #000;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .08)
        }

        .player-box::after {
            content: "";
            display: block;
            aspect-ratio: 16/9
        }

        .player-box video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #000
        }

        /* Sections */
        .section {
            padding: 28px 16px 8px;
            max-width: 1280px;
            margin-inline: auto
        }

        .section h2 {
            font-size: 1.25rem;
            margin: 0 0 10px
        }

        .section .muted {
            color: var(--muted);
            font-size: .95rem
        }

        /* Row scrolling */
        .row-wrapper {
            position: relative
        }

        .row {
            display: flex;
            gap: 14px;
            align-items: stretch;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 10px 2px 20px;
            scroll-snap-type: x mandatory
        }

        .row::-webkit-scrollbar {
            height: 10px
        }

        .row::-webkit-scrollbar-thumb {
            background: #2a2a2a;
            border-radius: 999px
        }

        .card {
            min-width: 180px;
            max-width: 220px;
            flex: 0 0 auto;
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 16px;
            overflow: hidden;
            scroll-snap-align: start;
            transition: transform .2s ease
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, .35)
        }

        .poster {
            position: relative;
            aspect-ratio: 2/3;
            background: #222
        }

        .badge {
            position: absolute;
            top: 8px;
            left: 8px;
            padding: 4px 8px;
            border-radius: 999px;
            background: rgba(0, 0, 0, .6);
            border: 1px solid rgba(255, 255, 255, .12);
            font-size: .75rem
        }

        .meta {
            padding: 10px 12px
        }

        .title {
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 6px;
            font-size: .98rem
        }

        .sub {
            color: var(--muted);
            font-size: .85rem
        }

        .actions {
            display: flex;
            gap: 8px;
            margin-top: 12px
        }

        .play,
        .add {
            flex: 1;
            padding: 8px 10px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 700
        }

        .play {
            background: linear-gradient(45deg, var(--accent), var(--accent-2));
            color: #fff
        }

        .add {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, .16);
            color: #fff
        }

        /* Row arrows */
        .arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, .18);
            background: rgba(0, 0, 0, .45);
            backdrop-filter: blur(6px);
            display: grid;
            place-items: center;
            cursor: pointer;
            z-index: 2
        }

        .arrow.left {
            left: -4px
        }

        .arrow.right {
            right: -4px
        }

        /* Series Panel */
        #spSeasons li button {
            width: 100%;
            text-align: left;
            background: transparent;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .12);
            padding: 8px 10px;
            border-radius: 8px;
            margin: 8px;
            cursor: pointer
        }

        #spSeasons li button.active {
            background: rgba(255, 255, 255, .08);
            border-color: rgba(255, 255, 255, .24)
        }

        #spEpisodes .ep-card {
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 10px;
            padding: 10px;
            background: #0f0f0f
        }

        #spEpisodes .ep-title {
            font-weight: 700;
            margin-bottom: 6px
        }

        #spEpisodes .ep-meta {
            color: #b5b5b5;
            font-size: .9rem;
            margin-bottom: 8px
        }

        #spEpisodes .ep-actions button {
            background: linear-gradient(45deg, #ff004c, #ff7b00);
            border: none;
            color: #fff;
            border-radius: 8px;
            padding: 6px 10px;
            cursor: pointer
        }

        /* Purchase Modal */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100
        }

        .modal-content {
            background: #111;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 12px;
            padding: 20px;
            max-width: 400px;
            text-align: center
        }

        .modal-title {
            font-size: 1.2rem;
            margin-bottom: 12px;
            color: var(--accent)
        }

        .modal-text {
            color: var(--muted);
            margin-bottom: 20px
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center
        }

        .modal-actions button {
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--accent), var(--accent-2));
            color: #fff
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, .16);
            color: #fff
        }

        /* Footer */
        footer {
            padding: 32px 16px;
            color: var(--muted);
            text-align: center
        }

        /* Responsive */
        @media (max-width: 640px) {
            .card {
                min-width: 150px
            }

            .nav-links {
                display: none
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo">MultiplexPlay</div>
        <div class="nav-links">
            <a href="#">Home</a>
            <a href="#movies">Movies</a>
            <a href="#shows">TV Shows</a>
        </div>
        <button class="cta"><i class="fa-solid fa-crown"></i> Go Premium</button>
    </nav>

    <!-- Player -->
    <section class="player">
        <div class="player-box">
            <video id="videoPlayer" controls playsinline controlsList="nodownload noplaybackrate"
                disablePictureInPicture oncontextmenu="return false" poster="{{ $poster ?? '' }}">
                <!-- Secure HLS playback -->
            </video>
            <div id="wm"
                style="position:absolute;inset:auto 8px 8px auto;z-index:2;color:rgba(255,255,255,.65);font-size:12px;pointer-events:none;">
                {{ auth()->user()->email ?? 'Guest' }} • <span id="timestamp"></span></div>
        </div>
    </section>

    <!-- Series Panel (hidden by default) -->
    <section id="seriesPanel" style="display:none;max-width:1280px;margin:12px auto 0;padding:0 16px">
        <div style="background:#111;border:1px solid rgba(255,255,255,.08);border-radius:12px;overflow:hidden">
            <div
                style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.06)">
                <h3 id="spTitle" style="margin:0;font-size:1.1rem">Series</h3>
                <button id="spClose"
                    style="background:transparent;border:1px solid rgba(255,255,255,.16);color:#fff;border-radius:8px;padding:6px 10px;cursor:pointer">Close</button>
            </div>
            <div style="display:flex;gap:0;min-height:220px">
                <aside style="width:220px;border-right:1px solid rgba(255,255,255,.06);max-height:360px;overflow:auto">
                    <ul id="spSeasons" style="list-style:none;margin:0;padding:0"></ul>
                </aside>
                <main style="flex:1;padding:12px;max-height:360px;overflow:auto">
                    <div id="spEpisodes"
                        style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px"></div>
                </main>
            </div>
        </div>
    </section>

    <!-- Dynamic Content Sections -->
    <div id="contentSections">
        <!-- Sections from home_content_for_android will be injected here -->
    </div>

    <!-- Purchase Modal -->
    <div id="purchaseModal" class="modal">
        <div class="modal-content">
            <div class="modal-title"><i class="fa-solid fa-lock"></i> Subscription Required</div>
            <div id="purchaseMessage" class="modal-text">Please subscribe to watch this content.</div>
            <div class="modal-actions">
                <button id="purchaseBtn" class="btn-primary">Purchase Now</button>
                <button id="cancelBtn" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <footer>© <span id="year"></span> MultiplexPlay • All rights reserved.</footer>

    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script>
        // Config: Set user context (from auth or default)
        const USER_CONFIG = {
            id: @json(auth()->user()->_id ?? ''),
            email: @json(auth()->user()->email ?? ''),
            country: 'IN'
        };

        // Reusable video click handler function
        async function handleVideoPlay(videoItem, currentUserId) {
            try {
                // Extract required data from video item
                const videoId = videoItem.videos_id || videoItem._id || videoItem.id;
                const channelId = videoItem.channel_id || '';
                const isWebseries = String(videoItem.is_tvseries) === '1';
                const title = videoItem.title || 'Untitled';

                console.log('Video clicked:', {
                    videoId,
                    channelId,
                    isWebseries,
                    title,
                    rawItem: videoItem
                });

                // Validate required data
                if (!videoId) {
                    alert('Video ID not found');
                    return;
                }

                if (!currentUserId) {
                    alert('Please login first to watch content');
                    return;
                }

                // Determine API endpoint and build query params
                let apiUrl, queryParams;

                if (isWebseries) {
                    // Webseries API call
                    apiUrl = '/api/ott/webseries/details';
                    queryParams = new URLSearchParams({
                        id: videoId,
                        field: '_id',
                        user_id: currentUserId,
                        channel_id: channelId
                    });
                } else {
                    // Movie API call
                    apiUrl = '/api/ott/check-movie';
                    queryParams = new URLSearchParams({
                        vId: videoId,
                        user_id: currentUserId,
                        channel_id: channelId,
                        country: USER_CONFIG.country
                    });
                }

                const fullUrl = `${apiUrl}?${queryParams.toString()}`;
                console.log('API call URL:', fullUrl);

                // Make API call
                const response = await fetch(fullUrl);
                const data = await response.json();

                console.log('API response:', data);

                // Handle subscription status
                if (data.isSubscribed === false) {
                    // Extract subscription type from response data
                    const mainData = Array.isArray(data.data) && data.data.length > 0 ? data.data[0] : {};
                    const isChannelSubscription = mainData.isChannel === true;

                    // Show appropriate purchase modal
                    showPurchaseModal(isChannelSubscription, title, channelId);
                    return;
                }

                // User is subscribed - proceed with video playback
                const mainData = Array.isArray(data.data) && data.data.length > 0 ? data.data[0] : {};
                const videoUrl = mainData.video_url || videoItem.video_url || videoItem.stream_url;

                if (videoUrl) {
                    if (isWebseries && mainData.seasonsId && mainData.seasonsId.length > 0) {
                        // For webseries with seasons, open series panel
                        SeriesPanel.openSeries(mainData, title);
                    } else {
                        // Direct video playback with secure manifest
                        const secureUrl =
                            `/api/video-manifest?url=${encodeURIComponent(videoUrl)}&token=${btoa(JSON.stringify({timestamp: Math.floor(Date.now()/1000)}))}`;
                        playVideo(secureUrl, title);
                    }
                } else {
                    alert('Video URL not available');
                }

            } catch (error) {
                console.error('Video play failed:', error);
                alert('Failed to load video. Please try again.');
            }
        }

        // Secure HLS player with session management
        let hlsInstance = null;
        let currentSession = null;

        function generateSessionId() {
            return 'sess_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
        }

        async function playSecureUrl(url, title = '', contentId = '', contentType = 'movie', channelId = '') {
            const video = document.getElementById('videoPlayer');
            if (!video || !url) return;

            try {
                // Check/create video session
                currentSession = generateSessionId();
                const sessionData = {
                    video_id: contentId,
                    session_id: currentSession,
                    user_id: USER_CONFIG.id || 'guest_' + Date.now()
                };

                const sessionRes = await fetch('/api/check-video-session', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify(sessionData)
                });

                const sessionJson = await sessionRes.json();
                if (!sessionJson.success && sessionJson.existing_session) {
                    if (!confirm('Video is playing in another session. Force play here?')) {
                        return;
                    }
                    // Force session
                    await fetch('/api/force-video-session', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify(sessionData)
                    });
                }

                // Check subscription before play
                let checkUrl, checkParams;
                if (contentType === 'webseries') {
                    checkUrl = '/api/ott/check-webseries';
                    checkParams = {
                        id: contentId,
                        user_id: USER_CONFIG.id,
                        channel_id: channelId,
                        field: '_id'
                    };
                } else {
                    checkUrl = '/api/ott/check-movie';
                    checkParams = {
                        vId: contentId,
                        user_id: USER_CONFIG.id,
                        channel_id: channelId,
                        country: USER_CONFIG.country
                    };
                }

                const subRes = await fetch(checkUrl + '?' + new URLSearchParams(checkParams));
                const subJson = await subRes.json();

                if (!subJson.isSubscribed) {
                    // Show purchase modal
                    showPurchaseModal(subJson.isChannel, title, channelId);
                    return;
                }

                // Play video securely
                const secureUrl =
                    `/api/video-manifest?url=${encodeURIComponent(url)}&token=${btoa(JSON.stringify({timestamp: Math.floor(Date.now()/1000)}))}`;

                if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = secureUrl;
                    video.play().catch(() => {});
                } else if (window.Hls && Hls.isSupported()) {
                    if (hlsInstance) hlsInstance.destroy();
                    hlsInstance = new Hls({
                        maxBufferLength: 60,
                        backBufferLength: 60
                    });
                    hlsInstance.loadSource(secureUrl);
                    hlsInstance.attachMedia(video);
                    video.play().catch(() => {});
                }

                // Start heartbeat
                startHeartbeat(sessionData);

            } catch (e) {
                console.warn('Secure play failed:', e);
                alert('Failed to play video. Please try again.');
            }
        }

        function startHeartbeat(sessionData) {
            if (window.heartbeatInterval) clearInterval(window.heartbeatInterval);
            window.heartbeatInterval = setInterval(async () => {
                try {
                    await fetch('/api/video-heartbeat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.content || ''
                        },
                        body: JSON.stringify(sessionData)
                    });
                } catch (e) {
                    console.warn('Heartbeat failed:', e);
                }
            }, 30000); // Every 30 seconds
        }

        function showPurchaseModal(isChannel, title, channelId) {
            const modal = document.getElementById('purchaseModal');
            const message = document.getElementById('purchaseMessage');
            const purchaseBtn = document.getElementById('purchaseBtn');

            if (isChannel) {
                message.textContent = `Please purchase channel access to watch "${title}".`;
                purchaseBtn.textContent = 'Buy Channel Access';
            } else {
                message.textContent = `Please subscribe to a plan to watch "${title}".`;
                purchaseBtn.textContent = 'Subscribe Now';
            }

            modal.style.display = 'flex';
        }

        // Purchase modal handlers
        document.getElementById('cancelBtn').addEventListener('click', () => {
            document.getElementById('purchaseModal').style.display = 'none';
        });
        document.getElementById('purchaseBtn').addEventListener('click', () => {
            // Redirect to purchase/subscription page
            window.location.href = '/pricing'; // or wherever your purchase flow is
        });

        // Series Panel Controller
        const SeriesPanel = (() => {
            const root = document.getElementById('seriesPanel');
            const titleEl = document.getElementById('spTitle');
            const seasonsEl = document.getElementById('spSeasons');
            const episodesEl = document.getElementById('spEpisodes');
            const closeBtn = document.getElementById('spClose');

            closeBtn.addEventListener('click', () => root.style.display = 'none');

            function openSeries(seriesData, seriesTitle) {
                titleEl.textContent = seriesTitle || 'Series';
                root.style.display = 'block';

                const seasons = Array.isArray(seriesData?.seasonsId) ? seriesData.seasonsId : [];
                if (!seasons.length) {
                    seasonsEl.innerHTML = '<li style="padding:12px;color:#b5b5b5">No seasons found.</li>';
                    episodesEl.innerHTML = '';
                    return;
                }

                seasonsEl.innerHTML = '';
                seasons.forEach((s, idx) => {
                    const li = document.createElement('li');
                    const btn = document.createElement('button');
                    btn.textContent = s.title || `Season ${idx + 1}`;
                    btn.addEventListener('click', () => selectSeason(s, btn, seriesData));
                    li.appendChild(btn);
                    seasonsEl.appendChild(li);
                });

                const first = seasons[0];
                if (first) {
                    selectSeason(first, seasonsEl.querySelector('button'), seriesData);
                }
            }

            function selectSeason(seasonData, btnEl, seriesData) {
                seasonsEl.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                if (btnEl) btnEl.classList.add('active');

                const eps = Array.isArray(seasonData?.episodesId) ? seasonData.episodesId : [];
                if (!eps.length) {
                    episodesEl.innerHTML = '<div style="padding:6px;color:#b5b5b5">No episodes found.</div>';
                    return;
                }

                episodesEl.innerHTML = '';
                eps.forEach((ep, i) => {
                    const m3u8 = ep.video_url || ep.stream_url;
                    const poster = ep.thumbnail_url || ep.poster_url || '';
                    const card = document.createElement('div');
                    card.className = 'ep-card';
                    card.innerHTML = `
            <div class="ep-title">${ep.title || `Episode ${i+1}`}</div>
            <div class="ep-meta">HD</div>
            ${poster ? `<img src="${poster}" alt="" style="width:100%;border-radius:8px;margin-bottom:8px;object-fit:cover;max-height:120px">` : ''}
            <div class="ep-actions"><button>Play Episode</button></div>
          `;
                    card.querySelector('button').addEventListener('click', () => {
                        if (m3u8) {
                            playSecureUrl(m3u8, ep.title || `Episode ${i+1}`, ep._id || ep.id,
                                'webseries', seriesData.channel_id);
                            root.style.display = 'none';
                        }
                    });
                    episodesEl.appendChild(card);
                });
            }

            return {
                openSeries
            };
        })();

        // Load home content with genre sections
        (async function() {
            try {
                const homeRes = await fetch(`/api/ott/home?country=${USER_CONFIG.country}`);
                const homeJson = await homeRes.json();

                const featuresData = homeJson?.features_genre_and_movie || [];
                const sectionsContainer = document.getElementById('contentSections');

                // Helper function to create video card
                function createVideoCard(videoItem) {
                    const poster = videoItem.thumbnail_url || videoItem.poster_url || videoItem.image_url ||
                        `https://picsum.photos/400/600?random=${Math.floor(Math.random()*1000)}`;
                    const title = videoItem.title || 'Untitled';
                    const isWebseries = String(videoItem.is_tvseries) === '1';
                    const badge = String(videoItem.is_paid) === '1' ? 'PAID' : (videoItem.video_quality || 'FREE');

                    const card = document.createElement('article');
                    card.className = 'card';
                    card.innerHTML = `
            <div class="poster">
              <img src="${poster}" alt="${title}">
              <span class="badge">${badge}</span>
            </div>
            <div class="meta">
              <div class="title">${title}</div>
              <div class="sub">${isWebseries ? 'Series' : 'Movie'}</div>
              <div class="actions">
                <button class="play"><i class="fa-solid fa-play"></i> ${isWebseries ? 'Watch' : 'Play'}</button>
                <button class="add"><i class="fa-solid fa-plus"></i></button>
              </div>
            </div>`;

                    // Use reusable click handler
                    card.querySelector('.play').addEventListener('click', () => {
                        handleVideoPlay(videoItem, USER_CONFIG.id);
                    });

                    return card;
                }

                // Create genre sections from features_genre_and_movie
                featuresData.forEach(genreSection => {
                    const genreName = genreSection.name || genreSection.genre_name || 'Content';
                    const movies = Array.isArray(genreSection.movies) ? genreSection.movies : [];

                    if (!movies.length) return;

                    const section = document.createElement('section');
                    section.className = 'section';
                    section.innerHTML = `
            <h2>${genreName}</h2>
            <p class="muted">Swipe or use arrows to scroll →</p>
            <div class="row-wrapper" data-row>
              <button class="arrow left" aria-label="Scroll left"><i class="fa-solid fa-chevron-left"></i></button>
              <div class="row"></div>
              <button class="arrow right" aria-label="Scroll right"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
          `;

                    const row = section.querySelector('.row');
                    movies.slice(0, 15).forEach(item => {
                        row.appendChild(createVideoCard(item));
                    });

                    sectionsContainer.appendChild(section);
                });

                // Setup arrow navigation for all rows
                setupRowNavigation();

            } catch (e) {
                console.warn('Failed loading home content', e);
            }
        })();

        // Row navigation setup
        function setupRowNavigation() {
            const ROW_SCROLL = 600;
            document.querySelectorAll('[data-row]').forEach((wrap) => {
                const row = wrap.querySelector('.row');
                const left = wrap.querySelector('.arrow.left');
                const right = wrap.querySelector('.arrow.right');

                left.addEventListener('click', () => row.scrollBy({
                    left: -ROW_SCROLL,
                    behavior: 'smooth'
                }));
                right.addEventListener('click', () => row.scrollBy({
                    left: ROW_SCROLL,
                    behavior: 'smooth'
                }));

                const toggle = () => {
                    const max = row.scrollWidth - row.clientWidth - 2;
                    left.style.visibility = row.scrollLeft <= 2 ? 'hidden' : 'visible';
                    right.style.visibility = row.scrollLeft >= max ? 'hidden' : 'visible';
                };
                row.addEventListener('scroll', toggle, {
                    passive: true
                });
                window.addEventListener('resize', toggle);
                toggle();

                // Drag to scroll
                let isDown = false,
                    startX = 0,
                    startLeft = 0;
                row.addEventListener('pointerdown', (e) => {
                    isDown = true;
                    startX = e.clientX;
                    startLeft = row.scrollLeft;
                    row.setPointerCapture(e.pointerId);
                });
                row.addEventListener('pointermove', (e) => {
                    if (!isDown) return;
                    row.scrollLeft = startLeft - (e.clientX - startX);
                });
                row.addEventListener('pointerup', () => {
                    isDown = false;
                });
                row.addEventListener('pointercancel', () => {
                    isDown = false;
                });
            });
        }

        // Anti-piracy measures
        document.addEventListener('keydown', (e) => {
            const block = (e.ctrlKey && (e.key === 's' || e.key === 'S' || e.key === 'p' || e.key === 'P')) || (e
                .key === 'PrintScreen');
            if (block) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        // Update watermark timestamp
        setInterval(() => {
            const wm = document.getElementById('wm');
            const ts = document.getElementById('timestamp');
            if (ts) ts.textContent = new Date().toLocaleTimeString();
        }, 1000);

        // Year in footer
        document.getElementById('year').textContent = new Date().getFullYear();

        // Handle page unload (end video session)
        window.addEventListener('beforeunload', () => {
            if (currentSession && USER_CONFIG.id) {
                navigator.sendBeacon('/api/end-video-session', JSON.stringify({
                    video_id: 'current',
                    session_id: currentSession,
                    user_id: USER_CONFIG.id
                }));
            }
        });
    </script>
</body>

</html>
