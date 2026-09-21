<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50 text-slate-900">

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

    <!-- Tailwind CSS -->
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
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.08), transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(99, 102, 241, 0.05), transparent 50%),
                #f8fafc;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        }

        .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            transform: translateY(-2px);
            border-color: #93c5fd;
            box-shadow: 0 12px 28px -6px rgba(59, 130, 246, 0.12);
        }

        .gradient-text {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .shimmer {
            background: linear-gradient(90deg, rgba(226, 232, 240, 0.4) 0%, rgba(203, 213, 225, 0.7) 50%, rgba(226, 232, 240, 0.4) 100%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        /* Custom sleek light scrollbar for modal & summary containers */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col antialiased text-slate-800 selection:bg-blue-500 selection:text-white">

    <!-- Toast Notifications Container -->
    <div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-md w-full pointer-events-none"></div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center shadow-md shadow-blue-500/20">
                    <i data-lucide="sparkles" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h1 class="font-heading font-bold text-lg tracking-tight flex items-center gap-2">
                        <span class="gradient-text">Nvecta AI</span> Notes
                    </h1>
                    <p class="text-[11px] text-slate-500 font-medium">Laravel 12 REST API & Semantic Vector Intelligence</p>
                </div>
            </div>

            <!-- Header Badges & Actions -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-slate-600 font-medium">Backend Live</span>
                    <span class="text-slate-300">|</span>
                    <span class="text-blue-600 font-medium text-[11px]" id="providerBadge">AI: Active</span>
                </div>

                <a href="/docs/index.html" target="_blank" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 hover:text-slate-900 text-xs font-semibold shadow-xs transition">
                    <i data-lucide="book-open" class="w-3.5 h-3.5 text-blue-600"></i>
                    <span>API Docs</span>
                </a>

                <button onclick="openCreateModal()" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold shadow-md shadow-blue-600/25 transition active:scale-95">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>New Note</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-6">

        <!-- Search & Filter Controls -->
        <section class="glass-panel p-4 rounded-2xl flex flex-col md:flex-row items-center gap-3 shadow-xs">
            <!-- Search Bar -->
            <div class="relative flex-1 w-full">
                <i data-lucide="search" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Search notes (Keyword or Semantic AI Query)..."
                    class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition shadow-xs"
                    onkeyup="handleSearchInput(event)">
                <button id="clearSearchBtn" onclick="clearSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Sorting & Limits -->
            <div class="flex items-center gap-2 w-full md:w-auto">
                <select id="sortSelect" onchange="applyFilters()" class="bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-xs font-medium text-slate-700 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 shadow-xs">
                    <option value="id_desc">Newest First</option>
                    <option value="id_asc">Oldest First</option>
                    <option value="title_asc">Title A-Z</option>
                </select>

                <select id="limitSelect" onchange="applyFilters()" class="bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-xs font-medium text-slate-700 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 shadow-xs">
                    <option value="6">6 per page</option>
                    <option value="9" selected>9 per page</option>
                    <option value="15">15 per page</option>
                </select>
            </div>
        </section>

        <!-- Search Mode Banner (When Search is active) -->
        <div id="searchBanner" class="hidden flex items-center justify-between px-4 py-2.5 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-900">
            <span class="flex items-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4 text-blue-600"></i>
                <span>Showing semantic matches for: <strong id="searchQueryText" class="text-blue-950 font-bold"></strong></span>
            </span>
            <button onclick="clearSearch()" class="text-blue-600 hover:text-blue-800 underline font-medium">Clear search</button>
        </div>

        <!-- Notes Grid / Loading Shimmer / Empty State -->
        <div id="notesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 min-h-[300px]">
            <!-- Dynamic Content loaded via JS -->
        </div>

        <!-- Pagination Controls -->
        <div id="paginationContainer" class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white border border-slate-200 p-4 rounded-2xl text-xs text-slate-600 shadow-xs">
            <div id="paginationInfo" class="font-medium">Showing 0 of 0 notes</div>
            <div id="paginationButtons" class="flex items-center gap-1.5"></div>
        </div>

    </main>

    <!-- Note Detail & AI Summary Modal -->
    <div id="detailModal" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white max-w-2xl w-full max-h-[90vh] rounded-2xl p-6 border border-slate-200 shadow-2xl flex flex-col gap-4 animate-in fade-in zoom-in-95 duration-150">
            <!-- Modal Header (Fixed) -->
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3.5 shrink-0">
                <div>
                    <span id="detailBadge" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 mb-1.5">
                        <i data-lucide="cpu" class="w-3 h-3"></i> Note Details
                    </span>
                    <h2 id="detailTitle" class="font-heading font-bold text-xl text-slate-900 break-words"></h2>
                </div>
                <button onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="flex-1 overflow-y-auto pr-1 flex flex-col gap-4 custom-scrollbar">
                <!-- Full Note Content -->
                <div class="flex flex-col gap-1.5">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Note Content</span>
                    <p id="detailContent" class="text-sm text-slate-800 leading-relaxed whitespace-pre-wrap bg-slate-50 p-4 rounded-xl border border-slate-200 max-h-48 overflow-y-auto break-words select-text"></p>
                </div>

                <!-- AI Summary Section -->
                <div class="flex flex-col gap-2.5 bg-gradient-to-br from-blue-50/70 to-indigo-50/50 p-4 rounded-xl border border-blue-100 shadow-xs">
                    <div class="flex items-center justify-between gap-2 pb-2 border-b border-blue-100">
                        <div class="flex items-center gap-2 flex-wrap">
                            <i data-lucide="bot" class="w-4 h-4 text-blue-600"></i>
                            <span class="text-xs font-semibold text-blue-900">AI Executive Summary</span>
                            <span id="summaryStatusPill" class="hidden px-2 py-0.5 rounded text-[10px] font-semibold bg-white text-slate-600 border border-slate-200 shadow-xs"></span>
                        </div>
                        <div class="flex items-center gap-2.5 shrink-0">
                            <button id="copySummaryBtn" onclick="copySummaryText()" class="hidden text-xs text-slate-700 hover:text-slate-900 px-2.5 py-1 rounded-lg bg-white hover:bg-slate-100 border border-slate-200 flex items-center gap-1 font-medium shadow-xs transition" title="Copy Summary">
                                <i data-lucide="copy" class="w-3.5 h-3.5 text-slate-500"></i>
                                <span>Copy</span>
                            </button>
                            <button id="regenerateSummaryBtn" onclick="triggerSummary(currentDetailId, true)" class="text-xs text-blue-700 hover:text-blue-800 px-2.5 py-1 rounded-lg bg-white hover:bg-blue-50 border border-blue-200 flex items-center gap-1 font-medium shadow-xs transition">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-blue-600"></i>
                                <span>Re-summarize</span>
                            </button>
                        </div>
                    </div>
                    <!-- Dedicated scrollable container for long AI summaries -->
                    <div id="detailSummaryContainer" class="text-xs text-slate-800 leading-relaxed min-h-[44px] max-h-56 overflow-y-auto pr-1 whitespace-pre-wrap select-text">
                        <span class="text-slate-400 italic">No summary generated yet. Click Generate Summary below.</span>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons (Fixed) -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs shrink-0">
                <span id="detailDates" class="text-slate-400"></span>
                <div class="flex items-center gap-2">
                    <button onclick="editFromDetail()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium flex items-center gap-1.5 transition">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit
                    </button>
                    <button onclick="closeDetailModal()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold shadow-md shadow-blue-600/20 transition">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create / Edit Note Modal -->
    <div id="noteModal" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white max-w-lg w-full rounded-2xl p-6 border border-slate-200 shadow-2xl flex flex-col gap-4 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 id="modalTitle" class="font-heading font-bold text-lg text-slate-900">Create New Note</h3>
                <button onclick="closeNoteModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="noteForm" onsubmit="handleNoteSubmit(event)" class="flex flex-col gap-4">
                <input type="hidden" id="formNoteId">

                <div class="flex flex-col gap-1.5">
                    <label for="formTitle" class="text-xs font-semibold text-slate-700">Title <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="formTitle"
                        placeholder="e.g., Understanding System Design and AI"
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition shadow-xs"
                        required>
                    <span id="formTitleError" class="text-[11px] text-rose-500 hidden"></span>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="formContent" class="text-xs font-semibold text-slate-700">Content <span class="text-rose-500">*</span></label>
                    <textarea
                        id="formContent"
                        rows="6"
                        placeholder="Type your note content here..."
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition resize-none shadow-xs"
                        required></textarea>
                    <span id="formContentError" class="text-[11px] text-rose-500 hidden"></span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeNoteModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        Cancel
                    </button>
                    <button type="submit" id="saveNoteBtn" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-md shadow-blue-600/25 flex items-center gap-1.5 transition">
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
                const res = await fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
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
                const date = new Date(note.created_at).toLocaleDateString(undefined, {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
                const hasSummary = !!note.summary;
                const hasVector = !!note.has_embedding;

                return `
                <div class="glass-card rounded-2xl p-5 flex flex-col justify-between gap-4 group">
                    <div class="flex flex-col gap-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[11px] font-mono font-semibold text-slate-400">#${note.id}</span>
                                ${note.similarity_percentage !== undefined ? `
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200" title="Cosine Similarity Score: ${note.similarity_score}">
                                        <i data-lucide="sparkles" class="w-3 h-3 text-indigo-600"></i> ${note.similarity_percentage}% Match
                                    </span>
                                ` : ''}
                            </div>
                            <div class="flex items-center gap-1.5">
                                ${hasVector ? `
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200" title="Vector Embedding Active in MySQL">
                                        <i data-lucide="zap" class="w-3 h-3"></i> AI Indexed
                                    </span>
                                ` : ''}
                                ${hasSummary ? `
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200" title="AI Summary Cached">
                                        <i data-lucide="bot" class="w-3 h-3"></i> Summary
                                    </span>
                                ` : ''}
                            </div>
                        </div>

                        <h3 class="font-heading font-bold text-base text-slate-900 group-hover:text-blue-600 transition line-clamp-1 cursor-pointer" onclick="openDetailModal(${note.id})">
                            ${escapeHtml(note.title)}
                        </h3>

                        <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                            ${escapeHtml(note.content)}
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span class="text-[11px] text-slate-400">${date}</span>
                        <div class="flex items-center gap-1">
                            <button onclick="openDetailModalWithSummary(${note.id})" class="p-1.5 rounded-lg hover:bg-blue-50 text-blue-600 hover:text-blue-700 transition" title="Generate/View AI Summary">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                            </button>
                            <button onclick="openDetailModal(${note.id})" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition" title="View Note Details">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                            <button onclick="openEditModal(${note.id})" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition" title="Edit Note">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </button>
                            <button onclick="deleteNote(${note.id})" class="p-1.5 rounded-lg hover:bg-rose-50 text-rose-500 hover:text-rose-600 transition" title="Delete Note">
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
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-center bg-white border border-slate-200 rounded-2xl p-8 shadow-xs">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600 mb-4">
                        <i data-lucide="folder-open" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-slate-900 mb-1">No Notes Found</h3>
                    <p class="text-xs text-slate-500 max-w-sm mb-6">${message}</p>
                    <button onclick="openCreateModal()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold flex items-center gap-2 shadow-md shadow-blue-600/25 transition">
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
                <button onclick="loadNotes(${p.current_page - 1})" ${p.current_page <= 1 ? 'disabled class="opacity-40 cursor-not-allowed"' : 'class="hover:bg-slate-100"'} class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 transition">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                </button>
            `;

            for (let i = 1; i <= p.total_pages; i++) {
                if (i === 1 || i === p.total_pages || (i >= p.current_page - 1 && i <= p.current_page + 1)) {
                    btns += `
                        <button onclick="loadNotes(${i})" class="px-3 py-1.5 rounded-lg border ${i === p.current_page ? 'bg-blue-600 border-blue-600 text-white font-bold shadow-xs' : 'border-slate-200 hover:bg-slate-100 text-slate-700'} transition text-xs">
                            ${i}
                        </button>
                    `;
                } else if (i === p.current_page - 2 || i === p.current_page + 2) {
                    btns += `<span class="px-1 text-slate-400">...</span>`;
                }
            }

            btns += `
                <button onclick="loadNotes(${p.current_page + 1})" ${!p.has_more_pages ? 'disabled class="opacity-40 cursor-not-allowed"' : 'class="hover:bg-slate-100"'} class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 transition">
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

        // Semantic Vector Search API Call
        async function executeSearch(query) {
            searchQuery = query;
            document.getElementById('searchBanner').classList.remove('hidden');
            document.getElementById('searchQueryText').innerText = query;
            renderLoadingSkeleton();

            try {
                const res = await fetch(`/api/notes/search?q=${encodeURIComponent(query)}&limit=15`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();

                if (json.success) {
                    const matchedNotes = json.data.notes || [];
                    renderNotesGrid(matchedNotes);

                    const info = document.getElementById('paginationInfo');
                    info.innerText = `Found ${json.data.total_matches} semantic match(es) for "${query}"`;
                    document.getElementById('paginationButtons').innerHTML = '';
                } else {
                    renderEmptyState(json.message || 'No semantic matches found.');
                }
            } catch (err) {
                console.error(err);
                renderEmptyState('Failed to execute semantic search.');
            }
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
            let note = allNotes.find(n => n.id === id);

            // If not found in current page collection, fetch from API
            if (!note) {
                try {
                    const res = await fetch(`/api/notes/${id}`, { headers: { 'Accept': 'application/json' } });
                    const json = await res.json();
                    if (json.success) {
                        note = json.data;
                    }
                } catch (e) {
                    console.error(e);
                }
            }

            if (!note) {
                showToast('Note not found', 'error');
                return;
            }

            document.getElementById('detailTitle').innerText = note.title;
            document.getElementById('detailContent').innerText = note.content;
            document.getElementById('detailDates').innerText = `Created: ${new Date(note.created_at).toLocaleString()}`;

            const summaryContainer = document.getElementById('detailSummaryContainer');
            const summaryPill = document.getElementById('summaryStatusPill');
            const copyBtn = document.getElementById('copySummaryBtn');

            if (note.summary) {
                activeSummaryText = note.summary;
                summaryContainer.innerHTML = `<p class="text-slate-800 leading-relaxed">${escapeHtml(note.summary)}</p>`;
                summaryPill.classList.remove('hidden');
                summaryPill.innerText = 'Cached';
                summaryPill.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
                copyBtn.classList.remove('hidden');
            } else {
                activeSummaryText = '';
                summaryContainer.innerHTML = `<div class="flex items-center justify-between gap-3 w-full py-1">
                    <span class="text-slate-400 italic">No summary generated yet.</span>
                    <button onclick="triggerSummary(${id})" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold flex items-center gap-1.5 shadow-xs transition">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Generate AI Summary
                    </button>
                </div>`;
                summaryPill.classList.add('hidden');
                copyBtn.classList.add('hidden');
            }

            document.getElementById('detailModal').classList.remove('hidden');
            lucide.createIcons();
            return note;
        }

        async function openDetailModalWithSummary(id) {
            const note = await openDetailModal(id);
            if (note && !note.summary) {
                triggerSummary(id);
            }
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }

        let activeSummaryText = '';

        function copySummaryText() {
            if (!activeSummaryText) return;
            navigator.clipboard.writeText(activeSummaryText).then(() => {
                showToast('AI Summary copied to clipboard!', 'success');
            }).catch(() => {
                showToast('Failed to copy to clipboard', 'error');
            });
        }

        // Trigger AI Summary API
        async function triggerSummary(id, force = false) {
            currentDetailId = id;
            // Ensure modal is open
            if (document.getElementById('detailModal').classList.contains('hidden')) {
                await openDetailModal(id);
            }

            const summaryContainer = document.getElementById('detailSummaryContainer');
            const summaryPill = document.getElementById('summaryStatusPill');
            const copyBtn = document.getElementById('copySummaryBtn');

            summaryContainer.innerHTML = `<div class="flex items-center gap-2 py-2 text-blue-600 font-medium text-xs">
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                <span>Analyzing and generating executive AI summary for note #${id}...</span>
            </div>`;
            copyBtn.classList.add('hidden');
            lucide.createIcons();

            try {
                const url = `/api/notes/${id}/summary${force ? '?force=true' : ''}`;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();

                if (json.success) {
                    const sum = json.data.summary;
                    activeSummaryText = sum;
                    summaryContainer.innerHTML = `<p class="text-slate-800 leading-relaxed">${escapeHtml(sum)}</p>`;
                    summaryPill.classList.remove('hidden');
                    summaryPill.innerText = json.data.cached ? 'From Cache' : 'Fresh AI';
                    summaryPill.className = json.data.cached ?
                        'px-2 py-0.5 rounded text-[10px] font-semibold bg-white text-slate-600 border border-slate-200 shadow-xs' :
                        'px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-800 border border-blue-200';
                    copyBtn.classList.remove('hidden');

                    // Update note in local state and DOM card badges
                    const local = allNotes.find(n => n.id === id);
                    if (local) local.summary = sum;

                    showToast(json.message, 'success');
                } else {
                    summaryContainer.innerHTML = `<div class="flex items-center justify-between gap-2 py-1 text-rose-600 text-xs">
                        <span>Failed: ${escapeHtml(json.message)}</span>
                        <button onclick="triggerSummary(${id}, true)" class="px-2.5 py-1 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded font-medium">Retry</button>
                    </div>`;
                    showToast(json.message || 'Failed to generate summary', 'error');
                }
            } catch (err) {
                summaryContainer.innerHTML = `<div class="flex items-center justify-between gap-2 py-1 text-rose-600 text-xs">
                    <span>Error connecting to AI service.</span>
                    <button onclick="triggerSummary(${id}, true)" class="px-2.5 py-1 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded font-medium">Retry</button>
                </div>`;
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
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title,
                        content
                    })
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
                    headers: {
                        'Accept': 'application/json'
                    }
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

            toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl border text-xs font-semibold transition-all duration-200 transform translate-y-2 opacity-0 ${
                isSuccess ? 'bg-white border-emerald-300 text-emerald-800' :
                isError ? 'bg-white border-rose-300 text-rose-800' :
                'bg-white border-slate-300 text-slate-800'
            }`;

            toast.innerHTML = `
                <i data-lucide="${isSuccess ? 'check-circle' : isError ? 'alert-circle' : 'info'}" class="w-4 h-4 shrink-0 ${isSuccess ? 'text-emerald-600' : isError ? 'text-rose-600' : 'text-blue-600'}"></i>
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
            } [m]));
        }
    </script>
</body>

</html>