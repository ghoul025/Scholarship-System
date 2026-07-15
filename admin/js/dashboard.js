document.addEventListener('DOMContentLoaded', function(){
  const rows = Array.from(document.querySelectorAll('table tbody tr'));
  const quickButtons = document.querySelectorAll('.quick-filter');
  const enrollmentButtons = document.querySelectorAll('.enrollment-filter');
  const semesterButtons = document.querySelectorAll('.semester-filter');
  const searchInput = document.querySelector('input[name="search"]');

  let currentQuick = 'all';
  let currentEnroll = 'all';
  let currentSem = 'all';

  function applyFilters() {
    const searchQ = (searchInput.value || '').trim().toLowerCase();

    rows.forEach(r => {
      const status = r.dataset.status || 'Incomplete';
      const enroll = r.dataset.enroll || 'Not Enrolled';
      const sem = r.dataset.sem || '1st';
      const text = r.dataset.search || r.textContent.toLowerCase();

      const matchesQuick = (currentQuick === 'all' || status === currentQuick);
      const matchesEnroll = (currentEnroll === 'all' || enroll === currentEnroll);
      const matchesSem = (currentSem === 'all' || sem === currentSem);
      const matchesSearch = (searchQ === '' || text.includes(searchQ));

      r.style.display = (matchesQuick && matchesEnroll && matchesSem && matchesSearch) ? '' : 'none';
    });
  }

  quickButtons.forEach(btn => {
    btn.addEventListener('click', function(){
      quickButtons.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentQuick = this.dataset.filter;
      applyFilters();
    });
  });

  enrollmentButtons.forEach(btn => {
    btn.addEventListener('click', function(){
      enrollmentButtons.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentEnroll = this.dataset.enroll;
      applyFilters();
    });
  });

  semesterButtons.forEach(btn => {
    btn.addEventListener('click', function(){
      semesterButtons.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentSem = this.dataset.sem;
      applyFilters();
    });
  });

  if (searchInput) searchInput.addEventListener('input', applyFilters);
});
