let currentIndex = 0;

function showNextImage() {
  const images = document.querySelectorAll("#slideshow img");
  images[currentIndex].classList.remove("active");

  currentIndex = (currentIndex + 1) % images.length;

  images[currentIndex].classList.add("active");
}

// Gọi hàm showNextImage mỗi 5 giây
setInterval(showNextImage, 5000);
