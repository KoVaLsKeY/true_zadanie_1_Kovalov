document.addEventListener('DOMContentLoaded', function() {
    fetch('../php/data/slides.json')
        .then(response => response.json())
        .then(slides => {
            const slidesContainer = document.querySelector('.slides-container');
            slides.forEach((slideData, index) => {
                const slideDiv = document.createElement('div');
                slideDiv.className = 'slide fade';
                if (index === 0) slideDiv.classList.add('active');

                // Bonus: Pridanie klikateľného odkazu
                const link = document.createElement('a');
                link.href = slideData.url;
                link.style.display = 'block';
                link.style.height = '100%';

                const img = document.createElement('img');
                img.src = slideData.imageSrc;
                const textDiv = document.createElement('div');
                textDiv.className = 'slide-text';
                textDiv.textContent = slideData.text;

                link.appendChild(img);
                link.appendChild(textDiv);
                slideDiv.appendChild(link);
                slidesContainer.insertBefore(slideDiv, slidesContainer.querySelector('.prev'));
            });

            // Inicializácia slidera po načítaní dát
            initSlider();
        })
        .catch(error => console.error('Chyba pri načítaní sliderov:', error));
});