document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('scholars-table-body');
    const selectAll = document.getElementById('select-all');
    const batchForm = document.getElementById('batch-action-form');
    const batchActionSelect = document.getElementById('batch-action-select');
    const yearDropdown = document.getElementById('batch-year-dropdown');
    const courseDropdown = document.getElementById('batch-course-dropdown');
    const typeDropdown = document.getElementById('batch-type-dropdown');
    const batchSy = document.getElementById('batch-sy-dropdown');
    const batchSem = document.getElementById('batch-semester-dropdown');
    const batchNameInput = document.getElementById('batch-name-input');
    const batchBtn = document.getElementById('batch-action-btn');
    const exportBtn = document.getElementById('export-selected-btn');
    const exportPreviewBody = document.getElementById('export-preview-body');
    const exportScholarIds = document.getElementById('export-scholar-ids');
    const selectedCount = document.getElementById('selected-count');
    const toggleAddScholarBtn = document.getElementById('toggleAddScholarBtn');
    const addScholarForm = document.getElementById('addScholarForm');
    const closeAddScholar = document.getElementById('closeAddScholar');

    function qs(selector) { return document.querySelector(selector); }
    function qsa(selector) { return Array.from(document.querySelectorAll(selector)); }

    if (toggleAddScholarBtn && addScholarForm) {
        toggleAddScholarBtn.addEventListener('click', () => addScholarForm.classList.toggle('hidden'));
    }
    if (closeAddScholar && addScholarForm) {
        closeAddScholar.addEventListener('click', () => addScholarForm.classList.add('hidden'));
    }

    function showModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
    }
    function hideModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex');
    }

    qsa('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => hideModal(btn.dataset.modalClose));
    });

    function populateEditModalFromRow(row) {
        if (!row) return;
        const get = (k) => row.getAttribute('data-' + k) || '';
        const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
        setVal('edit-scholar-id', get('id'));
        setVal('edit-username', get('username'));
        setVal('edit-first_name', get('first'));
        setVal('edit-middle_name', get('middle'));
        setVal('edit-last_name', get('last'));
        setVal('edit-phone', get('phone'));
        setVal('edit-sex', get('sex'));
        setVal('edit-units', get('units'));
        setVal('edit-tuition_fee', get('tuition'));
        setVal('edit-course', get('course'));
        setVal('edit-year_level', get('year'));
        setVal('edit-scholarship_type', get('scholarship-type'));
        setVal('edit-school_year_id', get('schoolyearid'));
        setVal('edit-semester', get('semester'));
        setVal('edit-status', get('status') || 'not_enrolled');
        try {
            const rawBatch = get('batch') || '';
            const m = rawBatch.match(/\d+/);
            const batchNum = m ? m[0] : '';
            setVal('edit-batch', batchNum);
        } catch (err) {
            setVal('edit-batch', get('batch'));
        }
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.action-edit');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        const id = btn.getAttribute('data-id');
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (!row) return;
        populateEditModalFromRow(row);
        showModal('editScholarModal');
    });

    function getCheckboxes() { return Array.from(document.querySelectorAll('.row-checkbox')); }
    function getSelectedIds() { return getCheckboxes().filter(cb => cb.checked).map(cb => cb.value); }
    function updateSelectedUI() {
        const ids = getSelectedIds();
        const count = ids.length;
        if (selectedCount) selectedCount.textContent = count > 0 ? `${count} selected` : '';
        if (exportBtn) exportBtn.disabled = count === 0;
        updateBatchBtn();
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            getCheckboxes().forEach(cb => { cb.checked = selectAll.checked; cb.dispatchEvent(new Event('change')); });
            updateSelectedUI();
        });
    }

    document.body.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('row-checkbox')) {
            const tr = e.target.closest('tr');
            if (tr) tr.classList.toggle('bg-blue-50', e.target.checked);
            const all = getCheckboxes();
            if (selectAll) selectAll.checked = all.length > 0 && all.every(x => x.checked);
            updateSelectedUI();
        }
    });

    if (batchForm) {
        batchForm.addEventListener('submit', function(e) {
            Array.from(batchForm.querySelectorAll('input[name="scholar_ids[]"]')).forEach(n => n.remove());
            const ids = getSelectedIds();
            ids.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'scholar_ids[]';
                inp.value = id;
                batchForm.appendChild(inp);
            });
            const action = batchActionSelect ? batchActionSelect.value : '';
            if (!action) { alert('Select a batch action.'); e.preventDefault(); return; }
            if (ids.length === 0) { alert('Select at least one scholar.'); e.preventDefault(); return; }
            if (action === 'reset' && !confirm('Reset password for selected scholars to 123456?')) { e.preventDefault(); return; }
            if (action === 'delete' && !confirm('Delete selected scholars? This cannot be undone.')) { e.preventDefault(); return; }
            if (action === 'change_year' && (!yearDropdown || !yearDropdown.value)) { alert('Select a year level.'); e.preventDefault(); return; }
            if (action === 'change_course' && (!courseDropdown || !courseDropdown.value)) { alert('Select a course.'); e.preventDefault(); return; }
            if (action === 'change_type' && (!typeDropdown || !typeDropdown.value)) { alert('Select a scholarship type.'); e.preventDefault(); return; }
            if (action === 'enroll' && (!batchSy || !batchSy.value || !batchSem || !batchSem.value)) { alert('Select both school year and semester.'); e.preventDefault(); return; }
            if (action === 'assign_batch') {
                if (!batchNameInput || !batchNameInput.value.trim()) { alert('Enter a batch number.'); e.preventDefault(); return; }
                if (!/^\d+$/.test(batchNameInput.value.trim())) { alert('Batch must be a number.'); e.preventDefault(); return; }
            }
        });
    }

    function hideAllDropdowns() {
        [yearDropdown, courseDropdown, typeDropdown, batchSy, batchSem, batchNameInput].forEach(el => { if (!el) return; el.classList.add('hidden'); el.required = false; });
    }
    if (batchActionSelect) {
        batchActionSelect.addEventListener('change', function() {
            hideAllDropdowns();
            const v = this.value;
            if (v === 'change_year' && yearDropdown) { yearDropdown.classList.remove('hidden'); yearDropdown.required = true; }
            if (v === 'change_course' && courseDropdown) { courseDropdown.classList.remove('hidden'); courseDropdown.required = true; }
            if (v === 'change_type' && typeDropdown) { typeDropdown.classList.remove('hidden'); typeDropdown.required = true; }
            if (v === 'enroll' && batchSy && batchSem) { batchSy.classList.remove('hidden'); batchSy.required = true; batchSem.classList.remove('hidden'); batchSem.required = true; }
            if (v === 'assign_batch' && batchNameInput) { batchNameInput.classList.remove('hidden'); batchNameInput.required = true; }
            updateBatchBtn();
        });
    }

    function updateBatchBtn() {
        if (!batchBtn || !batchActionSelect) return;
        const action = batchActionSelect.value;
        const ids = getSelectedIds();
        let valid = action && ids.length > 0;
        if (action === 'change_year') valid = valid && yearDropdown && yearDropdown.value !== '';
        if (action === 'change_course') valid = valid && courseDropdown && courseDropdown.value !== '';
        if (action === 'change_type') valid = valid && typeDropdown && typeDropdown.value !== '';
        if (action === 'enroll') valid = valid && batchSy && batchSy.value !== '' && batchSem && batchSem.value !== '';
        if (action === 'assign_batch') valid = valid && batchNameInput && /^\d+$/.test(batchNameInput.value.trim());
        batchBtn.disabled = !valid;
    }

    [yearDropdown, courseDropdown, typeDropdown, batchSy, batchSem, batchNameInput].forEach(el => {
        if (el) el.addEventListener('change', updateBatchBtn);
        if (el && el.tagName === 'INPUT') el.addEventListener('input', updateBatchBtn);
    });

    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const ids = getSelectedIds();
            if (!exportScholarIds) return;
            exportScholarIds.value = ids.join(',');
            if (exportPreviewBody) exportPreviewBody.innerHTML = '<div class="text-center text-gray-500">Loading preview...</div>';
            if (ids.length > 0) {
                fetch('actions/preview_export_scholars.php?preview_export=1&ids=' + ids.join(','))
                    .then(res => res.text())
                    .then(html => { if (exportPreviewBody) exportPreviewBody.innerHTML = html; })
                    .catch(() => { if (exportPreviewBody) exportPreviewBody.innerHTML = '<div class="text-red-600">Preview failed</div>'; });
                showModal('exportModal');
            } else {
                if (exportPreviewBody) exportPreviewBody.innerHTML = '<div class="text-center text-gray-500">Select scholars to preview export.</div>';
                showModal('exportModal');
            }
        });
    }

    const exportBatchesBtn = document.getElementById('export-batches-btn');
    if (exportBatchesBtn) exportBatchesBtn.addEventListener('click', () => showModal('exportBatchesModal'));

    const exportForm = document.getElementById('export-batches-form');
    if (exportForm) {
        exportForm.addEventListener('submit', e => {
            const checked = Array.from(exportForm.querySelectorAll('input[type=checkbox]')).some(c => c.checked);
            if (!checked) { e.preventDefault(); alert('Select at least one batch to export.'); }
        });
    }

    updateSelectedUI();
    updateBatchBtn();
});