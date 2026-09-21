<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Notes Management System | Laravel & Semantic AI</title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Tailwind CSS (via CDN for standalone instant UI) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.15), transparent 50%),
                        radial-gradient(circle at 100% 100%, rgba(139, 92, 246, 0.1), transparent 50%),
                        #090d16;
        }
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            transform: translateY(-2px);
            border-color: rgba(99, 102, 241, 0.35);
            box-shadow: 0 12px 30px -10px rgba(99, 102, 241, 0.2);
        }
        .gradient-text {
            background: linear-gradient(135deg, #818cf8 0%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .shimmer {
            background: linear-gradient(90deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.03) 100%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Toast Notifications Container -->
    <div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-md w-full pointer-events-none"></div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 glass-panel border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                    <i data-lucide="sparkles" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h1 class="font-heading font-bold text-lg tracking-tight flex items-center gap-2">
                        <span class="gradient-text">Nvecta AI</span> Notes
                    </h1>
                    <p class="text-[11px] text-slate-400">Laravel 12 REST API & Vector Embeddings</p>
                </div>
            </div>

            <!-- Header Badges & Actions -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900/80 border border-slate-700/60 text-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-slate-300 font-medium">Backend Live</span>
                    <span class="text-slate-500">|</span>
                    <span class="text-indigo-400 font-mono text-[11px]" id="providerBadge">AI: Active</span>
                </div>

                <button onclick="openCreateModal()" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 transition active:scale-95">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>New Note</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-6">

        <!-- Search & Filter Controls -->
        <section class="glass-panel p-4 rounded-2xl flex flex-col md:flex-row items-center gap-3 shadow-xl">
            <!-- Search Bar -->
            <div class="relative flex-1 w-full">
                <i data-lucide="search" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Search notes (Keyword or Semantic AI Query)..." 
                    class="w-full pl-10 pr-10 py-2.5 bg-slate-900/90 border border-slate-700/80 rounded-xl text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"
                    onkeyup="handleSearchInput(event)"
                >
                <button id="clearSearchBtn" onclick="clearSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Sorting & Limits -->
            <div class="flex items-center gap-2 w-full md:w-auto">
                <select id="sortSelect" onchange="applyFilters()" class="bg-slate-900/90 border border-slate-700/80 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="id_desc">Newest First</option>
                    <option value="id_asc">Oldest First</option>
                    <option value="title_asc">Title A-Z</option>
                </select>

                <select id="limitSelect" onchange="applyFilters()" class="bg-slate-900/90 border border-slate-700/80 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="6">6 per page</option>
                    <option value="9" selected>9 per page</option>
                    <option value="15">15 per page</option>
                </select>
            </div>
        </section>

        <!-- Search Mode Banner (When Search is active) -->
        <div id="searchBanner" class="hidden flex items-center justify-between px-4 py-2 rounded-xl bg-indigo-950/50 border border-indigo-500/30 text-xs text-indigo-200">
            <span class="flex items-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4 text-indigo-400"></i>
                <span>Showing results for query: <strong id="searchQueryText" class="text-white"></strong></span>
            </span>
            <button onclick="clearSearch()" class="text-indigo-300 hover:text-white underline font-medium">Clear search</button>
        </div>

        <!-- Notes Grid / Loading Shimmer / Empty State -->
        <div id="notesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 min-h-[300px]">
            <!-- Dynamic Content loaded via JS -->
        </div>

        <!-- Pagination Controls -->
        <div id="paginationContainer" class="flex flex-col sm:flex-row items-center justify-between gap-4 glass-panel p-4 rounded-2xl text-xs text-slate-400">
            <div id="paginationInfo">Showing 0 of 0 notes</div>
            <div id="paginationButtons" class="flex items-center gap-1.5"></div>
        </div>

    </main>

    <!-- Note Detail & AI Summary Modal -->
    <div id="detailModal" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass-panel bg-slate-900/95 max-w-2xl w-full rounded-2xl p-6 border border-slate-700 shadow-2xl flex flex-col gap-4 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-start justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <span id="detailBadge" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 mb-1.5">
                        <i data-lucide="cpu" class="w-3 h-3"></i> Note Details
                    </span>
                    <h2 id="detailTitle" class="font-heading font-bold text-xl text-white"></h2>
                </div>
                <button onclick="closeDetailModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Full Note Content -->
            <div class="flex flex-col gap-1.5">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Note Content</span>
                <p id="detailContent" class="text-sm text-slate-200 leading-relaxed whitespace-pre-wrap bg-slate-950/60 p-4 rounded-xl border border-slate-800/80 max-h-60 overflow-y-auto"></p>
            </div>

            <!-- AI Summary Section -->
            <div class="flex flex-col gap-2 bg-gradient-to-br from-indigo-950/40 to-slate-950/70 p-4 rounded-xl border border-indigo-500/20">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="bot" class="w-4 h-4 text-indigo-400"></i>
                        <span class="text-xs font-semibold text-indigo-200">AI-Generated Executive Summary</span>
                        <span id="summaryStatusPill" class="hidden px-2 py-0.5 rounded text-[10px] font-medium bg-slate-800 text-slate-300"></span>
                    </div>
                    <button id="regenerateSummaryBtn" onclick="triggerSummary(currentDetailId, true)" class="text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1 font-medium transition">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        <span>Re-summarize</span>
                    </button>
                </div>
                <div id="detailSummaryContainer" class="text-xs text-slate-300 leading-normal min-h-[38px] flex items-center">
                    <span class="text-slate-500 italic">No summary generated yet. Click Generate Summary below.</span>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-800 text-xs">
                <span id="detailDates" class="text-slate-500"></span>
                <div class="flex items-center gap-2">
                    <button onclick="editFromDetail()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-medium flex items-center gap-1.5">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit
                    </button>
                    <button onclick="closeDetailModal()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create / Edit Note Modal -->
    <div id="noteModal" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass-panel bg-slate-900/95 max-w-lg w-full rounded-2xl p-6 border border-slate-700 shadow-2xl flex flex-col gap-4 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 id="modalTitle" class="font-heading font-bold text-lg text-white">Create New Note</h3>
                <button onclick="closeNoteModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="noteForm" onsubmit="handleNoteSubmit(event)" class="flex flex-col gap-4">
                <input type="hidden" id="formNoteId">
                
                <div class="flex flex-col gap-1.5">
                    <label for="formTitle" class="text-xs font-semibold text-slate-300">Title <span class="text-rose-400">*</span></label>
                    <input 
                        type="text" 
                        id="formTitle" 
                        placeholder="e.g., Understanding System Design and AI" 
                        class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"
                        required
                    >
                    <span id="formTitleError" class="text-[11px] text-rose-400 hidden"></span>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="formContent" class="text-xs font-semibold text-slate-300">Content <span class="text-rose-400">*</span></label>
                    <textarea 
                        id="formContent" 
                        rows="6" 
                        placeholder="Type your note content here..." 
                        class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition resize-none"
                        required
                    ></textarea>
                    <span id="formContentError" class="text-[11px] text-rose-400 hidden"></span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" onclick="closeNoteModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition">
                        Cancel
                    </button>
                    <button type="submit" id="saveNoteBtn" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 flex items-center gap-1.5 transition">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span id="saveBtnText">Save Note</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Frontend State & Logic -->
    <script>
        // State
        let currentPage = 1;
        let currentLimit = 9;
        let currentSort = 'id';
        let currentOrder = 'desc';
        let searchQuery = '';
        let currentDetailId = null;
        let allNotes = [];

        // Initialize Lucide Icons & Initial Load
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            loadNotes();
        });

        // Fetch Notes from Backend API
        async function loadNotes(page = 1) {
            currentPage = page;
            renderLoadingSkeleton();

            try {
                const url = `/api/notes?page=${currentPage}&limit=${currentLimit}&sort_by=${currentSort}&order=${currentOrder}`;
                const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                const json = await res.json();

                if (json.success) {
                    allNotes = json.data.notes || [];
                    renderNotesGrid(allNotes);
                    renderPagination(json.data.pagination);
                } else {
                    showToast('Failed to load notes', 'error');
                }
            } catch (err) {
                console.error(err);
                renderEmptyState('Failed to connect to backend server. Make sure php artisan serve is running.');
            }
        }

        // Render Notes Grid
        function renderNotesGrid(notes) {
            const container = document.getElementById('notesContainer');
            if (!notes || notes.length === 0) {
                renderEmptyState();
                return;
            }

            container.innerHTML = notes.map(note => {
                const date = new Date(note.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
                const hasSummary = !!note.summary;
                const hasVector = !!note.has_embedding;

                return `
                <div class="glass-card rounded-2xl p-5 flex flex-col justify-between gap-4 group">
                    <div class="flex flex-col gap-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[11px] font-mono text-slate-400">#${note.id}</span>
                            <div class="flex items-center gap-1.5">
                                ${hasVector ? `
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20" title="Vector Embedding Active in MySQL">
                                        <i data-lucide="zap" class="w-3 h-3"></i> AI Indexed
                                    </span>
                                ` : ''}
                                ${hasSummary ? `
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20" title="AI Summary Cached">
                                        <i data-lucide="bot" class="w-3 h-3"></i> Summary
                                    </span>
                                ` : ''}
                            </div>
                        </div>

                        <h3 class="font-heading font-bold text-base text-slate-100 group-hover:text-indigo-300 transition line-clamp-1 cursor-pointer" onclick="openDetailModal(${note.id})">
                            ${escapeHtml(note.title)}
                        </h3>

                        <p class="text-xs text-slate-300 line-clamp-3 leading-relaxed">
                            ${escapeHtml(note.content)}
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                        <span class="text-[11px] text-slate-500">${date}</span>
                        <div class="flex items-center gap-1">
                            <button onclick="triggerSummary(${note.id})" class="p-1.5 rounded-lg hover:bg-indigo-600/20 text-indigo-400 hover:text-indigo-300 transition" title="Generate/View AI Summary">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                            </button>
                            <button onclick="openDetailModal(${note.id})" class="p-1.5 rounded-lg hover:bg-slate-700/60 text-slate-300 hover:text-white transition" title="View Note Details">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                            <button onclick="openEditModal(${note.id})" class="p-1.5 rounded-lg hover:bg-slate-700/60 text-slate-300 hover:text-white transition" title="Edit Note">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </button>
                            <button onclick="deleteNote(${note.id})" class="p-1.5 rounded-lg hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 transition" title="Delete Note">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
                `;
            }).join('');

            lucide.createIcons();
        }

        // Render Skeleton Loading
        function renderLoadingSkeleton() {
            const container = document.getElementById('notesContainer');
            container.innerHTML = Array(6).fill(0).map(() => `
                <div class="glass-card rounded-2xl p-5 flex flex-col gap-4">
                    <div class="h-4 w-1/4 rounded shimmer"></div>
                    <div class="h-6 w-3/4 rounded shimmer"></div>
                    <div class="space-y-2">
                        <div class="h-3 w-full rounded shimmer"></div>
                        <div class="h-3 w-5/6 rounded shimmer"></div>
                        <div class="h-3 w-4/6 rounded shimmer"></div>
                    </div>
                    <div class="h-5 w-full rounded shimmer mt-4"></div>
                </div>
            `).join('');
        }

        // Render Empty State
        function renderEmptyState(message = 'No notes found. Create your first note to get started!') {
            const container = document.getElementById('notesContainer');
            container.innerHTML = `
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-center glass-panel rounded-2xl p-8">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mb-4">
                        <i data-lucide="folder-open" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-white mb-1">No Notes Found</h3>
                    <p class="text-xs text-slate-400 max-w-sm mb-6">${message}</p>
                    <button onclick="openCreateModal()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold flex items-center gap-2 shadow-lg shadow-indigo-600/30">
                        <i data-lucide="plus" class="w-4 h-4"></i> Create Note
                    </button>
                </div>
            `;
            lucide.createIcons();
        }

        // Render Pagination Controls
        function renderPagination(p) {
            const info = document.getElementById('paginationInfo');
            const buttons = document.getElementById('paginationButtons');

            if (!p || p.total === 0) {
                info.innerText = 'Showing 0 notes';
                buttons.innerHTML = '';
                return;
            }

            const from = ((p.current_page - 1) * p.per_page) + 1;
            const to = Math.min(p.current_page * p.per_page, p.total);
            info.innerText = `Showing ${from}-${to} of ${p.total} notes`;

            let btns = `
                <button onclick="loadNotes(${p.current_page - 1})" ${p.current_page <= 1 ? 'disabled class="opacity-40 cursor-not-allowed"' : 'class="hover:bg-slate-800"'} class="px-2.5 py-1.5 rounded-lg border border-slate-700 text-slate-300 transition">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                </button>
            `;

            for (let i = 1; i <= p.total_pages; i++) {
                if (i === 1 || i === p.total_pages || (i >= p.current_page - 1 && i <= p.current_page + 1)) {
                    btns += `
                        <button onclick="loadNotes(${i})" class="px-3 py-1.5 rounded-lg border ${i === p.current_page ? 'bg-indigo-600 border-indigo-500 text-white font-bold' : 'border-slate-700 hover:bg-slate-800 text-slate-300'} transition">
                            ${i}
                        </button>
                    `;
                } else if (i === p.current_page - 2 || i === p.current_page + 2) {
                    btns += `<span class="px-1 text-slate-600">...</span>`;
                }
            }

            btns += `
                <button onclick="loadNotes(${p.current_page + 1})" ${!p.has_more_pages ? 'disabled class="opacity-40 cursor-not-allowed"' : 'class="hover:bg-slate-800"'} class="px-2.5 py-1.5 rounded-lg border border-slate-700 text-slate-300 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </button>
            `;

            buttons.innerHTML = btns;
            lucide.createIcons();
        }

        // Apply Filters (Sort & Limit)
        function applyFilters() {
            const sortVal = document.getElementById('sortSelect').value;
            const [sort, order] = sortVal.split('_');
            currentSort = sort;
            currentOrder = order;
            currentLimit = parseInt(document.getElementById('limitSelect').value);
            loadNotes(1);
        }

        // Search Input Handling
        let searchTimeout;
        function handleSearchInput(e) {
            const val = e.target.value.trim();
            const clearBtn = document.getElementById('clearSearchBtn');
            clearBtn.classList.toggle('hidden', val.length === 0);

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (val.length === 0) {
                    clearSearch();
                } else {
                    executeSearch(val);
                }
            }, 300);
        }

        // Client & Server Search
        async function executeSearch(query) {
            searchQuery = query;
            document.getElementById('searchBanner').classList.remove('hidden');
            document.getElementById('searchQueryText').innerText = query;

            // In Phase 7 semantic search endpoint GET /api/notes/search?q=... will be called directly.
            // For now, search matches dynamically across current and loaded notes
            const filtered = allNotes.filter(n => 
                n.title.toLowerCase().includes(query.toLowerCase()) || 
                n.content.toLowerCase().includes(query.toLowerCase())
            );
            renderNotesGrid(filtered);
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            document.getElementById('clearSearchBtn').classList.add('hidden');
            document.getElementById('searchBanner').classList.add('hidden');
            searchQuery = '';
            loadNotes(1);
        }

        // Note Details & AI Summary Modal
        async function openDetailModal(id) {
            currentDetailId = id;
            const note = allNotes.find(n => n.id === id);
            if (!note) return;

            document.getElementById('detailTitle').innerText = note.title;
            document.getElementById('detailContent').innerText = note.content;
            document.getElementById('detailDates').innerText = `Created: ${new Date(note.created_at).toLocaleString()}`;
            
            const summaryContainer = document.getElementById('detailSummaryContainer');
            const summaryPill = document.getElementById('summaryStatusPill');

            if (note.summary) {
                summaryContainer.innerHTML = `<p class="text-slate-200">${escapeHtml(note.summary)}</p>`;
                summaryPill.classList.remove('hidden');
                summaryPill.innerText = 'Cached';
                summaryPill.className = 'px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
            } else {
                summaryContainer.innerHTML = `<button onclick="triggerSummary(${id})" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium flex items-center gap-1.5 transition"><i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Generate AI Summary</button>`;
                summaryPill.classList.add('hidden');
                lucide.createIcons();
            }

            document.getElementById('detailModal').classList.remove('hidden');
            lucide.createIcons();
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }

        // Trigger AI Summary API
        async function triggerSummary(id, force = false) {
            const summaryContainer = document.getElementById('detailSummaryContainer');
            const summaryPill = document.getElementById('summaryStatusPill');

            summaryContainer.innerHTML = `<span class="flex items-center gap-2 text-indigo-300"><i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> AI is summarizing note #${id}...</span>`;
            lucide.createIcons();

            try {
                const url = `/api/notes/${id}/summary${force ? '?force=true' : ''}`;
                const res = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json' } });
                const json = await res.json();

                if (json.success) {
                    const sum = json.data.summary;
                    summaryContainer.innerHTML = `<p class="text-slate-200">${escapeHtml(sum)}</p>`;
                    summaryPill.classList.remove('hidden');
                    summaryPill.innerText = json.data.cached ? 'From Cache' : 'Fresh AI';
                    summaryPill.className = json.data.cached 
                        ? 'px-2 py-0.5 rounded text-[10px] font-medium bg-slate-800 text-slate-300' 
                        : 'px-2 py-0.5 rounded text-[10px] font-medium bg-indigo-500/20 text-indigo-300 border border-indigo-500/30';
                    
                    // Update note in local state
                    const local = allNotes.find(n => n.id === id);
                    if (local) local.summary = sum;

                    showToast(json.message, 'success');
                } else {
                    summaryContainer.innerHTML = `<span class="text-rose-400">Failed: ${json.message}</span>`;
                    showToast(json.message, 'error');
                }
            } catch (err) {
                summaryContainer.innerHTML = `<span class="text-rose-400">Error connecting to AI service.</span>`;
                showToast('AI Service Error', 'error');
            }
            lucide.createIcons();
        }

        // Create / Edit Modal Functions
        function openCreateModal() {
            document.getElementById('modalTitle').innerText = 'Create New Note';
            document.getElementById('saveBtnText').innerText = 'Create Note';
            document.getElementById('formNoteId').value = '';
            document.getElementById('formTitle').value = '';
            document.getElementById('formContent').value = '';
            clearFormErrors();
            document.getElementById('noteModal').classList.remove('hidden');
        }

        function openEditModal(id) {
            const note = allNotes.find(n => n.id === id);
            if (!note) return;

            document.getElementById('modalTitle').innerText = `Edit Note #${id}`;
            document.getElementById('saveBtnText').innerText = 'Update Note';
            document.getElementById('formNoteId').value = note.id;
            document.getElementById('formTitle').value = note.title;
            document.getElementById('formContent').value = note.content;
            clearFormErrors();
            document.getElementById('noteModal').classList.remove('hidden');
        }

        function editFromDetail() {
            closeDetailModal();
            if (currentDetailId) openEditModal(currentDetailId);
        }

        function closeNoteModal() {
            document.getElementById('noteModal').classList.add('hidden');
        }

        function clearFormErrors() {
            document.getElementById('formTitleError').classList.add('hidden');
            document.getElementById('formContentError').classList.add('hidden');
        }

        // Form Submission (Create or Update)
        async function handleNoteSubmit(e) {
            e.preventDefault();
            clearFormErrors();

            const id = document.getElementById('formNoteId').value;
            const title = document.getElementById('formTitle').value.trim();
            const content = document.getElementById('formContent').value.trim();
            const isEdit = !!id;

            const url = isEdit ? `/api/notes/${id}` : '/api/notes';
            const method = isEdit ? 'PUT' : 'POST';

            const btn = document.getElementById('saveNoteBtn');
            btn.disabled = true;
            btn.classList.add('opacity-70');

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ title, content })
                });
                const json = await res.json();

                if (json.success) {
                    showToast(json.message, 'success');
                    closeNoteModal();
                    loadNotes(currentPage);
                } else if (res.status === 422 && json.errors) {
                    if (json.errors.title) {
                        const el = document.getElementById('formTitleError');
                        el.innerText = json.errors.title[0];
                        el.classList.remove('hidden');
                    }
                    if (json.errors.content) {
                        const el = document.getElementById('formContentError');
                        el.innerText = json.errors.content[0];
                        el.classList.remove('hidden');
                    }
                } else {
                    showToast(json.message || 'Action failed', 'error');
                }
            } catch (err) {
                showToast('Network error while saving note', 'error');
            } finally {
                btn.disabled = false;
                btn.classList.remove('opacity-70');
            }
        }

        // Delete Note
        async function deleteNote(id) {
            if (!confirm(`Are you sure you want to delete Note #${id}?`)) return;

            try {
                const res = await fetch(`/api/notes/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();

                if (json.success) {
                    showToast('Note deleted successfully', 'success');
                    loadNotes(currentPage);
                } else {
                    showToast(json.message || 'Failed to delete note', 'error');
                }
            } catch (err) {
                showToast('Error deleting note', 'error');
            }
        }

        // Toast Notification System
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            
            const isSuccess = type === 'success';
            const isError = type === 'error';

            toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl border text-xs font-medium transition-all duration-200 transform translate-y-2 opacity-0 ${
                isSuccess ? 'bg-emerald-950/90 border-emerald-500/40 text-emerald-200' :
                isError ? 'bg-rose-950/90 border-rose-500/40 text-rose-200' :
                'bg-slate-900/90 border-slate-700 text-slate-200'
            }`;

            toast.innerHTML = `
                <i data-lucide="${isSuccess ? 'check-circle' : isError ? 'alert-circle' : 'info'}" class="w-4 h-4 shrink-0"></i>
                <span class="flex-1">${escapeHtml(message)}</span>
            `;

            container.appendChild(toast);
            lucide.createIcons();

            // Animate In
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            });

            // Animate Out after 3.5s
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 200);
            }, 3500);
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[m]));
        }
    </script>
</body>
</html>
