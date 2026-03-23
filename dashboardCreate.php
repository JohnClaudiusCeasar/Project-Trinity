<!-- Create Content -->
<main class="create-main">

    <!-- Hero -->
    <section class="dashboard-hero">
        <h1>Create</h1>
        <p>Choose what you'd like to add to the Trinity Archives</p>
    </section>

    <!-- SCREEN 1: Type Picker -->
    <div class="create-picker" id="createPicker">
        <div class="create-type-grid">

            <button class="create-type-card" data-type="story">
                <div class="create-type-icon">◎</div>
                <div class="create-type-label">Story</div>
                <div class="create-type-desc">Narratives, fiction, field reports, and written works</div>
                <div class="create-type-arrow">→</div>
            </button>

            <button class="create-type-card" data-type="character">
                <div class="create-type-icon">◈</div>
                <div class="create-type-label">Character</div>
                <div class="create-type-desc">Profiles, entities, personas, and beings within your worlds</div>
                <div class="create-type-arrow">→</div>
            </button>

            <button class="create-type-card" data-type="world">
                <div class="create-type-icon">⬡</div>
                <div class="create-type-label">World</div>
                <div class="create-type-desc">Settings, realms, dimensions, and constructed environments</div>
                <div class="create-type-arrow">→</div>
            </button>

            <button class="create-type-card" data-type="object">
                <div class="create-type-icon">✦</div>
                <div class="create-type-label">Object / Artifact</div>
                <div class="create-type-desc">Items, relics, anomalies, and objects of significance</div>
                <div class="create-type-arrow">→</div>
            </button>

        </div>
    </div>

    <!-- SCREEN 2: Skeleton Form (hidden by default) -->
    <div class="create-form-wrapper" id="createFormWrapper">

        <!-- Back Button -->
        <button class="create-back-btn" id="createBackBtn">
            <span>←</span> Back
        </button>

        <!-- Form Header -->
        <div class="create-form-header">
            <span class="create-form-type-icon" id="createFormIcon"></span>
            <div>
                <div class="create-form-type-label" id="createFormTypeLabel"></div>
                <h2 class="create-form-title">New Entry</h2>
            </div>
        </div>

        <!-- Skeleton Form -->
        <form class="create-form" id="createForm">

            <div class="form-field">
                <label class="form-label" for="entryTitle">Title</label>
                <input class="form-input" type="text" id="entryTitle" placeholder="Give your entry a title…" disabled>
            </div>

            <div class="form-field">
                <label class="form-label" for="entryDescription">Description</label>
                <textarea class="form-input form-textarea" id="entryDescription" rows="4" placeholder="A brief description of this entry…" disabled></textarea>
            </div>

            <div class="form-field">
                <label class="form-label" for="entryTags">Tags</label>
                <input class="form-input" type="text" id="entryTags" placeholder="e.g. Fiction, Collaborative, Anomaly…" disabled>
            </div>

            <div class="form-field">
                <label class="form-label" for="entryVisibility">Visibility</label>
                <select class="form-input form-select" id="entryVisibility" disabled>
                    <option value="">Select visibility…</option>
                    <option value="public">Public</option>
                    <option value="collaborators">Collaborators only</option>
                    <option value="private">Private</option>
                </select>
            </div>

            <!-- Skeleton notice -->
            <div class="form-skeleton-notice">
                <span>✦</span> Full form fields coming in a future build.
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="createCancelBtn">Cancel</button>
                <button type="submit" class="btn" disabled>Save Entry</button>
            </div>

        </form>
    </div>

</main>