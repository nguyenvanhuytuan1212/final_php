// Hiển thị ảnh xem trước khi chọn file
function previewImage(event, previewId) {
    const reader = new FileReader();
    reader.onload = function () {
        const output = document.getElementById(previewId);
        output.src = reader.result;
        output.style.display = 'block'; // Hiển thị ảnh
    };
    reader.readAsDataURL(event.target.files[0]);
}

// Không chặn submit form nữa, để PHP xử lý chuyển hướng sang trang thanh toán QR
