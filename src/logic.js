const navbar = document.querySelector(".navbar");
const navToggle = document.querySelector(".nav-toggle");
const navItems = document.querySelectorAll(".nav-links a, .nav-languages a");

if (navbar && navToggle) {
    navToggle.addEventListener("click", () => {
        const isOpen = navbar.classList.toggle("is-open");
        navToggle.setAttribute("aria-expanded", String(isOpen));
    });

    navItems.forEach((item) => {
        item.addEventListener("click", () => {
            navbar.classList.remove("is-open");
            navToggle.setAttribute("aria-expanded", "false");
        });
    });

    document.addEventListener("click", (event) => {
        if (!navbar.contains(event.target)) {
            navbar.classList.remove("is-open");
            navToggle.setAttribute("aria-expanded", "false");
        }
    });
}

const confirmForms = document.querySelectorAll("[data-confirm-form]");

confirmForms.forEach((form) => {
    const modal = document.querySelector(".permission-modal");

    if (!modal) {
        return;
    }

    const closeButtons = modal.querySelectorAll("[data-modal-close]");
    const confirmButton = modal.querySelector("[data-modal-confirm]");
    let pendingSubmitter = null;
    let isConfirmed = false;
    let previousActiveElement = null;

    const closeModal = () => {
        modal.classList.remove("is-open");
        modal.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "";
        pendingSubmitter?.focus();
        previousActiveElement = null;
    };

    const openModal = () => {
        previousActiveElement = document.activeElement;
        modal.classList.add("is-open");
        modal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
        confirmButton?.focus();
    };

    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
        button.addEventListener("click", () => {
            pendingSubmitter = button;
        });
    });

    form.addEventListener("submit", (event) => {
        if (isConfirmed) {
            isConfirmed = false;
            return;
        }

        event.preventDefault();
        pendingSubmitter = event.submitter || pendingSubmitter;
        openModal();
    });

    closeButtons.forEach((button) => {
        button.addEventListener("click", closeModal);
    });

    modal.addEventListener("click", (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && modal.classList.contains("is-open")) {
            closeModal();
        }
    });

    confirmButton?.addEventListener("click", () => {
        isConfirmed = true;
        closeModal();

        if (typeof form.requestSubmit === "function") {
            form.requestSubmit(pendingSubmitter || undefined);
            return;
        }

        form.submit();
    });
});

//error detection displays error if user has been redirected back to home page due to internal fault
// Error code 1 = displayed if user attempts to go directly to order_scan or callback scripts without first selecting a platform
// Error code 2 = displays error if Oauth fails
// Error code 3 =  displays error if an error occurs when attempting to fetch users posts

function error_detection() {
    const errorCode = new URLSearchParams(window.location.search).get("error");
    const language = document.documentElement.lang === "es" ? "es" : "en";
    const errorMessages = {
        en: {
            "1": "No short cuts, make sure to select a platform to scan first!",
            "2": "Looks like an authentication error, make sure to agree to all access controls when prompted!",
            "3": "Oh no there was a hiccup fetching your posts, please try again later!"
        },
        es: {
            "1": "No hay atajos. Primero selecciona una plataforma para escanear.",
            "2": "Parece que hubo un error de autenticación. Asegúrate de aceptar los permisos solicitados.",
            "3": "Hubo un problema al obtener tus publicaciones. Inténtalo de nuevo más tarde."
        }
    };

    const errorMessage = errorMessages[language][errorCode] || "";

    if (errorMessage !== "") {
        alert(errorMessage);
    }
}

document.addEventListener("DOMContentLoaded", error_detection);
