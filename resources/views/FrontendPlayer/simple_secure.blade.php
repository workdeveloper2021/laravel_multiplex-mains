<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'MultiplexPlay – Your Ultimate OTT Experience' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        referrerpolicy="no-referrer" />

    <style>
        :root {
            --bg: #0b0b0b;
            --text: #fff;
            --muted: #b5b5b5;
            --accent: #ff004c;
            --accent-2: #ff7b00;
            --card: #1a1a1a
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

        .watermark {
            position: absolute;
            inset: auto 8px 8px auto;
            z-index: 2;
            color: rgba(255, 255, 255, .65);
            font-size: 12px;
            pointer-events: none
        }

        .section {
            padding: 28px 16px 8px;
            max-width: 1280px;
            margin-inline: auto;
            position: relative
        }

        .section h2 {
            font-size: 1.25rem;
            margin: 0 0 10px;
            position: sticky;
            top: 64px;
            background: linear-gradient(0deg, rgba(11, 11, 11, 1) 60%, rgba(11, 11, 11, 0) 100%);
            padding-top: 6px
        }

        .section .muted {
            color: var(--muted);
            font-size: .95rem
        }

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

        /* Modal/Toast/Skeleton */
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
            max-width: 420px;
            text-align: center
        }

        .modal-title {
            font-size: 1.2rem;
            margin-bottom: 12px;
            color: var(--accent)
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

        .toast {
            position: fixed;
            bottom: 18px;
            left: 50%;
            transform: translateX(-50%);
            background: #151515;
            border: 1px solid rgba(255, 255, 255, .12);
            padding: 10px 14px;
            border-radius: 10px;
            color: #fff;
            display: none;
            z-index: 120
        }

        .skeleton {
            background: linear-gradient(90deg, #1f1f1f 25%, #232323 37%, #1f1f1f 63%);
            background-size: 400% 100%;
            animation: shine 1.2s infinite;
            border-radius: 14px
        }

        @keyframes shine {
            0% {
                background-position: 100% 50%
            }

            100% {
                background-position: 0 50%
            }
        }

        .skeleton.poster {
            aspect-ratio: 2/3
        }

        /* Webseries panel */
        .series-head {
            display: flex;
            gap: 16px;
            align-items: center;
            margin: 16px 0
        }

        .series-cover {
            width: 92px;
            height: 132px;
            border-radius: 12px;
            object-fit: cover;
            background: #222
        }

        .series-title {
            font-size: 1.15rem;
            font-weight: 800;
            margin: 0
        }

        .series-meta {
            color: var(--muted);
            font-size: .9rem
        }

        .series-controls {
            display: flex;
            gap: 10px;
            margin: 12px 0 6px;
            align-items: center
        }

        .series-select {
            background: #111;
            border: 1px solid rgba(255, 255, 255, .14);
            color: #fff;
            border-radius: 10px;
            padding: 8px 10px
        }

        .episodes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 10px
        }

        .ep-card {
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            overflow: hidden
        }

        .ep-thumb {
            aspect-ratio: 16/9;
            width: 100%;
            object-fit: cover;
            background: #222
        }

        .ep-body {
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .ep-title {
            font-weight: 700;
            font-size: .95rem
        }

        .ep-play {
            border: none;
            border-radius: 8px;
            padding: 6px 10px;
            cursor: pointer;
            font-weight: 700;
            background: linear-gradient(45deg, var(--accent), var(--accent-2));
            color: #fff
        }

        .locked {
            opacity: .6;
            filter: grayscale(.2)
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="logo">MultiplexPlay</div>
        <div class="nav-links">
            <a href="#">Home</a><a href="#movies">Movies</a><a href="#shows">TV Shows</a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">@csrf
                <button type="submit"
                    style="background:none;border:none;color:#e9e9e9;opacity:.9;cursor:pointer;font:inherit;margin-left:18px;">
                    <i class="fa-solid fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
        <button class="cta"><i class="fa-solid fa-location-dot"></i> Country:
            {{ session('country_code', 'IN') }}</button>
    </nav>

    <section class="player">
        <div class="player-box">
            <video id="videoPlayer" controls playsinline controlsList="nodownload noplaybackrate"
                disablePictureInPicture oncontextmenu="return false"></video>
            <div class="watermark">{{ auth()->user()->email ?? 'Guest' }} • <span id="timestamp"></span></div>
        </div>
    </section>

    <!-- Webseries panel appears here when a series is opened -->
    <section id="seriesPanel" class="section" style="display:none"></section>

    <div id="contentSections">
        <!-- Skeleton while loading -->
        <section class="section" id="skeleton">
            <h2>Loading…</h2>
            <div class="row-wrapper" data-row>
                <div class="row" id="skeletonRow"></div>
            </div>
        </section>
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

    <div id="toast" class="toast"></div>
    <footer>© <span id="year"></span> MultiplexPlay • All rights reserved.</footer>

    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script>
        // ---------- Config ----------
        const ROUTES = {
            home: '/api/ott/home',
            checkMovie: '/api/ott/check-movie',
            checkWeb: '/api/ott/check-webseries',
            manifest: '/api/video-manifest'
        };

        const USER = {
            id: @json(auth()->user()->_id ?? null),
            email: @json(auth()->user()->email ?? 'Guest'),
            country: 'IN'
        };

        // ---------- Utils ----------
        const $ = s => document.querySelector(s);
        const CE = (t, c) => {
            const el = document.createElement(t);
            if (c) el.className = c;
            return el;
        };

        function toast(msg, timeout = 2500) {
            const t = $('#toast');
            t.textContent = msg;
            t.style.display = 'block';
            setTimeout(() => t.style.display = 'none', timeout);
        }

        async function fetchJSON(url, opt = {}) {
            const res = await fetch(url, {
                credentials: 'same-origin',
                ...opt
            });
            if (!res.ok) {
                let m = `HTTP ${res.status}`;
                try {
                    m = (await res.json()).message || m;
                } catch {}
                throw new Error(m);
            }
            return res.json();
        }

        function toToken() {
            return btoa(JSON.stringify({
                timestamp: Math.floor(Date.now() / 1000)
            }));
        }

        function goodImg(item) {
            const u = item.thumbnail_url || item.poster_url || item.image_url;
            if (!u || u.includes('multiplexplay.com/storage/')) return '';
            return u;
        }

        function extractIdsFromVideo(item) {
            return {
                vId: item.videos_id || item._id || item.id,
                channelId: item.channel_id || ''
            };
        }

        // ---------- Player ----------
        let hls = null;

        function playVideo(url, title = '') {
            const video = $('#videoPlayer');
            if (!video || !url) return;
            try {
                if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = url;
                    video.play().catch(() => {});
                } else if (window.Hls && Hls.isSupported()) {
                    if (hls) hls.destroy();
                    hls = new Hls();
                    hls.loadSource(url);
                    hls.attachMedia(video);
                    video.play().catch(() => {});
                } else {
                    toast('HLS not supported on this browser');
                }
            } catch {
                toast('Unable to start playback');
            }
        }

        // ---------- Webseries panel ----------
        function renderSeriesUI(seriesPayload, {
            title,
            channelId,
            isSubscribed
        }) {
            const panel = $('#seriesPanel');
            const data = seriesPayload?.data || {};
            const seasons = Array.isArray(data.seasonsId) ? data.seasonsId : [];
            const poster = data.poster_url || data.image_url || data.thumbnail_url || '';
            const rel = data.release || '';

            panel.innerHTML = `
        <div class="series-head">
          <img class="series-cover" src="${poster}" alt="${title}">
          <div>
            <h3 class="series-title">${title || data.title || 'Series'}</h3>
            <div class="series-meta">${rel ? `Release: ${rel} • ` : ''}${seasons.length} season${seasons.length===1?'':'s'}</div>
            <div class="series-controls">
              <label>Season:&nbsp;</label>
              <select id="seasonSelect" class="series-select"></select>
              ${!isSubscribed ? '<span class="series-meta">• Locked content – subscribe to watch</span>' : ''}
            </div>
          </div>
        </div>
        <div id="episodesWrap" class="episodes"></div>
      `;

            // Season dropdown
            const seasonSelect = panel.querySelector('#seasonSelect');
            seasons.forEach((s, idx) => {
                const opt = document.createElement('option');
                opt.value = s._id || idx;
                opt.textContent = s.title || `Season ${idx+1}`;
                seasonSelect.appendChild(opt);
            });

            function renderEpisodes(idx) {
                const wrap = panel.querySelector('#episodesWrap');
                wrap.innerHTML = '';
                const season = seasons[idx] || {};
                const eps = Array.isArray(season.episodesId) ? season.episodesId : [];
                eps.forEach((ep, i) => {
                    const card = CE('div', 'ep-card' + (!isSubscribed ? ' locked' : ''));
                    const thumb = ep.thumbnail_url || '';
                    card.innerHTML = `
            <img class="ep-thumb" src="${thumb}" alt="${ep.title || `Episode ${i+1}`}">
            <div class="ep-body">
              <div class="ep-title">${ep.title || `Episode ${i+1}`}</div>
              <button class="ep-play"><i class="fa-solid fa-play"></i></button>
            </div>`;
                    card.querySelector('.ep-play').addEventListener('click', () => {
                        if (!isSubscribed) {
                            showPurchaseModal(true, title, channelId);
                            return;
                        }
                        const src = ep.video_url || ep.stream_url || null;
                        if (!src) {
                            toast('Episode source not available');
                            return;
                        }
                        const secure =
                            `${ROUTES.manifest}?url=${encodeURIComponent(src)}&token=${encodeURIComponent(toToken())}`;
                        playVideo(secure, `${title} – ${ep.title || 'Episode'}`);
                    });
                    wrap.appendChild(card);
                });
            }

            renderEpisodes(0);
            seasonSelect.addEventListener('change', () => renderEpisodes(seasonSelect.selectedIndex));

            panel.style.display = 'block';
            panel.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        // ---------- Secure play (movie vs series) ----------
        async function playSecure({
            m3u8,
            title,
            isWebseries,
            vId,
            channelId
        }) {
            if (!USER.id) {
                toast('Please login to watch');
                return;
            }

            try {
                if (isWebseries) {
                    const url =
                        `${ROUTES.checkWeb}?id=${encodeURIComponent(vId)}&user_id=${encodeURIComponent(USER.id)}&channel_id=${encodeURIComponent(channelId)}&field=_id&country=${USER.country}`;
                    const subData = await fetchJSON(url);

                    const isSubscribed = (typeof subData.isSubscribed === 'boolean') ?
                        subData.isSubscribed :
                        (subData.allowVideoAccess === true || subData.userSubscribed === true);

                    // Always render panel; gate playback by subscription
                    renderSeriesUI(subData, {
                        title,
                        channelId,
                        isSubscribed
                    });

                    if (!isSubscribed) return;

                    // Optionally auto-play first playable episode
                    const d = subData?.data || {};
                    const seasons = Array.isArray(d.seasonsId) ? d.seasonsId : [];
                    let first = null;
                    for (const s of seasons) {
                        for (const ep of (s.episodesId || [])) {
                            if (ep.video_url || ep.stream_url) {
                                first = ep;
                                break;
                            }
                        }
                        if (first) break;
                    }
                    if (first) {
                        const secure =
                            `${ROUTES.manifest}?url=${encodeURIComponent(first.video_url || first.stream_url)}&token=${encodeURIComponent(toToken())}`;
                        playVideo(secure, `${title} – ${first.title || 'Episode'}`);
                    }
                    return;
                }

                // MOVIE path
                const url =
                    `${ROUTES.checkMovie}?vId=${encodeURIComponent(vId)}&user_id=${encodeURIComponent(USER.id)}&channel_id=${encodeURIComponent(channelId)}&country=${USER.country}`;
                const subData = await fetchJSON(url);

                const isSubscribed = (typeof subData.isSubscribed === 'boolean') ?
                    subData.isSubscribed :
                    (subData.allowVideoAccess === true || subData.userSubscribed === true);

                if (!isSubscribed) {
                    const d = (Array.isArray(subData.data) ? subData.data[0] : subData.data) || {};
                    const isChannel = d?.isChannel === true;
                    showPurchaseModal(isChannel, title, channelId);
                    return;
                }

                const d = (Array.isArray(subData.data) ? subData.data[0] : subData.data) || {};
                const playUrl = d.video_url || d.stream_url || m3u8 || null;
                if (!playUrl) {
                    toast('Playable source not found');
                    return;
                }

                const secureUrl =
                    `${ROUTES.manifest}?url=${encodeURIComponent(playUrl)}&token=${encodeURIComponent(toToken())}`;
                playVideo(secureUrl, title);

            } catch (e) {
                console.warn(e);
                toast('Subscription verification failed');
            }
        }

        // ---------- Modal ----------
        function showPurchaseModal(isChannel, title, channelId = '') {
            const modal = $('#purchaseModal'),
                msg = $('#purchaseMessage'),
                btn = $('#purchaseBtn');
            if (isChannel === true) {
                msg.innerHTML =
                    `<strong>Channel Subscription Required</strong><br><br>Purchase channel access to watch "<strong>${title}</strong>".<br><small>Channel ID: ${channelId}</small>`;
                btn.textContent = 'Buy Channel Access';
            } else {
                msg.innerHTML =
                    `<strong>Premium Subscription Required</strong><br><br>Subscribe to a premium plan to watch "<strong>${title}</strong>".<br><small>Upgrade for full catalog access.</small>`;
                btn.textContent = 'Subscribe to Plan';
            }
            modal.style.display = 'flex';
        }
        $('#cancelBtn').addEventListener('click', () => {
            $('#purchaseModal').style.display = 'none';
        });
        $('#purchaseBtn').addEventListener('click', () => {
            window.location.href = '/pricing';
        });

        // ---------- Rows ----------
        function createSection(container, sectionName, items, forceTvseries = null) {
    if (!Array.isArray(items) || !items.length) return;

    const section = CE('section', 'section');
    section.innerHTML = `
        <h2>${sectionName}</h2>
        <p class="muted">Swipe or use arrows to scroll →</p>
        <div class="row-wrapper" data-row-wrapper>
          <button class="arrow left"><i class="fa-solid fa-chevron-left"></i></button>
          <div class="row"></div>
          <button class="arrow right"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    `;

    const rowWrapper = section.querySelector('.row-wrapper');
    const row = section.querySelector('.row');

    items.slice(0, 18).forEach(item => {
        const poster = goodImg(item) || `https://picsum.photos/400/600?random=${Math.floor(Math.random()*100000)}`;
        const title = item.title || item.name || 'Untitled';
        const isWebseries = (forceTvseries !== null) ? !!forceTvseries : (String(item.is_tvseries) === '1');
        const badge = (String(item.is_paid) === '1' || item.is_paid === 1) ? 'PAID' : (item.video_quality || 'FREE');
        const { vId, channelId } = extractIdsFromVideo(item);

        // Create card element
        const card = CE('article', 'card');
        card.dataset.vId = vId;
        card.dataset.channelId = channelId;

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
              <button class="add" title="Add to list"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>
        `;

        row.appendChild(card);
    });

    // ✅ Delegated click listener for all play buttons
    rowWrapper.addEventListener('click', (e) => {
        const playBtn = e.target.closest('.play');
        if (!playBtn) return; // not a play button

        e.stopPropagation();  // prevent parent row clicks
        e.preventDefault();

        const card = playBtn.closest('.card');
        const title = card.querySelector('.title').textContent;

        const videoData = {
            vId: card.dataset.vId,
            channelId: card.dataset.channelId,
            title,
        };

        console.log("👉 PLAY button clicked:", title);
        playVideo(videoData);
    });

    container.appendChild(section);
}


        // function createSection(container, sectionName, items, forceTvseries = null) {
        //     if (!Array.isArray(items) || !items.length) return;

        //     const section = CE('section', 'section');
        //     section.innerHTML = `
        // <h2>${sectionName}</h2>
        // <p class="muted">Swipe or use arrows to scroll →</p>
        // <div class="row-wrapper" data-row>
        //   <button class="arrow left"><i class="fa-solid fa-chevron-left"></i></button>
        //   <div class="row"></div>
        //   <button class="arrow right"><i class="fa-solid fa-chevron-right"></i></button>
        // </div>`;

        //     const row = section.querySelector('.row');

        //     items.slice(0, 18).forEach(item => {

        //         const poster = goodImg(item) ||

        //             `https://picsum.photos/400/600?random=${Math.floor(Math.random()*100000)}`;
        //         const title = item.title || item.name || 'Untitled';
        //         const isWebseries = (forceTvseries !== null) ? !!forceTvseries : (String(item.is_tvseries) === '1');
        //         const badge = (String(item.is_paid) === '1' || item.is_paid === 1) ? 'PAID' : (item.video_quality ||
        //             'FREE');
        //         const {
        //             vId,
        //             channelId
        //         } = extractIdsFromVideo(item);

        //         const card = CE('article', 'card');
        //         card.innerHTML = `
        //   <div class="poster">
        //     <img src="${poster}" alt="${title}">
        //     <span class="badge">${badge}</span>
        //   </div>
        //   <div class="meta">
        //     <div class="title">${title}</div>
        //     <div class="sub">${isWebseries ? 'Series' : 'Movie'}</div>
        //     <div class="actions">
        //       <button class="play"><i class="fa-solid fa-play"></i> ${isWebseries ? 'Watch' : 'Play'}</button>
        //       <button class="add" title="Add to list"><i class="fa-solid fa-plus"></i></button>
        //     </div>
        //   </div>`;

        //         // ✅ Play button listener
        //         card.querySelector('.play').addEventListener('click', () => {
        //             alert("✅ Play button clicked!");
        //             console.log("👉 PLAY button clicked:", title);
        //         });

        //         const btn = card.querySelector(".play");
        //         if (btn) {
        //             btn.addEventListener("click", () => {
        //                 console.log("👉 PLAY button clicked:", title);
        //                 playVideo(videoData);
        //             });
        //         }
        //         row.appendChild(card);
        //     });

        //     container.appendChild(section);


        // }



        function setupRowNavigation() {
            const ROW_SCROLL = 600;
            document.querySelectorAll('[data-row]').forEach(wrap => {
                const row = wrap.querySelector('.row'),
                    left = wrap.querySelector('.arrow.left'),
                    right = wrap.querySelector('.arrow.right');
                if (!row || !left || !right) return;
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

                // drag to scroll
                let isDown = false,
                    startX = 0,
                    startLeft = 0;
                row.addEventListener('pointerdown', e => {
                    isDown = true;
                    startX = e.clientX;
                    startLeft = row.scrollLeft;
                    row.setPointerCapture(e.pointerId);
                });
                row.addEventListener('pointermove', e => {
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

        // ---------- Boot ----------
        (function buildSkeleton() {
            const row = $('#skeletonRow');
            for (let i = 0; i < 12; i++) {
                const sk = CE('div', 'card');
                sk.innerHTML =
                    `<div class="poster skeleton poster"></div><div class="meta"><div class="title skeleton" style="height:18px;width:70%"></div><div class="sub skeleton" style="height:14px;width:40%;margin-top:6px"></div></div>`;
                row.appendChild(sk);
            }
        })();

        (async function loadHome() {
            try {
                const json = await fetchJSON(
                    `${ROUTES.home}?country=${encodeURIComponent(USER.country)}`);
                console.log(json);
                const sk = $('#skeleton');
                if (sk) sk.remove();

                const features = json?.sections || [];
                const container = $('#contentSections');

                features.forEach(section => {
                    const name = section.name || 'Featured';

                    const movies = Array.isArray(section.movies) ? section.movies : [];
                    const series = Array.isArray(section.series) ? section.series : [];

                    if (movies.length) {
                        createSection(container, `${name} – Movies`, movies, false);
                    }

                    if (series.length) {
                        createSection(container, `${name} – TV Series`, series, true);
                    }
                });


                setupRowNavigation();

                // Optional: autoplay first movie via proxy
                const first = features.flatMap(s => s.videos || []).find(v => String(v
                        .is_tvseries) ===
                    '0');
                const firstTitle = first?.title || 'Now Playing';
                const firstUrl = first?.video_url || first?.stream_url || null;
                if (firstUrl) {
                    const secure =
                        `${ROUTES.manifest}?url=${encodeURIComponent(firstUrl)}&token=${encodeURIComponent(toToken())}`;
                    playVideo(secure, firstTitle);
                }
            } catch (e) {
                console.warn('Failed loading content:', e);
                $('#contentSections').innerHTML =
                    '<div style="padding:20px;text-align:center;color:#b5b5b5">Failed to load content. Please refresh.</div>';
            }
        })();

        // Anti-piracy (best-effort)
        document.addEventListener('keydown', (e) => {
            const block = (e.ctrlKey && /[spSP]/.test(e.key)) || (e.key === 'PrintScreen');
            if (block) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        // Timestamp + year
        setInterval(() => {
            const ts = $('#timestamp');
            if (ts) ts.textContent = new Date().toLocaleTimeString();
        }, 1000);
        $('#year').textContent = new Date().getFullYear();

                // 🕵️ Global click debugger
        document.addEventListener("click", (e) => {
            console.log("👉 Click detected on:", e.target);

            // highlight करके भी दिखा देंगे
            e.target.style.outline = "2px solid red";

            // parent chain दिखाओ
            let parents = [];
            let node = e.target;
            while (node) {
                parents.push(node.tagName + (node.className ? "." + node.className : ""));
                node = node.parentElement;
            }
            console.log("🔗 Event bubbling path:", parents.join(" -> "));
        });

// existing createSection function code ...
// ...
// यहाँ तक createSection खत्म हो गया

// 🕵️ Global forced click capture for Play button
document.addEventListener("click", async (e) => {
    const btn = e.target.closest(".play");
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const card = btn.closest(".card");
    const title = card?.querySelector(".title")?.innerText || "Untitled";
    const itemId = card.getAttribute("data-id");

    alert("🎬 Play button clicked: " + title);

    let apiUrl = `${ROUTES.checkMovie}?id=${encodeURIComponent(itemId)}`;
    if (card.querySelector(".sub")?.innerText === "Series") {
        apiUrl = `${ROUTES.checkWeb}?id=${encodeURIComponent(itemId)}`;
    }

    const res = await fetch(apiUrl);
    const data = await res.json();
    console.log("👉 API Response:", data);

    const videoUrl = data.video_url || data.stream_url;
    if (!videoUrl) {
        alert("❌ No video url found.");
        return;
    }

    const secureUrl = `${ROUTES.manifest}?url=${encodeURIComponent(videoUrl)}&token=${encodeURIComponent(toToken())}`;
    playVideo(secureUrl, title);
});
    </script>
</body>

</html>
