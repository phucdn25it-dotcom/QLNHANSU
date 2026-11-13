document.querySelectorAll('[data-password-toggle]').forEach(button => {
  button.addEventListener('click', () => {
    const field = document.getElementById(button.dataset.passwordToggle);
    const visible = field.type === 'password';

    field.type = visible ? 'text' : 'password';
    button.textContent = visible ? 'Ẩn' : 'Hiện';
    button.setAttribute('aria-pressed', String(visible));
  });
});

document.querySelectorAll('[data-avatar]').forEach(img => {
  img.addEventListener('error', () => { img.src = 'assets/avatar.svg'; }, { once: true });
});
const input = document.getElementById('images');
let previewUrl;
input?.addEventListener('change', () => {
  const file = input.files[0];
  if (!file) return;
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
    alert('Chọn ảnh JPG, PNG hoặc WebP tối đa 2 MB.'); input.value = ''; return;
  }
  if (previewUrl) URL.revokeObjectURL(previewUrl);
  previewUrl = URL.createObjectURL(file);
  document.getElementById('preview').src = previewUrl;
});
