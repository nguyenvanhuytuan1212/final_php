document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("contact-form");

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();

    if (!name || !email) {
      alert("Vui lòng nhập đầy đủ họ và tên, email.");
      return;
    }

    alert("Gửi phản hồi thành công! Cảm ơn bạn đã liên hệ.");

    form.reset();
  });
});
