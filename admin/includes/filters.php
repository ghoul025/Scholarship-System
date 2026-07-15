<div id="filterCard" 
     class="fixed top-[55px] left-2 z-50 w-[48px] h-[48px] flex items-center justify-center">

  <!-- Toggle Button -->
  <button id="filterToggle" 
          class="w-14 h-14 rounded-full flex items-center justify-center 
                 bg-white text-gray-700 shadow-lg hover:bg-gray-100 
                 transition-all duration-300 ease-in-out 
                 focus:outline-none focus:ring-4 focus:ring-blue-200"
          title="Toggle Filters" aria-expanded="false" aria-controls="filterBody">
    <i class="bi bi-funnel text-xl"></i>
  </button>

  <!-- Filter Body -->
  <div id="filterBody" 
       class="absolute left-full top-0 hidden p-4 space-y-4 max-h-[70vh] overflow-y-auto 
              w-[280px] bg-white shadow-lg border border-gray-200 rounded-r-lg z-50">
    <form method="GET" action="dashboard.php" class="space-y-4 text-sm">

      <!-- Hidden Inputs -->
      <input type="hidden" name="status" id="statusInput" value="<?= htmlspecialchars($_GET['status'] ?? 'all') ?>">
      <input type="hidden" name="enrolled" id="enrolledInput" value="<?= htmlspecialchars($_GET['enrolled'] ?? 'all') ?>">
      <input type="hidden" name="semester" id="semesterInput" value="<?= htmlspecialchars($_GET['semester'] ?? 'all') ?>">
      <input type="hidden" name="course" id="courseInput" value="<?= htmlspecialchars($courses_filter ? implode(',', $courses_filter) : '') ?>">
      <input type="hidden" name="year_level" id="yearInput" value="<?= htmlspecialchars($years_filter ? implode(',', $years_filter) : '') ?>">
      <input type="hidden" name="school_year" id="schoolYearInput" value="<?= htmlspecialchars($school_year_filter ? implode(',', $school_year_filter) : '') ?>">

      <!-- Title -->
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700 flex items-center">
          <i class="bi bi-funnel-fill text-indigo-600 mr-2"></i> Filters
        </h3>
        <button type="button" id="closeFilter"
                class="p-1.5 rounded-md border border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition"
                aria-label="Close filters">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <!-- Status -->
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
        <div class="flex flex-wrap gap-2">
          <button type="button" class="px-3 py-1 text-xs rounded-full border transition" data-filter="all">All</button>
          <button type="button" class="px-3 py-1 text-xs rounded-full border transition" data-filter="Complete">Complete</button>
          <button type="button" class="px-3 py-1 text-xs rounded-full border transition" data-filter="Incomplete">Incomplete</button>
        </div>
      </div>

      <!-- Enrollment -->
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Enrollment</label>
        <div class="flex flex-wrap gap-2">
          <button type="button" class="px-3 py-1 text-xs rounded-full border transition" data-enroll="all">All</button>
          <button type="button" class="px-3 py-1 text-xs rounded-full border transition" data-enroll="1">Enrolled</button>
          <button type="button" class="px-3 py-1 text-xs rounded-full border transition" data-enroll="0">Not Enrolled</button>
        </div>
      </div>

      <!-- Semester -->
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Semester</label>
        <div class="flex flex-wrap gap-2">
          <button type="button" class="px-3 py-1 text-xs rounded-full border transition" data-sem="all">All</button>
          <button type="button" class="px-3 py-1 text-xs rounded-full border transition" data-sem="1st">1st Semester</button>
          <button type="button" class="px-3 py-1 text-xs rounded-full border transition" data-sem="2nd">2nd Semester</button>
        </div>
      </div>

      <!-- Multi-select Chips -->
      <?php
      $multiFilters = [
        'Course' => ['dataAttr' => 'data-course', 'hiddenInput' => 'courseInput', 'items' => $courses],
        'Year Level' => ['dataAttr' => 'data-year', 'hiddenInput' => 'yearInput', 'items' => $years],
        'School Year' => ['dataAttr' => 'data-school', 'hiddenInput' => 'schoolYearInput', 'items' => array_map(fn($sy) => $sy['label'], $school_years)]
      ];
      foreach ($multiFilters as $label => $filter):
      ?>
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1"><?= $label ?></label>
          <div class="flex flex-wrap gap-2">
            <?php foreach ($filter['items'] as $item): ?>
              <button type="button" class="px-3 py-1 text-xs rounded-full border transition" <?= $filter['dataAttr'] ?>="<?= htmlspecialchars($item) ?>">
                <?= htmlspecialchars($item) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- Search -->
      <div>
        <input id="searchInput" type="text" name="search" placeholder="Search by name..."
               value="<?= htmlspecialchars($search) ?>"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none">
      </div>

      <!-- Footer -->
<div class="flex gap-2 pt-3 border-t border-gray-200">
    <button type="submit" 
            class="flex-1 px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
        Apply
    </button>
    <a href="dashboard.php?show=scholars"
       class="flex-1 px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 text-center transition">
        Reset
    </a>
</div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const ACTIVE = ['bg-indigo-600','text-white','border-indigo-600','hover:bg-indigo-700'];
  const INACTIVE = ['bg-white','text-gray-700','border-gray-300','hover:bg-gray-100'];

  const filterBody = document.getElementById('filterBody');
  const filterToggle = document.getElementById('filterToggle');
  const closeFilter = document.getElementById('closeFilter');

  // Toggle filter panel
  filterToggle.addEventListener('click', () => {
    filterBody.classList.toggle('hidden');
    filterToggle.setAttribute('aria-expanded', filterBody.classList.contains('hidden') ? 'false' : 'true');
  });

  closeFilter.addEventListener('click', () => {
    filterBody.classList.add('hidden');
    filterToggle.setAttribute('aria-expanded', 'false');
  });

  // Single-select chips
  function setActiveSingle(groupSelector, hiddenInputId, valueAttr, value) {
    const chips = document.querySelectorAll(groupSelector);
    chips.forEach(btn => {
      ACTIVE.forEach(c => btn.classList.remove(c));
      INACTIVE.forEach(c => btn.classList.remove(c));
      INACTIVE.forEach(c => btn.classList.add(c));
    });
    const target = Array.from(chips).find(b => b.getAttribute(valueAttr) === value);
    if (target) {
      INACTIVE.forEach(c => target.classList.remove(c));
      ACTIVE.forEach(c => target.classList.add(c));
    }
    const input = document.getElementById(hiddenInputId);
    if (input) input.value = value;
  }

  // Multi-select chips
  function toggleMultiChip(btn, hiddenInputId, valueAttr) {
    const isActive = btn.classList.contains('bg-indigo-600');
    if (isActive) {
        ACTIVE.forEach(c => btn.classList.remove(c));
        INACTIVE.forEach(c => btn.classList.add(c));
    } else {
        INACTIVE.forEach(c => btn.classList.remove(c));
        ACTIVE.forEach(c => btn.classList.add(c));
    }
    const selected = Array.from(document.querySelectorAll(`[${valueAttr}]`))
                        .filter(b => b.classList.contains('bg-indigo-600'))
                        .map(b => b.getAttribute(valueAttr));
    const input = document.getElementById(hiddenInputId);
    if (input) input.value = selected.join(',');
  }

  // Attach chip listeners
  document.querySelectorAll('[data-filter]').forEach(btn => btn.addEventListener('click', () => setActiveSingle('[data-filter]', 'status', 'data-filter', btn.getAttribute('data-filter'))));
    // Attach chip listeners
  document.querySelectorAll('[data-filter]').forEach(btn => 
    btn.addEventListener('click', () => setActiveSingle('[data-filter]', 'statusInput', 'data-filter', btn.dataset.filter))
  );
  document.querySelectorAll('[data-enroll]').forEach(btn => 
    btn.addEventListener('click', () => setActiveSingle('[data-enroll]', 'enrolledInput', 'data-enroll', btn.dataset.enroll))
  );
  document.querySelectorAll('[data-sem]').forEach(btn => 
    btn.addEventListener('click', () => setActiveSingle('[data-sem]', 'semesterInput', 'data-sem', btn.dataset.sem))
  );

  [['data-course','courseInput'], ['data-year','yearInput'], ['data-school','schoolYearInput']]
    .forEach(([attr, inputId]) => {
      document.querySelectorAll(`[${attr}]`).forEach(btn => {
        btn.addEventListener('click', () => toggleMultiChip(btn, inputId, attr));
      });
    });

  // Initialize chips from hidden inputs
  function initChipsFromInputs() {
    setActiveSingle('[data-filter]', 'statusInput', 'data-filter', document.getElementById('statusInput')?.value ?? 'all');
    setActiveSingle('[data-enroll]', 'enrolledInput', 'data-enroll', document.getElementById('enrolledInput')?.value ?? 'all');
    setActiveSingle('[data-sem]', 'semesterInput', 'data-sem', document.getElementById('semesterInput')?.value ?? 'all');

    [['data-course','courseInput'], ['data-year','yearInput'], ['data-school','schoolYearInput']]
      .forEach(([attr, inputId]) => {
        const vals = (document.getElementById(inputId)?.value ?? '').split(',').filter(v => v !== '');
        document.querySelectorAll(`[${attr}]`).forEach(btn => {
          const value = btn.getAttribute(attr);
          if (vals.includes(value)) {
            INACTIVE.forEach(c => btn.classList.remove(c));
            ACTIVE.forEach(c => btn.classList.add(c));
          } else {
            ACTIVE.forEach(c => btn.classList.remove(c));
            INACTIVE.forEach(c => btn.classList.add(c));
          }
        });
      });
  }

  initChipsFromInputs();
});
</script>
