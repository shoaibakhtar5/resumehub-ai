import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.resumeBuilder = function resumeBuilder(initial) {
    const editableCollections = ['experiences', 'educations', 'skills', 'projects', 'languages', 'certifications', 'awards', 'references'];

    return {
        ...initial,
        autosaveTimer: null,
        previewTimer: null,
        historyTimer: null,
        autosaveInFlight: false,
        autosaveQueued: false,
        draggedSection: null,
        photoRevision: 0,
        undoStack: [],
        redoStack: [],
        restoringHistory: false,
        aiBusy: false,
        aiError: '',
        fullscreen: false,
        selectedType: 'none',
        selectedKey: '',
        selectedSectionKey: '',
        selectedItemIndex: null,
        selectedItemCollection: '',
        selectedFieldName: '',
        templateSwitching: false,

        init() {
            this.normalizeState();
            this.refreshSectionOrders();
            this.undoStack = [this.snapshot()];
            this.$nextTick(() => {
                this.dispatchPreview();
                this.updateLayout();
                if (window.Livewire) {
                    window.Livewire.dispatch('active-section-changed', { section: this.activeSection });
                }
            });
            this.resizeHandler = () => {
                this.updateLayout();
                this.updateSelectionOverlay();
            };
            window.addEventListener('resize', this.resizeHandler);
            this.$watch('selectedKey', () => {
                this.$nextTick(() => this.updateSelectionOverlay());
            });
            // Watch template_id: when user picks a different template, fetch its
            // stored theme from the server so no styles bleed across templates.
            this.$watch('template_id', (newId, oldId) => {
                if (String(newId) !== String(oldId)) {
                    this.switchTemplate(newId);
                }
            });
            window.resumeBuilderApp = this;
        },

        /**
         * Called when the user picks a different template.
         * Fetches the per-template saved theme from the server and applies it
         * before triggering a preview/autosave cycle. This guarantees that
         * switching Template A → Template B never leaks A's customisations.
         */
        switchTemplate(newTemplateId) {
            if (!this.templateThemeUrl) {
                // No resume persisted yet — just update the preview as normal.
                this.queueChange();
                return;
            }
            this.templateSwitching = true;
            const url = this.templateThemeUrl + '?template_id=' + encodeURIComponent(newTemplateId || '');
            window.fetch(url, {
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
            })
                .then(r => r.json())
                .then(({ theme }) => {
                    if (theme && typeof theme === 'object') {
                        // Restore the saved theme for this template (clean slate for new templates).
                        this.theme = { ...this.designDefaults(), ...theme };
                    }
                    this.queueChange();
                })
                .catch(() => {
                    // On network error, fall through gracefully without corrupting state.
                    this.queueChange();
                })
                .finally(() => {
                    this.templateSwitching = false;
                });
        },

        destroy() {
            if (this.resizeHandler) {
                window.removeEventListener('resize', this.resizeHandler);
            }
            if (this.autosaveTimer) window.clearTimeout(this.autosaveTimer);
            if (this.previewTimer) window.clearTimeout(this.previewTimer);
            if (this.historyTimer) window.clearTimeout(this.historyTimer);
            if (this.autosaveTimeout) clearTimeout(this.autosaveTimeout);
            if (window.resumeBuilderApp === this) {
                window.resumeBuilderApp = null;
            }
        },

        normalizeState() {
            this.sections = Array.isArray(this.sections) ? this.sections : [];
            editableCollections.forEach((collection) => {
                this[collection] = Array.isArray(this[collection]) ? this[collection] : [];
            });
            this.social_links = Array.isArray(this.social_links) && this.social_links.length
                ? this.social_links
                : [{ platform: 'linkedin', label: 'LinkedIn', url: '', is_visible: true, sort_order: 0 }];
            this.theme = { ...this.designDefaults(), ...(this.theme || {}) };
            this.theme.styles = this.theme.styles || {};
            this.zoom = this.clamp(Number(this.zoom) || 75, 50, 200);
        },

        designDefaults() {
            return {
                accent_color: '#3155e7', secondary_color: '#142845', heading_font: 'Poppins',
                body_font: 'Inter', font_pairing: 'modern', font_scale: 100, density: 'balanced',
                page_size: 'a4', layout: 'two-column', sidebar_width: 34, photo_position: 'center',
                section_spacing: 'medium', content_width: 'standard', page_background: '#ffffff',
                dividers: true, shadow: true, header_color: '#17243b', header_scale: 100,
                styles: {},
            };
        },

        truthy(value) {
            return value !== false && value !== 0 && value !== '0' && value !== null;
        },

        queueChange() {
            this.recordHistory();
            this.applyThemeToPreview();
            this.dispatchPreview();

            if (!this.autosaveUrl) {
                this.autosaveState = 'Save to enable autosave';
                return;
            }

            this.autosaveState = 'Unsaved changes';
            window.clearTimeout(this.autosaveTimer);
            this.autosaveTimer = window.setTimeout(() => this.autosave(), 1000);
        },

        dispatchPreview() {
            window.clearTimeout(this.previewTimer);
            this.previewTimer = window.setTimeout(() => {
                if (window.Livewire) {
                    window.Livewire.dispatch('resume-updated', { data: this.getPayload() });
                    this.$nextTick(() => window.setTimeout(() => {
                        this.applyThemeToPreview();
                        this.updateLayout();
                    }, 200));
                }
            }, 60);
        },

        applyThemeToPreview() {
            const preview = document.getElementById('resume-live-preview');
            if (!preview) return;

            const densityGaps = { compact: '14px', balanced: '20px', spacious: '28px' };
            const photoMargins = {
                left: '0 auto 22px 0', center: '0 auto 22px', right: '0 0 22px auto',
            };
            const embedded = preview.querySelector('.rh-embedded-template');

            preview.style.setProperty('--rh-accent', this.theme.accent_color);
            preview.style.setProperty('--rh-secondary', this.theme.secondary_color);
            preview.style.setProperty('--rh-page-bg', this.theme.page_background);
            preview.style.setProperty('--rh-heading-font', `"${this.theme.heading_font}", sans-serif`);
            preview.style.setProperty('--rh-body-font', `"${this.theme.body_font}", sans-serif`);
            preview.style.setProperty('--rh-font-scale', String(Number(this.theme.font_scale) / 100));
            preview.style.setProperty('--rh-sidebar-width', `${this.theme.sidebar_width}%`);
            preview.style.setProperty('--rh-section-gap', densityGaps[this.theme.density] || densityGaps.balanced);
            preview.style.setProperty('--rh-divider-display', this.truthy(this.theme.dividers) ? 'block' : 'none');
            preview.style.setProperty('--rh-photo-margin', photoMargins[this.theme.photo_position] || photoMargins.center);
            preview.style.setProperty('--rh-header-color', this.theme.header_color || '#17243b');
            preview.style.setProperty('--rh-header-scale', String(Number(this.theme.header_scale || 100) / 100));

            if (embedded) {
                embedded.classList.toggle('rh-layout-one-column', this.theme.layout === 'one-column');
                embedded.classList.toggle('rh-layout-two-column', this.theme.layout !== 'one-column');
            }

            // Inject dynamic Custom Styles
            let customStyleTag = preview.querySelector('#rh-custom-styles');
            if (!customStyleTag) {
                customStyleTag = document.createElement('style');
                customStyleTag.id = 'rh-custom-styles';
                preview.appendChild(customStyleTag);
            }
            
            let css = '';
            if (this.theme.styles) {
                for (const [key, rules] of Object.entries(this.theme.styles)) {
                    if (!rules) continue;
                    let ruleStr = '';
                    if (rules.font_family) ruleStr += `font-family: "${rules.font_family}", sans-serif !important;`;
                    if (rules.font_size) ruleStr += `font-size: ${rules.font_size} !important;`;
                    if (rules.font_weight) ruleStr += `font-weight: ${rules.font_weight} !important;`;
                    if (rules.color) ruleStr += `color: ${rules.color} !important;`;
                    if (rules.text_align) ruleStr += `text-align: ${rules.text_align} !important;`;
                    if (rules.letter_spacing) ruleStr += `letter-spacing: ${rules.letter_spacing} !important;`;
                    if (rules.line_height) ruleStr += `line-height: ${rules.line_height} !important;`;
                    if (rules.italic) ruleStr += `font-style: italic !important;`;
                    if (rules.underline) ruleStr += `text-decoration: underline !important;`;
                    
                    if (rules.border_radius) ruleStr += `border-radius: ${rules.border_radius} !important;`;
                    if (rules.opacity) ruleStr += `opacity: ${rules.opacity} !important;`;
                    if (rules.width) ruleStr += `width: ${rules.width} !important;`;
                    if (rules.height) ruleStr += `height: ${rules.height} !important;`;
                    
                    if (rules.background) ruleStr += `background-color: ${rules.background} !important;`;
                    if (rules.padding) ruleStr += `padding: ${rules.padding} !important;`;
                    if (rules.margin) ruleStr += `margin: ${rules.margin} !important;`;
                    if (rules.border_color || rules.border_width) {
                        ruleStr += `border: ${rules.border_width || '1px'} solid ${rules.border_color || '#ccc'} !important;`;
                    }
                    if (rules.shadow) ruleStr += `box-shadow: ${rules.shadow} !important;`;
                    
                    if (ruleStr) {
                        css += `[data-rh-selectable="${key}"] { ${ruleStr} }\n`;
                    }
                }
            }
            customStyleTag.textContent = css;
            this.$nextTick(() => this.updateSelectionOverlay());
        },

        autosave() {
            const form = document.getElementById(this.formId);
            if (!form) return;
            if (this.autosaveInFlight) {
                this.autosaveQueued = true;
                return;
            }

            this.autosaveInFlight = true;
            this.autosaveState = 'Saving…';
            const body = new FormData(form);
            const submittedPhotoRevision = this.photoRevision;
            body.delete('_method');

            window.fetch(this.autosaveUrl, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                body,
            })
                .then(async (response) => {
                    if (!response.ok) throw new Error((await response.json().catch(() => null))?.message || 'Autosave failed');
                    return response.json();
                })
                .then((payload) => {
                    this.completionScore = payload.completion_score ?? this.completionScore;
                    if (payload.photo_url && submittedPhotoRevision === this.photoRevision) {
                        const previousPhoto = this.photoPreview;
                        this.photoPreview = payload.photo_url;
                        this.profile.photo_path = payload.photo_url;
                        window.dispatchEvent(new CustomEvent('resume-photo-selected', { detail: payload.photo_url }));
                        if (previousPhoto?.startsWith('blob:')) URL.revokeObjectURL(previousPhoto);
                        const photoInput = form.querySelector('[name="profile_photo"]');
                        if (photoInput) photoInput.value = '';
                    }
                    this.autosaveState = 'Saved';
                })
                .catch(() => { this.autosaveState = 'Save failed'; })
                .finally(() => {
                    this.autosaveInFlight = false;
                    if (this.autosaveQueued) {
                        this.autosaveQueued = false;
                        this.autosaveTimer = window.setTimeout(() => this.autosave(), 350);
                    }
                });
        },

        handlePhoto(event) {
            const [file] = event.target.files;
            if (!file) return;
            if (this.photoPreview?.startsWith('blob:')) URL.revokeObjectURL(this.photoPreview);
            this.photoRevision += 1;
            this.photoPreview = URL.createObjectURL(file);
            this.profile.photo_path = this.photoPreview;
            window.dispatchEvent(new CustomEvent('resume-photo-selected', { detail: this.photoPreview }));
            this.queueChange();
        },

        selectSection(key) {
            this.activeSection = key;
            this.activePanel = 'elements';
            this.mobilePane = window.innerWidth < 1024 ? 'editor' : this.mobilePane;
            if (window.Livewire) {
                window.Livewire.dispatch('active-section-changed', { section: key });
            }
        },

        applyAiOutputFromEvent(detail) {
            const { section, output } = detail;
            this.activeSection = section;
            this.applyAiOutput(output);
        },

        selectElement(type, key, sectionKey = '', itemIndex = null, itemCollection = '', fieldName = '') {
            this.selectedType = type;
            this.selectedKey = key;
            this.selectedSectionKey = sectionKey;
            this.selectedItemIndex = itemIndex;
            this.selectedItemCollection = itemCollection;
            this.selectedFieldName = fieldName;
            
            if (key && key !== 'page') {
                this.theme.styles[key] = this.theme.styles[key] || {};
            }
            
            if (sectionKey) {
                this.activeSection = sectionKey;
                if (window.Livewire) {
                    window.Livewire.dispatch('active-section-changed', { section: sectionKey });
                }
            }
            this.$nextTick(() => this.updateSelectionOverlay());
        },

        deselectElement() {
            this.selectedType = 'none';
            this.selectedKey = '';
            this.selectedSectionKey = '';
            this.selectedItemIndex = null;
            this.selectedItemCollection = '';
            this.selectedFieldName = '';
            this.$nextTick(() => this.updateSelectionOverlay());
        },

        handleCanvasClick(event) {
            const selectable = event.target.closest('[data-rh-selectable]');
            if (!selectable) {
                const viewport = event.target.closest('.studio-page-selection');
                if (viewport) {
                    this.selectElement('page', 'page');
                } else {
                    this.deselectElement();
                }
                return;
            }
            
            const key = selectable.dataset.rhSelectable;
            if (!key) return;
            
            if (key.startsWith('profile.')) {
                const field = key.split('.')[1];
                if (field === 'photo') {
                    this.selectElement('image', key, 'personal', null, '', 'photo');
                } else {
                    this.selectElement('field', key, 'personal', null, '', field);
                }
            } else if (key.startsWith('section.')) {
                const sectionKey = key.split('.')[1];
                this.selectElement('section', key, sectionKey);
            } else if (key.startsWith('item.')) {
                const parts = key.split('.');
                const type = parts[1];
                const index = parseInt(parts[2], 10);
                const collectionMap = {
                    experience: 'experiences',
                    education: 'educations',
                    project: 'projects',
                    certification: 'certifications',
                    award: 'awards',
                    reference: 'references'
                };
                const collection = collectionMap[type] || type;
                this.selectElement('item', key, type, index, collection);
            } else if (key.startsWith('tag.')) {
                const parts = key.split('.');
                const type = parts[1];
                const index = parseInt(parts[2], 10);
                const collectionMap = {
                    skills: 'skills',
                    languages: 'languages',
                    social_links: 'social_links'
                };
                const collection = collectionMap[type] || type;
                this.selectElement('item', key, type === 'social_links' ? 'personal' : type, index, collection);
            }
        },

        updateSelectionOverlay() {
            const overlay = document.getElementById('selection-overlay');
            if (!overlay) return;

            if (this.selectedType === 'none' || this.selectedType === 'page') {
                overlay.style.display = 'none';
                return;
            }

            const canvas = document.getElementById('resume-live-preview');
            if (!canvas) {
                overlay.style.display = 'none';
                return;
            }

            const element = canvas.querySelector(`[data-rh-selectable="${this.selectedKey}"]`);
            if (!element) {
                overlay.style.display = 'none';
                return;
            }

            const canvasRect = canvas.getBoundingClientRect();
            const elemRect   = element.getBoundingClientRect();

            // getBoundingClientRect() values are in screen-space (after CSS scale).
            // The overlay is positioned absolutely inside the canvas which is also
            // scaled, so we must divide by the scale factor to get canvas-space coords.
            const scale = this.effectiveScale || 1;

            const top    = (elemRect.top  - canvasRect.top)  / scale;
            const left   = (elemRect.left - canvasRect.left) / scale;
            const width  = elemRect.width  / scale;
            const height = elemRect.height / scale;

            overlay.style.top    = `${top}px`;
            overlay.style.left   = `${left}px`;
            overlay.style.width  = `${width}px`;
            overlay.style.height = `${height}px`;
            overlay.style.display = 'block';
        },

        moveSelection(direction) {
            if (this.selectedType === 'section') {
                const sectionIndex = this.sections.findIndex(s => s.section_key === this.selectedSectionKey);
                if (sectionIndex === -1) return;
                const newIndex = direction === 'up' ? sectionIndex - 1 : sectionIndex + 1;
                if (newIndex < 0 || newIndex >= this.sections.length) return;
                
                const temp = this.sections[sectionIndex];
                this.sections[sectionIndex] = this.sections[newIndex];
                this.sections[newIndex] = temp;
                this.refreshSectionOrders();
                this.queueChange();
            } else if (this.selectedType === 'item') {
                const collection = this.selectedItemCollection;
                const index = this.selectedItemIndex;
                if (!Array.isArray(this[collection])) return;
                const newIndex = direction === 'up' ? index - 1 : index + 1;
                if (newIndex < 0 || newIndex >= this[collection].length) return;
                
                const temp = this[collection][index];
                this[collection][index] = this[collection][newIndex];
                this[collection][newIndex] = temp;
                
                this.refreshOrders(collection);
                this.selectedItemIndex = newIndex;
                const parts = this.selectedKey.split('.');
                parts[parts.length - 1] = newIndex;
                this.selectedKey = parts.join('.');
                this.queueChange();
            }
        },
        
        duplicateSelection() {
            if (this.selectedType === 'item') {
                const collection = this.selectedItemCollection;
                const index = this.selectedItemIndex;
                if (!Array.isArray(this[collection])) return;
                
                const itemToCopy = this[collection][index];
                const copiedItem = JSON.parse(JSON.stringify(itemToCopy));
                delete copiedItem.id;
                copiedItem.sort_order = this[collection].length;
                this[collection].push(copiedItem);
                
                this.refreshOrders(collection);
                this.queueChange();
            }
        },
        
        deleteSelection() {
            if (this.selectedType === 'section') {
                const section = this.sections.find(s => s.section_key === this.selectedSectionKey);
                if (section) {
                    section.is_visible = false;
                    this.deselectElement();
                    this.queueChange();
                }
            } else if (this.selectedType === 'item') {
                const collection = this.selectedItemCollection;
                const index = this.selectedItemIndex;
                if (!Array.isArray(this[collection])) return;
                
                this[collection].splice(index, 1);
                this.refreshOrders(collection);
                this.deselectElement();
                this.queueChange();
            }
        },
        
        triggerAiAction() {
            this.activePanel = 'ai';
        },

        sectionTitle(key) {
            return this.sections.find((section) => section.section_key === key)?.title || 'Resume content';
        },

        isSectionLocked(key) {
            return this.truthy(this.sections.find((section) => section.section_key === key)?.settings?.locked);
        },

        addItem(collection, item) {
            if (!Array.isArray(this[collection])) return;
            this[collection].push({ ...item, sort_order: this[collection].length, is_visible: true });
            this.queueChange();
        },

        removeItem(collection, index) {
            this[collection].splice(index, 1);
            this.refreshOrders(collection);
            this.queueChange();
        },

        startSectionDrag(index) {
            if (this.sections[index]?.settings?.locked) return;
            this.draggedSection = index;
        },

        dropSection(index) {
            if (this.draggedSection === null || this.draggedSection === index || this.sections[index]?.settings?.locked) {
                this.draggedSection = null;
                return;
            }
            const moved = this.sections.splice(this.draggedSection, 1)[0];
            this.sections.splice(index, 0, moved);
            this.draggedSection = null;
            this.refreshSectionOrders();
            this.queueChange();
        },

        toggleSectionLock(section) {
            section.settings = { ...(section.settings || {}), locked: !this.truthy(section.settings?.locked) };
            this.queueChange();
        },

        refreshSectionOrders() {
            this.sections.forEach((section, index) => { section.sort_order = index; });
        },

        refreshOrders(collection) {
            this[collection].forEach((item, index) => { item.sort_order = index; });
        },

        recordHistory() {
            if (this.restoringHistory) return;
            window.clearTimeout(this.historyTimer);
            this.historyTimer = window.setTimeout(() => {
                const next = this.snapshot();
                if (next === this.undoStack.at(-1)) return;
                this.undoStack.push(next);
                if (this.undoStack.length > 40) this.undoStack.shift();
                this.redoStack = [];
            }, 300);
        },

        snapshot() {
            return JSON.stringify(this.getPayload());
        },

        restoreSnapshot(serialized) {
            const state = JSON.parse(serialized);
            this.restoringHistory = true;
            Object.keys(state).forEach((key) => { this[key] = state[key]; });
            this.$nextTick(() => {
                this.restoringHistory = false;
                this.dispatchPreview();
                this.queueChange();
            });
        },

        undo() {
            if (this.undoStack.length < 2) return;
            this.redoStack.push(this.undoStack.pop());
            this.restoreSnapshot(this.undoStack.at(-1));
        },

        redo() {
            if (!this.redoStack.length) return;
            const next = this.redoStack.pop();
            this.undoStack.push(next);
            this.restoreSnapshot(next);
        },

        get canUndo() { return this.undoStack.length > 1; },
        get canRedo() { return this.redoStack.length > 0; },

        handleShortcut(event) {
            if (!(event.ctrlKey || event.metaKey)) return;
            if (event.key.toLowerCase() === 'z') {
                event.preventDefault();
                event.shiftKey ? this.redo() : this.undo();
            }
            if (event.key.toLowerCase() === 'y') {
                event.preventDefault();
                this.redo();
            }
        },

        applyPreset(accent, secondary) {
            this.theme.accent_color = accent;
            this.theme.secondary_color = secondary;
            this.queueChange();
        },

        resetDesign() {
            this.theme = this.designDefaults();
            this.queueChange();
        },

        adjustFont(delta) {
            this.currentFontScale = this.clamp(Number(this.currentFontScale) + delta, 80, 125);
        },

        setZoom(value) {
            this.zoom = this.clamp(Number(value), 50, 200);
            this.settings = { ...(this.settings || {}), builder_zoom: this.zoom };
            this.updateLayout();
        },

        clamp(value, min, max) {
            return Math.min(max, Math.max(min, value));
        },

        get deviceClass() {
            return `is-${this.device}`;
        },

        /**
         * ============================================================
         * A4 CANVAS LAYOUT ENGINE
         * ============================================================
         *
         * The page stage is always 794px wide (A4 at 96 dpi = 210mm).
         * We compute a CSS scale() factor to fit it in the viewport at
         * the current zoom%. The outer wrapper shrinks to the *scaled*
         * dimensions so scroll / click coordinates are correct.
         *
         * A4 at 96 dpi: 794 × 1123 px
         * US Letter at 96 dpi: 794 × 1028 px
         */
        get A4_W()  { return 794; },
        get A4_H()  { return this.theme.page_size === 'letter' ? 1028 : 1123; },

        /**
         * The natural scale factor needed to fit A4 inside the viewport.
         * Viewport width is measured from the canvasViewport ref minus padding.
         */
        get naturalScale() {
            const viewport = this.$refs.canvasViewport;
            if (!viewport) return 1;
            const available = viewport.clientWidth - 64; // 32px padding each side
            return Math.max(0.1, Math.min(available / this.A4_W, 2));
        },

        /**
         * Effective scale = naturalScale * userZoom%
         */
        get effectiveScale() {
            return this.naturalScale * (this.zoom / 100);
        },

        /**
         * CSS variable string passed to the page stage wrapper.
         * The wrapper reads --page-scale and --page-natural-height from here.
         */
        get pageStageStyle() {
            // no longer used for zoom — updateLayout() sets transform directly
            return '';
        },

        /**
         * Main layout engine entry point.
         *
         * Strategy:
         * 1. The rendered HTML sits in #resume-live-preview at 794px width, height=auto.
         *    We measure its real pixel height (scrollHeight) to know the content height.
         * 2. We overlay fixed 794×A4_H "page frames" on top of it — each frame is a
         *    clipping window that shows exactly one A4's worth of content.
         * 3. The stage (794px wide) is CSS-scaled to fit the viewport at the user's
         *    chosen zoom level.
         * 4. The wrapper collapses to the scaled dimensions so the viewport scroll bar
         *    reflects actual visual size, not the raw 794px layout size.
         */
        updateLayout() {
            this.$nextTick(() => {
                const stage   = this.$refs.pageStage;
                const wrapper = this.$refs.pageStageWrapper;
                const preview = document.getElementById('resume-live-preview');
                const pageSelection = this.$refs.pageSelection;
                if (!stage || !wrapper || !preview || !pageSelection) return;

                // ── 1. Compute scale ──────────────────────────────────────────
                const viewport = this.$refs.canvasViewport;
                const available = viewport ? viewport.clientWidth - 64 : 730; // 32px padding each side
                const naturalScale = Math.max(0.1, Math.min(available / this.A4_W, 2));
                const scale = naturalScale * (this.zoom / 100);

                // Apply scale transform
                stage.style.transform       = `scale(${scale})`;
                stage.style.transformOrigin = 'top center';
                stage.style.width           = `${this.A4_W}px`;

                // ── 2. Measure true content height ────────────────────────────
                const contentH = Math.max(preview.scrollHeight, this.A4_H);

                // ── 3. Compute page count ─────────────────────────────────────
                this.pageCount = Math.max(1, Math.ceil(contentH / this.A4_H));
                this.page      = Math.min(this.page, this.pageCount);

                // ── 4. Build / update page frame overlay ──────────────────────
                // Remove existing frames and separators
                const existing = stage.querySelectorAll('.rh-page-frame, .rh-page-separator');
                existing.forEach(el => el.remove());

                const noShadow = !this.truthy(this.theme.shadow);

                for (let i = 0; i < this.pageCount; i++) {
                    // Page separator between pages (not before first page)
                    if (i > 0) {
                        const sep = document.createElement('div');
                        sep.className = 'rh-page-separator';
                        sep.textContent = `Page ${i + 1}`;
                        sep.style.cssText = `
                            position: absolute;
                            top: ${i * this.A4_H - 10}px;
                            left: 0;
                            width: ${this.A4_W}px;
                        `;
                        stage.appendChild(sep);
                    }

                    // Page frame: a fixed A4-height clipping window
                    const frame = document.createElement('div');
                    frame.className = 'rh-page-frame' + (noShadow ? ' without-shadow' : '');
                    frame.style.cssText = `
                        position: absolute;
                        top: ${i * this.A4_H}px;
                        left: 0;
                        width: ${this.A4_W}px;
                        height: ${this.A4_H}px;
                        overflow: hidden;
                        pointer-events: none;
                        z-index: -1;
                    `;
                    stage.appendChild(frame);
                }

                // Make the stage tall enough to contain all pages
                const totalH = this.pageCount * this.A4_H;
                stage.style.position = 'relative';
                stage.style.height   = `${totalH}px`;

                // ── 5. Size the wrapper to the SCALED dimensions ──────────────
                wrapper.style.width  = `${this.A4_W * scale}px`;
                wrapper.style.height = `${totalH * scale}px`;
            });
        },

        setPage(value) {
            this.page = this.clamp(Number(value), 1, this.pageCount);
            this.$nextTick(() => {
                const viewport = this.$refs.canvasViewport;
                if (!viewport) return;
                const scale    = this.effectiveScale;
                const pageTop  = (this.page - 1) * this.A4_H * scale;
                viewport.scrollTo({ top: Math.max(0, pageTop), behavior: 'smooth' });
            });
        },

        updatePageCount() {
            // Kept for backwards-compat; delegates to updateLayout().
            this.updateLayout();
        },

        toggleFullscreen() {
            const studio = document.querySelector('.resume-studio-canvas');
            if (!document.fullscreenElement) {
                studio?.requestFullscreen?.();
                this.fullscreen = true;
            } else {
                document.exitFullscreen?.();
                this.fullscreen = false;
            }
        },

        get liveAtsScore() {
            let score = 10;
            if (this.profile.full_name && this.profile.email) score += 12;
            if (this.target_role) score += 8;
            if ((this.summary || '').trim().length >= 80) score += 15;
            if (this.experiences.some((item) => item.position && item.company)) score += 20;
            if (this.educations.some((item) => item.degree || item.institution)) score += 10;
            if (this.skills.filter((item) => item.name).length >= 4) score += 12;
            if (this.projects.some((item) => item.name)) score += 5;
            if (this.languages.some((item) => item.name) || this.certifications.some((item) => item.name)) score += 4;
            if ((this.summary || '').match(/\b\d+(?:%|x|\+)?\b/)) score += 4;
            return Math.min(100, score);
        },

        aiActionForSection() {
            if (this.activeSection === 'summary') return 'summary';
            if (this.activeSection === 'experience') return 'experience';
            if (this.activeSection === 'skills' || this.activeSection === 'languages') return 'skills';
            return 'review';
        },

        aiSource() {
            if (this.activeSection === 'summary') return this.summary || '';
            const map = { experience: 'experiences', education: 'educations', projects: 'projects', skills: 'skills', languages: 'languages', certifications: 'certifications', awards: 'awards', references: 'references' };
            const collection = map[this.activeSection];
            return collection ? JSON.stringify(this[collection] || []) : `${this.profile.full_name || ''} ${this.target_role || ''}`;
        },

        runAi(mode) {
            if (this.aiBusy) return;
            this.aiBusy = true;
            this.aiError = '';
            const input = `${mode.toUpperCase()} the following ${this.activeSection} content:\n${this.aiSource()}`;
            window.fetch(this.aiUrl, {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                body: JSON.stringify({ resume_id: this.resumeId, feature: 'resume-builder', action: this.aiActionForSection(), input, job_description: '', tone: 'professional' }),
            })
                .then(async (response) => {
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(payload.message || 'AI suggestion failed');
                    return payload;
                })
                .then(({ output }) => this.applyAiOutput(output || ''))
                .catch((error) => { this.aiError = error.message; })
                .finally(() => { this.aiBusy = false; });
        },

        applyAiOutput(output) {
            if (!output) return;
            if (this.activeSection === 'summary') {
                this.summary = output;
            } else if (this.activeSection === 'personal') {
                if (this.profile) {
                    this.profile.headline = output;
                }
                this.target_role = output;
            } else if (this.activeSection === 'skills' || this.activeSection === 'languages') {
                const collection = this.activeSection === 'skills' ? 'skills' : 'languages';
                const names = output.split(/[,\n]+/).map((item) => item.replace(/^[-*]\s*/, '').trim()).filter(Boolean).slice(0, 16);
                this[collection] = names.map((name, index) => ({ name, proficiency: '', is_visible: true, sort_order: index }));
            } else {
                const collectionMap = { 
                    experience: 'experiences', 
                    education: 'educations', 
                    projects: 'projects', 
                    certifications: 'certifications', 
                    awards: 'awards', 
                    references: 'references' 
                };
                const collection = collectionMap[this.activeSection];
                if (collection) {
                    if (!this[collection]) {
                        this[collection] = [];
                    }
                    if (!this[collection].length) {
                        const defaults = {
                            experiences: { company: '', position: '', description: '' },
                            educations: { institution: '', degree: '', description: '' },
                            projects: { name: '', role: '', description: '' },
                            certifications: { name: '', issuer: '', description: '' },
                            awards: { title: '', issuer: '', description: '' },
                            references: { name: '', title: '', company: '', description: '' }
                        };
                        this.addItem(collection, defaults[collection] || {});
                    }
                    if (this[collection][0]) {
                        if (this[collection][0].description !== undefined) {
                            this[collection][0].description = output;
                        } else if (this[collection][0].title !== undefined && this.activeSection === 'awards') {
                            this[collection][0].title = output;
                        } else if (this[collection][0].name !== undefined) {
                            this[collection][0].name = output;
                        }
                    }
                }
            }
            this.queueChange();
        },

        get currentFont() {
            const section = this.sections.find(s => s.section_key === this.activeSection);
            return section?.settings?.font_family || this.theme.body_font;
        },
        set currentFont(val) {
            const section = this.sections.find(s => s.section_key === this.activeSection);
            if (section) {
                section.settings = { ...(section.settings || {}), font_family: val, body_font: val };
            } else {
                this.theme.body_font = val;
            }
            this.queueChange();
        },

        get currentFontScale() {
            const section = this.sections.find(s => s.section_key === this.activeSection);
            return section?.settings?.font_scale !== undefined && section.settings.font_scale !== ''
                ? Number(section.settings.font_scale)
                : Number(this.theme.font_scale);
        },
        set currentFontScale(val) {
            const section = this.sections.find(s => s.section_key === this.activeSection);
            if (section) {
                section.settings = { ...(section.settings || {}), font_scale: Number(val) };
            } else {
                this.theme.font_scale = Number(val);
            }
            this.queueChange();
        },

        getPayload() {
            return {
                title: this.title, target_role: this.target_role, target_company: this.target_company,
                summary: this.summary, profile: this.profile, theme: this.theme, social_links: this.social_links,
                experiences: this.experiences, educations: this.educations, skills: this.skills,
                projects: this.projects, languages: this.languages, certifications: this.certifications,
                awards: this.awards, references: this.references, sections: this.sections,
                settings: { ...(this.settings || {}), builder_zoom: this.zoom }, template_id: this.template_id,
            };
        },
    };
};

document.addEventListener('DOMContentLoaded', () => {
    if (!document.querySelector('[wire\\:id]') && !window.Livewire) {
        Alpine.start();
    }
});

let livewireHookRegistered = false;
document.addEventListener('livewire:init', () => {
    if (livewireHookRegistered) return;
    livewireHookRegistered = true;
    window.Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
        succeed(({ snapshot, effect }) => {
            if (window.resumeBuilderApp) {
                window.setTimeout(() => window.resumeBuilderApp.updateSelectionOverlay(), 100);
            }
        });
    });
});
