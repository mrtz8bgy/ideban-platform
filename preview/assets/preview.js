(() => {
  const form = document.getElementById('preview-form');
  const message = document.getElementById('preview-message');
  if (!form || !message) return;
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    message.textContent = 'این فقط پیش‌نمایش رابط کاربری است؛ ثبت واقعی درخواست پس از راه‌اندازی PHP و اتصال MySQL فعال می‌شود.';
    message.classList.add('show');
  });
})();
