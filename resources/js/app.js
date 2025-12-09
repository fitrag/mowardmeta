import './bootstrap';

// Alpine.js Components

// Metadata Image Uploader Component
window.metadataImageUploader = function () {
    return {
        isDragging: false,
        isCompressing: false,
        compressProgress: 0,
        compressTotal: 0,
        localImages: [],
        db: null,
        currentSessionId: null,

        async initDB() {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open('MetadataGeneratorDB', 2); // Upgrade version

                request.onerror = () => reject(request.error);

                request.onsuccess = () => {
                    this.db = request.result;
                    this.loadImagesFromDB();
                    resolve(this.db);
                };

                request.onupgradeneeded = (event) => {
                    const db = event.target.result;
                    if (!db.objectStoreNames.contains('images')) {
                        db.createObjectStore('images', { keyPath: 'id', autoIncrement: true });
                    }
                    if (!db.objectStoreNames.contains('history')) {
                        const store = db.createObjectStore('history', { keyPath: 'id', autoIncrement: true });
                        store.createIndex('createdAt', 'createdAt', { unique: false });
                    }
                };
            });
        },

        async loadImagesFromDB() {
            if (!this.db) return;

            const transaction = this.db.transaction(['images'], 'readonly');
            const store = transaction.objectStore('images');
            const request = store.getAll();

            request.onsuccess = () => {
                this.localImages = request.result || [];
                this.syncQueueWithLivewire();
            };
        },

        syncQueueWithLivewire() {
            const queue = this.localImages.map((img, index) => ({
                index: index,
                filename: img.filename,
                status: 'pending',
                title: null,
                keywords: null,
                error: null,
                generationId: null
            }));
            this.$wire.setImageQueue(queue);
        },

        async handleFiles(files) {
            if (!files || files.length === 0) return;

            this.isCompressing = true;
            this.compressTotal = files.length;
            this.compressProgress = 0;

            const maxWidth = 1920;
            const maxHeight = 1920;
            const quality = 0.8;
            const thumbnailSize = 200;

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                this.compressProgress = i + 1;

                try {
                    const result = await this.compressAndStore(file, maxWidth, maxHeight, quality, thumbnailSize);
                    this.localImages.push(result);
                } catch (e) {
                    console.error('Failed to process image:', e);
                }

                // Small delay to prevent UI freeze
                await new Promise(r => setTimeout(r, 10));
            }

            this.isCompressing = false;
            this.syncQueueWithLivewire();

            // Clear file input
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        compressAndStore(file, maxWidth, maxHeight, quality, thumbnailSize) {
            return new Promise((resolve, reject) => {
                if (!file.type.startsWith('image/')) {
                    reject(new Error('Not an image'));
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = async () => {
                        // Calculate dimensions for main image
                        let width = img.width;
                        let height = img.height;

                        if (width > maxWidth) {
                            height = (height * maxWidth) / width;
                            width = maxWidth;
                        }
                        if (height > maxHeight) {
                            width = (width * maxHeight) / height;
                            height = maxHeight;
                        }

                        // Create main canvas
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        // Create thumbnail canvas
                        const thumbCanvas = document.createElement('canvas');
                        const thumbRatio = Math.min(thumbnailSize / img.width, thumbnailSize / img.height);
                        thumbCanvas.width = img.width * thumbRatio;
                        thumbCanvas.height = img.height * thumbRatio;
                        const thumbCtx = thumbCanvas.getContext('2d');
                        thumbCtx.drawImage(img, 0, 0, thumbCanvas.width, thumbCanvas.height);

                        // Get base64 data
                        const base64Data = canvas.toDataURL('image/jpeg', quality).split(',')[1];
                        const thumbnail = thumbCanvas.toDataURL('image/jpeg', 0.7);

                        const imageData = {
                            filename: file.name,
                            base64: base64Data,
                            mimeType: 'image/jpeg',
                            thumbnail: thumbnail,
                            originalSize: file.size,
                            compressedSize: Math.round(base64Data.length * 0.75)
                        };

                        // Store in IndexedDB
                        const transaction = this.db.transaction(['images'], 'readwrite');
                        const store = transaction.objectStore('images');
                        const request = store.add(imageData);

                        request.onsuccess = () => {
                            imageData.id = request.result;
                            resolve(imageData);
                        };

                        request.onerror = () => reject(request.error);
                    };
                    img.onerror = () => reject(new Error('Failed to load image'));
                    img.src = e.target.result;
                };
                reader.onerror = () => reject(reader.error);
                reader.readAsDataURL(file);
            });
        },

        getImageStatus(index) {
            const queue = this.$wire.imageQueue;
            return queue[index]?.status || 'pending';
        },

        getLocalImage(index) {
            return this.localImages[index] || null;
        },

        async removeImage(index) {
            const img = this.localImages[index];
            if (!img || !this.db) return;

            // Remove from IndexedDB
            const transaction = this.db.transaction(['images'], 'readwrite');
            const store = transaction.objectStore('images');
            store.delete(img.id);

            // Remove from local array
            this.localImages.splice(index, 1);
            this.syncQueueWithLivewire();
        },

        async clearAllImages(skipWire = false) {
            if (!this.db) return;

            return new Promise((resolve) => {
                const transaction = this.db.transaction(['images'], 'readwrite');
                const store = transaction.objectStore('images');
                const request = store.clear();

                request.onsuccess = () => {
                    this.localImages = [];
                    // Reset file input so the same files can be selected again
                    const fileInput = document.getElementById('image-upload');
                    if (fileInput) fileInput.value = '';

                    if (!skipWire) {
                        this.$wire.resetForm();
                    }
                    resolve();
                };

                request.onerror = () => {
                    this.localImages = [];
                    const fileInput = document.getElementById('image-upload');
                    if (fileInput) fileInput.value = '';

                    if (!skipWire) {
                        this.$wire.resetForm();
                    }
                    resolve();
                };
            });
        },

        startGeneration() {
            // Generate unique session ID for this batch
            this.currentSessionId = Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
            this.$wire.startProcessing();
        },

        async sendImageToServer(index) {
            const img = this.localImages[index];
            if (!img) return;

            // Send base64 data to Livewire
            this.$wire.processImage(index, img.filename, img.base64, img.mimeType);
        },

        exportToCSV() {
            const results = this.$wire.results;
            if (!results || Object.keys(results).length === 0) {
                showToast('No results to export', 'warning');
                return;
            }

            // CSV header
            let csv = 'Filename,Title,Keywords\n';

            // Add each result
            Object.values(results).forEach(result => {
                // Escape quotes and wrap in quotes for CSV
                const filename = '"' + (result.filename || '').replace(/"/g, '""') + '"';
                const title = '"' + (result.title || '').replace(/"/g, '""') + '"';
                const keywords = '"' + (result.keywords || '').replace(/"/g, '""') + '"';

                csv += `${filename},${title},${keywords}\n`;
            });

            // Create blob and download
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');

            // Generate filename with date
            const now = new Date();
            const dateStr = now.toISOString().slice(0, 10);
            const timeStr = now.toTimeString().slice(0, 5).replace(':', '-');

            link.setAttribute('href', url);
            link.setAttribute('download', `metadata_${dateStr}_${timeStr}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            showToast('CSV exported!');
        },

        async saveToHistory(data) {
            if (!this.db) return;

            // Check if history store exists
            if (!this.db.objectStoreNames.contains('history')) {
                return;
            }

            // Find the matching local image to get the thumbnail
            const localImg = this.localImages.find(img => img.filename === data.filename);

            const historyItem = {
                sessionId: this.currentSessionId,
                filename: data.filename,
                title: data.title,
                keywords: data.keywords,
                thumbnail: localImg?.thumbnail || null,
                createdAt: new Date().toISOString()
            };

            const transaction = this.db.transaction(['history'], 'readwrite');
            const store = transaction.objectStore('history');
            store.add(historyItem);
        },

        destroy() {
            // Clear images when component is destroyed (navigating away)
            // Pass true to skip calling Livewire methods since the component is being destroyed
            this.clearAllImages(true);
        }
    };
};

// History Manager Component
window.clientHistoryManager = function () {
    return {
        db: null,
        history: [],
        sessions: [],
        filteredSessions: [],
        search: '',
        selectedSessions: [],
        isLoading: true,
        currentPage: 1,
        perPage: 5,

        get totalPages() {
            return Math.ceil(this.filteredSessions.length / this.perPage);
        },

        get paginatedSessions() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredSessions.slice(start, start + this.perPage);
        },

        async initDB() {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open('MetadataGeneratorDB', 2);

                request.onerror = () => {
                    this.isLoading = false;
                    reject(request.error);
                };

                request.onsuccess = () => {
                    this.db = request.result;
                    this.loadHistory();
                    resolve(this.db);
                };

                request.onupgradeneeded = (event) => {
                    const db = event.target.result;
                    if (!db.objectStoreNames.contains('images')) {
                        db.createObjectStore('images', { keyPath: 'id', autoIncrement: true });
                    }
                    if (!db.objectStoreNames.contains('history')) {
                        const store = db.createObjectStore('history', { keyPath: 'id', autoIncrement: true });
                        store.createIndex('createdAt', 'createdAt', { unique: false });
                        store.createIndex('sessionId', 'sessionId', { unique: false });
                    }
                };
            });
        },

        async loadHistory() {
            if (!this.db) {
                this.isLoading = false;
                return;
            }

            if (!this.db.objectStoreNames.contains('history')) {
                this.isLoading = false;
                return;
            }

            const transaction = this.db.transaction(['history'], 'readonly');
            const store = transaction.objectStore('history');
            const request = store.getAll();

            request.onsuccess = () => {
                this.history = request.result || [];
                this.groupBySessions();
                this.isLoading = false;
            };

            request.onerror = () => {
                this.isLoading = false;
            };
        },

        groupBySessions() {
            const sessionMap = new Map();

            // Group items by sessionId
            this.history.forEach(item => {
                const sessionId = item.sessionId || 'legacy-' + item.id; // Handle old items without sessionId

                if (!sessionMap.has(sessionId)) {
                    sessionMap.set(sessionId, {
                        id: sessionId,
                        items: [],
                        createdAt: item.createdAt,
                        expanded: false
                    });
                }

                sessionMap.get(sessionId).items.push(item);

                // Update session createdAt to earliest item
                if (item.createdAt < sessionMap.get(sessionId).createdAt) {
                    sessionMap.get(sessionId).createdAt = item.createdAt;
                }
            });

            // Convert to array and sort by date descending
            this.sessions = Array.from(sessionMap.values()).sort((a, b) =>
                new Date(b.createdAt) - new Date(a.createdAt)
            );

            // Initialize sort state for each session
            this.sessions.forEach(session => {
                session.sortDir = 'asc'; // Default sort direction
                // Initial sort by filename ASC
                session.items.sort((a, b) => (a.filename || '').localeCompare(b.filename || ''));
            });

            this.filterSessions();
        },

        sortSession(sessionId, direction) {
            const session = this.sessions.find(s => s.id === sessionId);
            if (!session) return;

            session.sortDir = direction;

            session.items.sort((a, b) => {
                const nameA = (a.filename || '').toLowerCase();
                const nameB = (b.filename || '').toLowerCase();

                if (direction === 'asc') {
                    return nameA.localeCompare(nameB);
                } else {
                    return nameB.localeCompare(nameA);
                }
            });
        },

        filterSessions() {
            if (!this.search) {
                this.filteredSessions = [...this.sessions];
            } else {
                const s = this.search.toLowerCase();
                this.filteredSessions = this.sessions.filter(session =>
                    session.items.some(item =>
                        (item.filename || '').toLowerCase().includes(s) ||
                        (item.title || '').toLowerCase().includes(s) ||
                        (item.keywords || '').toLowerCase().includes(s)
                    )
                );
            }
            this.currentPage = 1;
        },

        async deleteSession(sessionId) {
            const confirmed = await showConfirm({
                title: 'Delete Session?',
                text: 'This will permanently delete all items in this session.',
                confirmText: 'Yes, delete it',
                icon: 'warning'
            });
            if (!confirmed) return;

            const session = this.sessions.find(s => s.id === sessionId);
            if (!session) return;

            const transaction = this.db.transaction(['history'], 'readwrite');
            const store = transaction.objectStore('history');

            session.items.forEach(item => store.delete(item.id));

            this.history = this.history.filter(item =>
                !session.items.some(si => si.id === item.id)
            );
            this.groupBySessions();
            showToast('Session deleted');
        },

        async deleteSelectedSessions() {
            const confirmed = await showConfirm({
                title: `Delete ${this.selectedSessions.length} Sessions?`,
                text: 'This will permanently delete all selected sessions and their items.',
                confirmText: 'Yes, delete all',
                icon: 'warning'
            });
            if (!confirmed) return;

            const transaction = this.db.transaction(['history'], 'readwrite');
            const store = transaction.objectStore('history');

            this.selectedSessions.forEach(sessionId => {
                const session = this.sessions.find(s => s.id === sessionId);
                if (session) {
                    session.items.forEach(item => store.delete(item.id));
                }
            });

            this.history = this.history.filter(item => {
                const session = this.sessions.find(s => s.items.some(si => si.id === item.id));
                return session && !this.selectedSessions.includes(session.id);
            });

            this.selectedSessions = [];
            this.groupBySessions();
            showToast('Sessions deleted');
        },

        copyItem(item) {
            const text = `Title: ${item.title}\nKeywords: ${item.keywords}`;
            navigator.clipboard.writeText(text);
            showToast('Copied to clipboard');
        },

        exportSessionCsv(session) {
            this.downloadCsv(session.items, `metadata-session-${this.formatDateShort(session.createdAt)}.csv`);
        },

        exportAllCsv() {
            if (this.history.length === 0) {
                showToast('No items to export', 'warning');
                return;
            }
            this.downloadCsv(this.history, `metadata-all-${this.formatDateShort(new Date().toISOString())}.csv`);
        },

        downloadCsv(items, filename) {
            let csv = 'Filename,Title,Keywords\n';

            items.forEach(item => {
                const fname = '"' + (item.filename || '').replace(/"/g, '""') + '"';
                const title = '"' + (item.title || '').replace(/"/g, '""') + '"';
                const keywords = '"' + (item.keywords || '').replace(/"/g, '""') + '"';
                csv += `${fname},${title},${keywords}\n`;
            });

            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            showToast('CSV exported!');
        },

        truncate(str, length) {
            if (!str) return '';
            return str.length > length ? str.substring(0, length) + '...' : str;
        },

        getKeywords(str, limit) {
            if (!str) return [];
            return str.split(',').slice(0, limit).map(k => k.trim());
        },

        countKeywords(str) {
            if (!str) return 0;
            return str.split(',').length;
        },

        formatDateTime(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatDateShort(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toISOString().slice(0, 10);
        },

        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },

        nextPage() {
            if (this.currentPage < this.totalPages) this.currentPage++;
        }
    };
};
