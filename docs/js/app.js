/**
 * QuizMaster — Shared Application Logic
 * Data layer (localStorage), layout, theme, modals, toasts, utilities.
 */

/* ════════════════════════════════════
   DATA LAYER (localStorage)
   ════════════════════════════════════ */

const DB = {
    _get(key) { try { return JSON.parse(localStorage.getItem(key)) || []; } catch { return []; } },
    _set(key, val) { localStorage.setItem(key, JSON.stringify(val)); },

    nextId(key) {
        return Date.now() + Math.floor(Math.random() * 1000);
    },

    // ── Quizzes ──
    getQuizzes() { return this._get('qm_quizzes'); },
    getQuiz(id) { return this.getQuizzes().find(q => q.id === parseInt(id)); },
    saveQuiz(quiz) {
        const quizzes = this.getQuizzes();
        if (!quiz.id) {
            quiz.id = this.nextId('quiz');
            quiz.created_at = new Date().toISOString();
            quiz.questions = (quiz.questions || []).map((q, i) => ({...q, id: i + 1, points: q.points || 10}));
            quizzes.push(quiz);
        } else {
            const idx = quizzes.findIndex(q => q.id === quiz.id);
            if (idx >= 0) quizzes[idx] = quiz;
        }
        this._set('qm_quizzes', quizzes);
        if (typeof fbSaveQuiz === 'function') fbSaveQuiz(quiz);
        return quiz;
    },
    deleteQuiz(id) {
        id = parseInt(id);
        this._set('qm_quizzes', this.getQuizzes().filter(q => q.id !== id));
        this._set('qm_attempts', this.getAttempts().filter(a => a.quiz_id !== id));
        if (typeof fbDeleteQuiz === 'function') fbDeleteQuiz(id);
    },

    // ── Attempts ──
    getAttempts() { return this._get('qm_attempts'); },
    getAttemptsForQuiz(quizId) { return this.getAttempts().filter(a => a.quiz_id === parseInt(quizId)); },
    saveAttempt(attempt) {
        const attempts = this.getAttempts();
        attempt.id = this.nextId('attempt');
        attempt.created_at = new Date().toISOString();
        attempts.push(attempt);
        this._set('qm_attempts', attempts);
        if (typeof fbSaveAttempt === 'function') fbSaveAttempt(attempt);
        return attempt;
    },

    // ── Seeding ──
    isSeeded() { return localStorage.getItem('qm_seeded') === '1'; },
    seed() {
        if (this.isSeeded()) return;
        SEED_QUIZZES.forEach((sq, i) => {
            const quiz = {
                id: i + 1,
                title: sq.title,
                description: sq.description,
                category: sq.category,
                difficulty: sq.difficulty,
                time_per_question: sq.time_per_question,
                created_at: new Date().toISOString(),
                questions: (sq.questions || []).map((q, j) => ({...q, id: j + 1, points: q.points || 10})),
            };
            const quizzes = this.getQuizzes();
            quizzes.push(quiz);
            this._set('qm_quizzes', quizzes);
        });
        localStorage.setItem('qm_seeded', '1');
    },
};

/* ════════════════════════════════════
   UTILITIES
   ════════════════════════════════════ */

function escapeHtml(text) {
    if (!text) return '';
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

function getCategoryIcon(category) {
    const map = {
        'Science':'flask-conical','Science & Nature':'flask-conical','Science: Computers':'cpu',
        'Mathematics':'calculator','History':'landmark','Geography':'globe','Movies':'clapperboard',
        'Music':'music','Technology':'monitor','Sports':'medal','Food':'utensils',
        'Animals':'paw-print','General Knowledge':'lightbulb','Television':'tv',
        'Video Games':'gamepad-2','Mythology':'swords','Art':'palette','Celebrities':'star',
        'Vehicles':'car','Comics':'book-open','Anime & Manga':'sparkles','Cartoons':'smile',
    };
    return map[category] || 'book-open';
}

function getDifficultyColor(difficulty) {
    return {easy:'green', medium:'yellow', hard:'red'}[difficulty] || 'gray';
}

function getGrade(percentage) {
    if (percentage >= 90) return {label:'Genius!', icon:'crown', color:'yellow'};
    if (percentage >= 70) return {label:'Great Job!', icon:'thumbs-up', color:'green'};
    if (percentage >= 50) return {label:'Not Bad!', icon:'smile', color:'blue'};
    if (percentage >= 30) return {label:'Keep Trying!', icon:'trending-up', color:'orange'};
    return {label:'Better Luck Next Time!', icon:'refresh-cw', color:'red'};
}

function timeAgo(dateString) {
    const seconds = Math.floor((Date.now() - new Date(dateString).getTime()) / 1000);
    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return minutes + 'm ago';
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return hours + 'h ago';
    const days = Math.floor(hours / 24);
    if (days < 30) return days + 'd ago';
    const months = Math.floor(days / 30);
    if (months < 12) return months + 'mo ago';
    return Math.floor(months / 12) + 'y ago';
}

function pluralize(word, count) {
    return count === 1 ? word : word + 's';
}

function getUrlParam(name) {
    return new URLSearchParams(window.location.search).get(name);
}

/* Theme vars (THEMES, savedTheme, T) are declared in <head> inline script before Tailwind */

/* ════════════════════════════════════
   LAYOUT INJECTION
   ════════════════════════════════════ */

function injectLayout() {
    // Determine relative path to root (for logo/links)
    const isSubdir = window.location.pathname.includes('/docs/');
    const base = ''; // All files in same dir

    // ── Navbar ──
    const nav = document.getElementById('main-nav');
    if (nav) {
        nav.className = 'gradient-bg text-white shadow-lg sticky top-0 z-50';
        nav.innerHTML = `
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="${base}index.html" class="flex items-center gap-2.5 group">
                    <img src="${base}img/logo.png" alt="QuizMaster" class="h-9 w-9 object-contain">
                    <span class="text-lg font-bold tracking-tight">QuizMaster</span>
                </a>
                <div class="hidden md:flex items-center gap-1">
                    <a href="${base}quizzes.html" class="nav-link flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i> Quizzes
                    </a>
                    <a href="${base}create.html" class="nav-link flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> Create
                    </a>
                    <a href="${base}generate.html" class="nav-link flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                        <i data-lucide="zap" class="w-4 h-4"></i> Generate
                    </a>
                    <a href="${base}leaderboard.html" class="nav-link flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                        <i data-lucide="trophy" class="w-4 h-4"></i> Leaderboard
                    </a>
                </div>
                <div class="flex items-center gap-1">
                    <div class="relative" id="theme-picker-wrapper">
                        <button onclick="toggleThemePicker()" class="p-2 rounded-lg hover:bg-white/10 transition" title="Change Theme">
                            <i data-lucide="palette" class="w-5 h-5"></i>
                        </button>
                        <div id="theme-dropdown" class="theme-dropdown absolute right-0 top-full mt-2 bg-white rounded-xl shadow-2xl border border-gray-100 p-3 w-56 z-[60]">
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-1 mb-2">Choose Theme</p>
                            <div id="theme-options" class="space-y-0.5"></div>
                        </div>
                    </div>
                    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="md:hidden p-2 rounded-lg hover:bg-white/10 transition">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
            <div id="mobile-menu" class="hidden md:hidden pb-4 space-y-1">
                <a href="${base}quizzes.html" class="flex items-center gap-2 px-3 py-2.5 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i> Quizzes
                </a>
                <a href="${base}create.html" class="flex items-center gap-2 px-3 py-2.5 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Create
                </a>
                <a href="${base}generate.html" class="flex items-center gap-2 px-3 py-2.5 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                    <i data-lucide="zap" class="w-4 h-4"></i> Generate
                </a>
                <a href="${base}leaderboard.html" class="flex items-center gap-2 px-3 py-2.5 rounded-lg hover:bg-white/10 transition text-sm font-medium">
                    <i data-lucide="trophy" class="w-4 h-4"></i> Leaderboard
                </a>
            </div>
        </div>`;
    }

    // ── Footer ──
    const footer = document.getElementById('main-footer');
    if (footer) {
        footer.className = 'bg-gray-900 text-gray-400 py-10 mt-auto';
        footer.innerHTML = `
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <img src="${base}img/logo.png" alt="QuizMaster" class="h-8 w-8 rounded-lg object-contain">
                    <span class="font-semibold text-gray-300">QuizMaster</span>
                </div>
                <p class="text-sm">&copy; ${new Date().getFullYear()} QuizMaster. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="${base}quizzes.html" class="text-sm hover:text-white transition">Quizzes</a>
                    <a href="${base}create.html" class="text-sm hover:text-white transition">Create</a>
                    <a href="${base}leaderboard.html" class="text-sm hover:text-white transition">Leaderboard</a>
                </div>
            </div>
        </div>`;
    }

    // ── Modal Container ──
    if (!document.getElementById('app-modal')) {
        document.body.insertAdjacentHTML('beforeend', `
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
        <div class="toast-container" id="toast-container"></div>`);
    }
}

/* ════════════════════════════════════
   THEME PICKER
   ════════════════════════════════════ */

function toggleThemePicker() {
    document.getElementById('theme-dropdown').classList.toggle('open');
}

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

/* ════════════════════════════════════
   TOAST
   ════════════════════════════════════ */

function showToast(message, type, duration) {
    type = type || 'info'; duration = duration || 2500;
    const container = document.getElementById('toast-container');
    if (!container) return;
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.textContent = message;
    container.appendChild(t);
    requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('show')));
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 400); }, duration);
}

/* ════════════════════════════════════
   MODAL
   ════════════════════════════════════ */

function closeAppModal(e) { if (e.target === e.currentTarget) e.currentTarget.classList.remove('active'); }

function showModal(opts) {
    const modal = document.getElementById('app-modal');
    const wrap = document.getElementById('app-modal-icon-wrap');
    const iconEl = document.getElementById('app-modal-icon');

    wrap.className = 'modal-icon-wrap ' + (opts.iconColor || 'blue');
    iconEl.setAttribute('data-lucide', opts.icon || 'info');
    lucide.createIcons({attrs:{id:'app-modal-icon'}});

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
    showModal({icon, iconColor, title, message, buttons:[{text:btnText||'OK', class:btnClass||'modal-btn-primary'}]});
}

function showConfirm(icon, iconColor, title, message, confirmText, confirmClass, onConfirm) {
    showModal({icon, iconColor, title, message, buttons:[
        {text:'Cancel', class:'modal-btn-cancel'},
        {text:confirmText||'Confirm', class:confirmClass||'modal-btn-primary', onClick:onConfirm}
    ]});
}

/* ════════════════════════════════════
   CONFETTI
   ════════════════════════════════════ */

function launchConfetti() {
    const cs = getComputedStyle(document.documentElement);
    const colors = [cs.getPropertyValue('--t-primary').trim(), cs.getPropertyValue('--t-secondary').trim(), '#f59e0b', '#10b981', '#ef4444', '#3b82f6'];
    for (let i = 0; i < 50; i++) {
        setTimeout(() => {
            const c = document.createElement('div');
            c.className = 'confetti';
            c.style.left = Math.random() * 100 + 'vw';
            c.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            c.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            c.style.width = (Math.random() * 8 + 4) + 'px';
            c.style.height = (Math.random() * 8 + 4) + 'px';
            c.style.animationDuration = (Math.random() * 2 + 2) + 's';
            document.body.appendChild(c);
            setTimeout(() => c.remove(), 4000);
        }, i * 60);
    }
}

/* ════════════════════════════════════
   INIT
   ════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', async () => {
    DB.seed();

    // Sync with Firebase cloud (timeout 3s so page still loads if offline)
    if (typeof initFirebase === 'function') {
        try {
            initFirebase();
            await Promise.race([
                (async () => { await fbPullAll(); await fbSeedCloud(); })(),
                new Promise(r => setTimeout(r, 3000))
            ]);
        } catch (e) { /* continue with local data */ }
    }

    injectLayout();
    lucide.createIcons();
    renderThemeOptions();

    // Close theme picker on outside click
    document.addEventListener('click', e => {
        const wrapper = document.getElementById('theme-picker-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            document.getElementById('theme-dropdown').classList.remove('open');
        }
    });

    // Escape closes modals & theme picker
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
            document.getElementById('theme-dropdown').classList.remove('open');
        }
    });

    // Call page-specific init
    if (typeof pageInit === 'function') pageInit();
});
