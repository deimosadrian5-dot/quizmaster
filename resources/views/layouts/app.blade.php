<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'QuizMaster') — QuizMaster</title>

    <!-- ─── Theme Engine (runs before Tailwind) ─── -->
    <script>
        const THEMES = {
            indigo: {
                label:'Indigo',
                primary:'#4f46e5', secondary:'#7c3aed', rgb:'99,102,241',
                palette:{50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81'}
            },
            ocean: {
                label:'Ocean',
                primary:'#0284c7', secondary:'#06b6d4', rgb:'2,132,199',
                palette:{50:'#f0f9ff',100:'#e0f2fe',200:'#bae6fd',300:'#7dd3fc',400:'#38bdf8',500:'#0ea5e9',600:'#0284c7',700:'#0369a1',800:'#075985',900:'#0c4a6e'}
            },
            emerald: {
                label:'Emerald',
                primary:'#059669', secondary:'#10b981', rgb:'5,150,105',
                palette:{50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',800:'#065f46',900:'#064e3b'}
            },
            sunset: {
                label:'Sunset',
                primary:'#ea580c', secondary:'#f59e0b', rgb:'234,88,12',
                palette:{50:'#fff7ed',100:'#ffedd5',200:'#fed7aa',300:'#fdba74',400:'#fb923c',500:'#f97316',600:'#ea580c',700:'#c2410c',800:'#9a3412',900:'#7c2d12'}
            },
            rose: {
                label:'Rose',
                primary:'#e11d48', secondary:'#ec4899', rgb:'225,29,72',
                palette:{50:'#fff1f2',100:'#ffe4e6',200:'#fecdd3',300:'#fda4af',400:'#fb7185',500:'#f43f5e',600:'#e11d48',700:'#be123c',800:'#9f1239',900:'#881337'}
            },
            midnight: {
                label:'Midnight',
                primary:'#1e293b', secondary:'#475569', rgb:'30,41,59',
                palette:{50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a'}
            }
        };
        const savedTheme = localStorage.getItem('qm-theme') || 'indigo';
        const T = THEMES[savedTheme] || THEMES.indigo;

        // Set CSS custom properties for non-Tailwind usage (gradients, SVG, rgba)
        const ds = document.documentElement.style;
        Object.entries(T.palette).forEach(([k, v]) => ds.setProperty('--t-' + k, v));
        ds.setProperty('--t-primary', T.primary);
        ds.setProperty('--t-secondary', T.secondary);
        ds.setProperty('--t-rgb', T.rgb);
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: T.palette,
                        // Override Tailwind's default indigo so existing indigo-* classes follow theme
                        indigo: T.palette
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        /* ── Base ── */
        body { font-family: 'Inter', sans-serif; }

        .gradient-bg   { background: linear-gradient(135deg, var(--t-primary) 0%, var(--t-secondary) 100%); }
        .gradient-warm { background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); }

        /* ── Interactions ── */
        .card-hover   { transition: all 0.25s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.12); }

        .quiz-option          { transition: all 0.2s ease; cursor: pointer; }
        .quiz-option:hover    { transform: scale(1.02); border-color: var(--t-500); background: var(--t-50); }
        .quiz-option.selected { border-color: var(--t-500); background: var(--t-100); box-shadow: 0 0 0 3px rgba(var(--t-rgb),.25); }
        .quiz-option.correct  { border-color: #10b981; background: #d1fae5; }
        .quiz-option.incorrect{ border-color: #ef4444; background: #fee2e2; }

        .progress-bar { transition: width 0.5s ease; }
        .timer-ring   { transition: stroke-dashoffset 1s linear; }

        /* ── Animations ── */
        .fade-in   { animation: fadeIn .45s ease-out; }
        @keyframes fadeIn {
            from { opacity:0; transform:translateY(16px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .bounce-in { animation: bounceIn .55s cubic-bezier(.68,-.55,.265,1.55); }
        @keyframes bounceIn {
            from { opacity:0; transform:scale(.4); }
            to   { opacity:1; transform:scale(1); }
        }
        .pulse-glow { animation: pulseGlow 2.5s infinite; }
        @keyframes pulseGlow {
            0%,100% { box-shadow:0 0 4px rgba(var(--t-rgb),.4); }
            50%     { box-shadow:0 0 20px rgba(var(--t-rgb),.6); }
        }
        .confetti { position:fixed; width:10px; height:10px; top:-10px; animation:confettiFall 3s linear forwards; }
        @keyframes confettiFall { to { top:100vh; transform:rotate(720deg); } }
        .timer-shake { animation: timerShake .5s ease-in-out infinite; }
        @keyframes timerShake {
            0%,100% { transform: rotate(-90deg) scale(1); }
            25%     { transform: rotate(-90deg) scale(1.08); }
            50%     { transform: rotate(-90deg) scale(1); }
            75%     { transform: rotate(-90deg) scale(1.08); }
        }

        /* ── Toast ── */
        .toast-container { position:fixed; top:76px; right:16px; z-index:10000; display:flex; flex-direction:column; gap:8px; }
        .toast {
            padding:10px 18px; border-radius:10px; font-weight:600; font-size:13px; color:#fff;
            box-shadow:0 6px 20px rgba(0,0,0,.12); transform:translateX(120%);
            transition:transform .35s cubic-bezier(.175,.885,.32,1.275); max-width:300px;
            display:flex; align-items:center; gap:8px;
        }
        .toast.show    { transform:translateX(0); }
        .toast.warning { background:linear-gradient(135deg,#f59e0b,#d97706); }
        .toast.danger  { background:linear-gradient(135deg,#ef4444,#dc2626); }
        .toast.success { background:linear-gradient(135deg,#10b981,#059669); }
        .toast.info    { background:linear-gradient(135deg, var(--t-500), var(--t-secondary)); }

        /* ── Modal ── */
        .modal-overlay {
            position:fixed; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(6px);
            z-index:9998; display:flex; align-items:center; justify-content:center;
            opacity:0; pointer-events:none; transition:opacity .2s ease;
        }
        .modal-overlay.active { opacity:1; pointer-events:auto; }
        .modal-box {
            background:#fff; border-radius:1rem; padding:2rem; max-width:26rem; width:92%;
            transform:scale(.88) translateY(16px);
            transition:transform .28s cubic-bezier(.34,1.56,.64,1);
            box-shadow:0 20px 50px rgba(0,0,0,.2);
        }
        .modal-overlay.active .modal-box { transform:scale(1) translateY(0); }
        .modal-icon-wrap {
            width:3.5rem; height:3.5rem; border-radius:50%; display:flex;
            align-items:center; justify-content:center; margin:0 auto .85rem;
        }
        .modal-icon-wrap svg { width:1.5rem; height:1.5rem; }
        .modal-icon-wrap.green  { background:#d1fae5; color:#059669; }
        .modal-icon-wrap.red    { background:#fee2e2; color:#dc2626; }
        .modal-icon-wrap.amber  { background:#fef3c7; color:#d97706; }
        .modal-icon-wrap.blue   { background:#dbeafe; color:#2563eb; }
        .modal-icon-wrap.purple { background:#ede9fe; color:#7c3aed; }
        .modal-title   { font-size:1.125rem; font-weight:700; text-align:center; color:#111827; margin-bottom:.35rem; }
        .modal-message { text-align:center; color:#6b7280; font-size:.85rem; margin-bottom:1.25rem; line-height:1.6; }
        .modal-actions { display:flex; gap:.6rem; justify-content:center; }
        .modal-btn {
            padding:.55rem 1.25rem; border-radius:.6rem; font-weight:600; font-size:.8125rem;
            cursor:pointer; transition:all .2s; border:2px solid transparent;
        }
        .modal-btn:hover { transform:translateY(-1px); }
        .modal-btn-primary { background:linear-gradient(135deg, var(--t-primary), var(--t-secondary)); color:#fff; }
        .modal-btn-primary:hover { opacity:.9; }
        .modal-btn-danger  { background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; }
        .modal-btn-danger:hover  { opacity:.9; }
        .modal-btn-success { background:linear-gradient(135deg,#10b981,#059669); color:#fff; }
        .modal-btn-success:hover { opacity:.9; }
        .modal-btn-cancel  { background:#f3f4f6; color:#4b5563; border-color:#e5e7eb; }
        .modal-btn-cancel:hover  { background:#e5e7eb; }
        .modal-btn-warning { background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; }
        .modal-btn-warning:hover { opacity:.9; }

        /* ── Nav ── */
        nav a.nav-link { position:relative; }
        nav a.nav-link::after {
            content:''; position:absolute; bottom:-2px; left:50%; width:0; height:2px;
            background:#fff; transition:all .25s ease; transform:translateX(-50%);
        }
        nav a.nav-link:hover::after { width:100%; }

        /* ── Theme Picker ── */
        .theme-dropdown {
            opacity:0; pointer-events:none; transform:scale(.92) translateY(-4px);
            transition: opacity .2s ease, transform .2s ease;
        }
        .theme-dropdown.open {
            opacity:1; pointer-events:auto; transform:scale(1) translateY(0);
        }
        .theme-swatch {
            width:26px; height:26px; border-radius:50%; cursor:pointer;
            border:2px solid rgba(255,255,255,.3); transition:all .2s; flex-shrink:0;
        }
        .theme-swatch:hover { transform:scale(1.15); border-color:rgba(255,255,255,.6); }
        .theme-swatch-lg {
            width:32px; height:32px; border-radius:50%; cursor:pointer;
            border:2px solid #e5e7eb; transition:all .2s; flex-shrink:0;
        }
        .theme-swatch-lg:hover { transform:scale(1.1); }
        .theme-swatch-lg.active { border-color:#111827; box-shadow:0 0 0 2px #fff, 0 0 0 4px #9ca3af; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- ─── Navbar ─── -->
    <nav class="gradient-bg text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-2.5 group">
                    <img src="{{ asset('img/logo.png') }}" alt="QuizMaster" class="h-9 w-9 object-contain">
                    <span class="text-lg font-bold tracking-tight">QuizMaster</span>
                </a>

                <!-- Desktop Nav Links -->
                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('quizzes.index') }}" class="nav-link flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i> Quizzes
                    </a>
                    <a href="{{ route('quizzes.create') }}" class="nav-link flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> Create
                    </a>
                    <a href="{{ route('quizzes.generate') }}" class="nav-link flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                        <i data-lucide="zap" class="w-4 h-4"></i> Generate
                    </a>
                    <a href="{{ route('quizzes.leaderboard') }}" class="nav-link flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                        <i data-lucide="trophy" class="w-4 h-4"></i> Leaderboard
                    </a>
                </div>

                <!-- Right side controls -->
                <div class="flex items-center gap-1">
                    <!-- Theme Picker -->
                    <div class="relative" id="theme-picker-wrapper">
                        <button onclick="toggleThemePicker()" class="p-2 rounded-lg hover:bg-white/10 transition" title="Change Theme">
                            <i data-lucide="palette" class="w-5 h-5"></i>
                        </button>
                        <div id="theme-dropdown" class="theme-dropdown absolute right-0 top-full mt-2 bg-white rounded-xl shadow-2xl border border-gray-100 p-3 w-56 z-[60]">
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-1 mb-2">Choose Theme</p>
                            <div id="theme-options" class="space-y-0.5"></div>
                        </div>
                    </div>

                    <!-- Mobile hamburger -->
                    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="md:hidden p-2 rounded-lg hover:bg-white/10 transition">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4 space-y-1">
                <a href="{{ route('quizzes.index') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i> Quizzes
                </a>
                <a href="{{ route('quizzes.create') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Create
                </a>
                <a href="{{ route('quizzes.generate') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                    <i data-lucide="zap" class="w-4 h-4"></i> Generate
                </a>
                <a href="{{ route('quizzes.leaderboard') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                    <i data-lucide="trophy" class="w-4 h-4"></i> Leaderboard
                </a>
            </div>
        </div>
    </nav>

    <!-- ─── Flash Messages ─── -->
    @if(session('error'))
    <div class="modal-overlay active" id="flash-modal" onclick="closeFlashModal(event)">
        <div class="modal-box">
            <div class="modal-icon-wrap red"><i data-lucide="alert-circle" class="w-6 h-6"></i></div>
            <div class="modal-title">Oops!</div>
            <div class="modal-message">{{ session('error') }}</div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-danger" onclick="document.getElementById('flash-modal').classList.remove('active')">Got it</button>
            </div>
        </div>
    </div>
    @endif
    @if(session('success'))
    <div class="modal-overlay active" id="flash-modal" onclick="closeFlashModal(event)">
        <div class="modal-box">
            <div class="modal-icon-wrap green"><i data-lucide="check-circle" class="w-6 h-6"></i></div>
            <div class="modal-title">Awesome!</div>
            <div class="modal-message">{{ session('success') }}</div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-success" onclick="document.getElementById('flash-modal').classList.remove('active')">Let's Go</button>
            </div>
        </div>
    </div>
    @endif

    <!-- ─── Reusable Modal ─── -->
    <div class="modal-overlay" id="app-modal" onclick="closeAppModal(event)">
        <div class="modal-box">
            <div class="modal-icon-wrap blue" id="app-modal-icon-wrap">
                <i data-lucide="info" id="app-modal-icon" class="w-6 h-6"></i>
            </div>
            <div class="modal-title" id="app-modal-title">Notice</div>
            <div class="modal-message" id="app-modal-message"></div>
            <div class="modal-actions" id="app-modal-actions"></div>
        </div>
    </div>

    <!-- ─── Content ─── -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- ─── Footer ─── -->
    <footer class="bg-gray-900 text-gray-400 py-10 mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('img/logo.png') }}" alt="QuizMaster" class="h-8 w-8 rounded-lg object-contain">
                    <span class="font-semibold text-gray-300">QuizMaster</span>
                </div>
                <p class="text-sm">&copy; {{ date('Y') }} QuizMaster. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('quizzes.index') }}" class="text-sm hover:text-white transition">Quizzes</a>
                    <a href="{{ route('quizzes.create') }}" class="text-sm hover:text-white transition">Create</a>
                    <a href="{{ route('quizzes.leaderboard') }}" class="text-sm hover:text-white transition">Leaderboard</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ─── Toast Container ─── -->
    <div class="toast-container" id="toast-container"></div>

    <!-- ─── Global JS ─── -->
    <script>
        // Initialise Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            renderThemeOptions();
        });

        function refreshIcons() { lucide.createIcons(); }

        // ── Theme Picker ──
        function toggleThemePicker() {
            document.getElementById('theme-dropdown').classList.toggle('open');
        }

        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('theme-picker-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                document.getElementById('theme-dropdown').classList.remove('open');
            }
        });

        function setTheme(name) {
            localStorage.setItem('qm-theme', name);
            window.location.reload();
        }

        function renderThemeOptions() {
            const container = document.getElementById('theme-options');
            if (!container) return;
            let html = '';
            Object.entries(THEMES).forEach(([key, t]) => {
                const active = key === savedTheme;
                html += `
                <button onclick="setTheme('${key}')" class="flex items-center gap-3 w-full px-2 py-2 rounded-lg hover:bg-gray-50 transition text-left group">
                    <div class="theme-swatch-lg ${active ? 'active' : ''}" style="background:linear-gradient(135deg, ${t.primary}, ${t.secondary})"></div>
                    <span class="text-sm font-medium ${active ? 'text-gray-900' : 'text-gray-600'}">${t.label}</span>
                    ${active ? '<svg class="w-4 h-4 text-green-500 ml-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' : ''}
                </button>`;
            });
            container.innerHTML = html;
        }

        // ── Toast ──
        function showToast(message, type, duration) {
            type = type || 'info'; duration = duration || 2500;
            const container = document.getElementById('toast-container');
            const t = document.createElement('div');
            t.className = 'toast ' + type;
            t.textContent = message;
            container.appendChild(t);
            requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('show')));
            setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 400); }, duration);
        }

        // ── Flash / App Modal helpers ──
        function closeFlashModal(e) { if (e.target === e.currentTarget) e.currentTarget.classList.remove('active'); }
        function closeAppModal(e)   { if (e.target === e.currentTarget) e.currentTarget.classList.remove('active'); }

        /**
         * Show a modal.
         * @param {Object} opts
         * @param {string} opts.icon      – Lucide icon name
         * @param {string} opts.iconColor – 'green'|'red'|'amber'|'blue'|'purple'
         * @param {string} opts.title
         * @param {string} opts.message   – Can contain HTML
         * @param {Array}  opts.buttons   – [{text, class, onClick}]
         */
        function showModal(opts) {
            const modal = document.getElementById('app-modal');
            const wrap  = document.getElementById('app-modal-icon-wrap');
            const iconEl= document.getElementById('app-modal-icon');

            wrap.className = 'modal-icon-wrap ' + (opts.iconColor || 'blue');
            iconEl.setAttribute('data-lucide', opts.icon || 'info');
            lucide.createIcons({ attrs: { id: 'app-modal-icon' } });

            document.getElementById('app-modal-title').textContent = opts.title || 'Notice';
            document.getElementById('app-modal-message').innerHTML = opts.message || '';

            const actions = document.getElementById('app-modal-actions');
            actions.innerHTML = '';
            (opts.buttons || []).forEach(btn => {
                const b = document.createElement('button');
                b.className = 'modal-btn ' + (btn.class || 'modal-btn-primary');
                b.textContent = btn.text || 'OK';
                b.onclick = () => { modal.classList.remove('active'); if (btn.onClick) btn.onClick(); };
                actions.appendChild(b);
            });
            modal.classList.add('active');
        }

        function hideModal() { document.getElementById('app-modal').classList.remove('active'); }

        function showAlert(icon, iconColor, title, message, btnText, btnClass) {
            showModal({ icon, iconColor, title, message, buttons: [{ text: btnText||'OK', class: btnClass||'modal-btn-primary' }] });
        }

        function showConfirm(icon, iconColor, title, message, confirmText, confirmClass, onConfirm) {
            showModal({
                icon, iconColor, title, message,
                buttons: [
                    { text: 'Cancel', class: 'modal-btn-cancel' },
                    { text: confirmText||'Confirm', class: confirmClass||'modal-btn-primary', onClick: onConfirm }
                ]
            });
        }

        // Auto-close flash modal
        setTimeout(() => { const f = document.getElementById('flash-modal'); if (f) f.classList.remove('active'); }, 5000);

        // Escape closes modals & theme picker
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
                document.getElementById('theme-dropdown').classList.remove('open');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
