import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.resumeBuilder = function resumeBuilder(initial) {
    return {
        ...initial,
        autosaveTimer: null,
        previewTimer: null,
        autosaveInFlight: false,
        autosaveQueued: false,
        autosaveState: 'Ready',
        draggedSection: null,
        photoRevision: 0,

        init() {
            this.refreshSectionOrders();
            window.setTimeout(() => this.dispatchPreview(), 0);
        },

        queueAutosave() {
            this.dispatchPreview();

            if (!this.autosaveUrl) {
                this.autosaveState = 'Draft not saved';

                return;
            }

            this.autosaveState = 'Unsaved changes';
            window.clearTimeout(this.autosaveTimer);
            this.autosaveTimer = window.setTimeout(() => this.autosave(), 1200);
        },

        dispatchPreview() {
            window.clearTimeout(this.previewTimer);
            this.previewTimer = window.setTimeout(() => {
                if (window.Livewire) {
                    window.Livewire.dispatch('resume-updated', { data: this.getPayload() });
                }
            }, 80);
        },

        autosave() {
            const form = document.getElementById(this.formId);

            if (!form) {
                return;
            }

            if (this.autosaveInFlight) {
                this.autosaveQueued = true;

                return;
            }

            this.autosaveInFlight = true;
            this.autosaveState = 'Saving...';
            const body = new FormData(form);
            const submittedPhotoRevision = this.photoRevision;
            body.delete('_method');

            window.fetch(this.autosaveUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Autosave failed');
                    }

                    return response.json();
                })
                .then((payload) => {
                    this.completionScore = payload.completion_score ?? this.completionScore;
                    if (payload.photo_url && submittedPhotoRevision === this.photoRevision) {
                        const previousPhoto = this.photoPreview;
                        this.photoPreview = payload.photo_url;
                        this.profile.photo_path = payload.photo_url;
                        window.dispatchEvent(new CustomEvent('resume-photo-selected', { detail: payload.photo_url }));

                        if (previousPhoto?.startsWith('blob:')) {
                            URL.revokeObjectURL(previousPhoto);
                        }

                        const photoInput = form.querySelector('[name="profile_photo"]');

                        if (photoInput) {
                            photoInput.value = '';
                        }
                    }
                    this.autosaveState = 'Saved';
                })
                .catch(() => {
                    this.autosaveState = 'Save failed';
                })
                .finally(() => {
                    this.autosaveInFlight = false;

                    if (this.autosaveQueued) {
                        this.autosaveQueued = false;
                        window.clearTimeout(this.autosaveTimer);
                        this.autosaveTimer = window.setTimeout(() => this.autosave(), 400);
                    }
                });
        },

        handlePhoto(event) {
            const [file] = event.target.files;

            if (!file) {
                return;
            }

            if (this.photoPreview?.startsWith('blob:')) {
                URL.revokeObjectURL(this.photoPreview);
            }

            this.photoRevision += 1;
            this.photoPreview = URL.createObjectURL(file);
            window.dispatchEvent(new CustomEvent('resume-photo-selected', { detail: this.photoPreview }));
            this.queueAutosave();
        },

        goToStep(index) {
            this.activeStep = Math.max(0, Math.min(7, index));
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        nextStep() {
            this.goToStep(this.activeStep + 1);
        },

        previousStep() {
            this.goToStep(this.activeStep - 1);
        },

        stepLabel(index) {
            return ['Personal', 'Education', 'Experience', 'Skills', 'Projects', 'Languages', 'Summary', 'Review'][index] || 'Review';
        },

        openPreview() {
            this.previewOpen = true;
            this.$nextTick(() => document.getElementById('resume-live-preview')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
        },

        addItem(collection, item) {
            this[collection].push({ ...item, sort_order: this[collection].length, is_visible: true });
            this.queueAutosave();
        },

        removeItem(collection, index) {
            this[collection].splice(index, 1);
            this.refreshOrders(collection);
            this.queueAutosave();
        },

        addCustomSection() {
            this.custom_sections.push({
                title: '',
                description: '',
                is_visible: true,
                sort_order: this.custom_sections.length,
                items: [{ title: '', subtitle: '', description: '', is_visible: true, sort_order: 0 }],
            });
            this.queueAutosave();
        },

        addCustomItem(sectionIndex) {
            this.custom_sections[sectionIndex].items.push({
                title: '',
                subtitle: '',
                description: '',
                is_visible: true,
                sort_order: this.custom_sections[sectionIndex].items.length,
            });
            this.queueAutosave();
        },

        removeCustomItem(sectionIndex, itemIndex) {
            this.custom_sections[sectionIndex].items.splice(itemIndex, 1);
            this.custom_sections[sectionIndex].items.forEach((item, index) => {
                item.sort_order = index;
            });
            this.queueAutosave();
        },

        startSectionDrag(index) {
            this.draggedSection = index;
        },

        dropSection(index) {
            if (this.draggedSection === null || this.draggedSection === index) {
                this.draggedSection = null;
                return;
            }

            const moved = this.sections.splice(this.draggedSection, 1)[0];
            this.sections.splice(index, 0, moved);
            this.draggedSection = null;
            this.refreshSectionOrders();
            this.queueAutosave();
        },

        refreshSectionOrders() {
            this.sections.forEach((section, index) => {
                section.sort_order = index;
            });
        },

        refreshOrders(collection) {
            this[collection].forEach((item, index) => {
                item.sort_order = index;
            });
        },

        sectionVisible(key) {
            const section = this.sections.find((item) => item.section_key === key);

            return !section || Boolean(section.is_visible);
        },

        visibleItems(collection) {
            return this[collection].filter((item) => item.is_visible !== false && item.is_visible !== '0');
        },

        keywordIdeas() {
            const source = `${this.target_role || ''} ${this.skills.map((skill) => skill.name).join(' ')} ${this.summary || ''}`;
            const stop = new Set(['and', 'the', 'with', 'for', 'from', 'that', 'this', 'your', 'role']);
            const words = source.toLowerCase().match(/[a-z][a-z+#.-]{2,}/g) || [];

            return [...new Set(words)]
                .filter((word) => !stop.has(word))
                .slice(0, 10)
                .map((word) => word.replace(/\b\w/g, (letter) => letter.toUpperCase()));
        },

        getPayload() {
            return {
                title: this.title,
                target_role: this.target_role,
                summary: this.summary,
                profile: this.profile,
                theme: this.theme,
                social_links: this.social_links,
                experiences: this.experiences,
                educations: this.educations,
                skills: this.skills,
                projects: this.projects,
                languages: this.languages,
                sections: this.sections,
                settings: this.settings,
                template_id: this.template_id,
            };
        },
    };
};

Alpine.start();
