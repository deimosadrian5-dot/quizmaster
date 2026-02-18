/**
 * QuizMaster — Firebase Cloud Sync
 * Syncs quizzes & attempts to Firebase Realtime Database so all visitors share the same data.
 *
 * SETUP: Replace the config below with your Firebase project config.
 * 1. Go to https://console.firebase.google.com
 * 2. Create a project (disable Google Analytics)
 * 3. Go to Build → Realtime Database → Create Database → Start in TEST mode
 * 4. Go to Project Settings (gear icon) → General → scroll to "Your apps" → click Web (</>)
 * 5. Register app, copy the firebaseConfig object, paste below
 */

const FIREBASE_CONFIG = {
    apiKey: "AIzaSyAEDIeiyyXaxgFyN_iNgVmOFCvDCIPfnak",
    authDomain: "quizmaster-37381.firebaseapp.com",
    databaseURL: "https://quizmaster-37381-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "quizmaster-37381",
    storageBucket: "quizmaster-37381.firebasestorage.app",
    messagingSenderId: "69959216753",
    appId: "1:69959216753:web:13a8bdbba9d7f9567035d0"
};

let _fbReady = false;
let _fbDB = null;

function initFirebase() {
    if (!FIREBASE_CONFIG.apiKey || FIREBASE_CONFIG.apiKey.includes('YOUR_')) return;
    try {
        firebase.initializeApp(FIREBASE_CONFIG);
        _fbDB = firebase.database();
        _fbReady = true;
    } catch (e) {
        console.warn('Firebase init failed:', e);
    }
}

function isFirebaseReady() { return _fbReady; }

/* ── Push to cloud ── */
async function fbSaveQuiz(quiz) {
    if (!_fbReady) return;
    try { await _fbDB.ref('quizzes/' + quiz.id).set(quiz); } catch (e) {}
}

async function fbSaveAttempt(attempt) {
    if (!_fbReady) return;
    try { await _fbDB.ref('attempts/' + attempt.id).set(attempt); } catch (e) {}
}

async function fbDeleteQuiz(id) {
    if (!_fbReady) return;
    try {
        await _fbDB.ref('quizzes/' + id).remove();
        const snap = await _fbDB.ref('attempts').orderByChild('quiz_id').equalTo(id).once('value');
        const updates = {};
        snap.forEach(child => { updates[child.key] = null; });
        if (Object.keys(updates).length > 0) {
            await _fbDB.ref('attempts').update(updates);
        }
    } catch (e) {}
}

/* ── Pull from cloud & merge into localStorage ── */
async function fbPullAll() {
    if (!_fbReady) return;
    try {
        const [qSnap, aSnap] = await Promise.all([
            _fbDB.ref('quizzes').once('value'),
            _fbDB.ref('attempts').once('value')
        ]);

        // Merge cloud quizzes
        const cloudQuizzes = [];
        qSnap.forEach(c => cloudQuizzes.push(c.val()));
        if (cloudQuizzes.length > 0) {
            const local = DB.getQuizzes();
            const localIds = new Set(local.map(q => q.id));
            cloudQuizzes.forEach(cq => {
                if (!localIds.has(cq.id)) local.push(cq);
            });
            DB._set('qm_quizzes', local);
        }

        // Merge cloud attempts
        const cloudAttempts = [];
        aSnap.forEach(c => cloudAttempts.push(c.val()));
        if (cloudAttempts.length > 0) {
            const local = DB.getAttempts();
            const localIds = new Set(local.map(a => a.id));
            cloudAttempts.forEach(ca => {
                if (!localIds.has(ca.id)) local.push(ca);
            });
            DB._set('qm_attempts', local);
        }
    } catch (e) {
        console.warn('Firebase pull failed:', e);
    }
}

/* ── Seed cloud with initial quizzes (first visitor only) ── */
async function fbSeedCloud() {
    if (!_fbReady) return;
    try {
        const snap = await _fbDB.ref('quizzes/1').once('value');
        if (snap.exists()) return; // Cloud already seeded
        const quizzes = DB.getQuizzes();
        for (const q of quizzes) {
            await _fbDB.ref('quizzes/' + q.id).set(q);
        }
    } catch (e) {}
}
