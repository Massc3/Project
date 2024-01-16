const buttons = document.querySelectorAll(".btn");
const slides = document.querySelectorAll(".slide-card-home-page-avis");

buttons.forEach((button) => {
    button.addEventListener("click", (e) => {
        const calcNextSlide = e.target.id === "next" ? 1 : -1;
        // Logique pour changer les diapositives ici en utilisant calcNextSlide
    });
});
