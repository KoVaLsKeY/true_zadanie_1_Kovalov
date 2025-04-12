function initSlider() {
  let idx = 1;
  const slides = document.getElementsByClassName("slide");
  const prev = document.getElementById("prev");
  const next = document.getElementById("next");

  // Funkcia na zobrazenie konkrétneho slídu
  function show(n) {
      // Kontrola hraníc
      if (n > slides.length) idx = 1;
      if (n < 1) idx = slides.length;
      
      // Skrytie všetkých slidov
      Array.from(slides).forEach(slide => {
          slide.style.display = "none";
      });
      
      // Zobrazenie aktuálneho slídu
      if (slides[idx - 1]) {
          slides[idx - 1].style.display = "block";
      }
  }

  // Posun na ďalší/predchádzajúci slide
  function nextSlide(n) {
      show(idx += n);
  }

  // Event listeners pre tlačidlá
  prev.addEventListener("click", () => nextSlide(-1));
  next.addEventListener("click", () => nextSlide(1));

  // Inicializácia: zobraziť prvý slide
  show(idx);
}