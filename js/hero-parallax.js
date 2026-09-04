document.addEventListener("DOMContentLoaded", function () {
    const heroWraps = document.querySelectorAll(".hero-image-wrap");

    heroWraps.forEach((heroWrap) => {
        const heroProducto = heroWrap.querySelector(".hero-producto");
        if (!heroProducto) return;

        heroProducto.classList.add("floating");

        function handleMove(e) {
            if (window.innerWidth <= 991) return;

            const rect = heroWrap.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateY = ((x - centerX) / centerX) * 12;
            const rotateX = ((centerY - y) / centerY) * 12;
            const translateX = ((x - centerX) / centerX) * 12;
            const translateY = ((y - centerY) / centerY) * 12;

            heroProducto.style.transform = `
                translate3d(${translateX}px, ${translateY - 6}px, 0)
                rotateX(${rotateX}deg)
                rotateY(${rotateY}deg)
                scale(1.03)
            `;
        }

        function resetMove() {
            heroProducto.style.transform = "";
        }

        heroWrap.addEventListener("mousemove", handleMove);
        heroWrap.addEventListener("mouseleave", resetMove);
        heroWrap.addEventListener("touchend", resetMove);
    });
});
