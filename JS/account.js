// Hiển thị ảnh xem trước khi chọn file
function previewImage(event, previewId) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById(previewId);
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}

// Hiệu ứng đẹp khi lưu form
document.getElementById('accountForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    Swal.fire({
        title: '🎉 ĐẶT HÀNG THÀNH CÔNG!',
        text: 'Cảm ơn bạn đã đặt hàng tại cửa hàng của chúng tôi 💖',
        icon: 'success',
        background: '#f0fff0', // nền xanh nhạt nhẹ nhàng
        color: '#333',
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Đóng',
        width: '400px',
        padding: '20px',
        position: 'center',
        showClass: {
            popup: 'animate__animated animate__zoomIn'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    });
});
